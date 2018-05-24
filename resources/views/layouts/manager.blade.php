<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->

    <link rel='shortcut icon' href='{{asset('media/favicon.ico')}}' type='image/x-icon' />

      <link href="{{ asset('css/style.css') }}" rel="stylesheet">
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
      <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">


  <meta name="mobile-web-app-capable" content="yes">
  <link rel="icon" sizes="192x192" href="{{asset('media/logo.png')}}">

  <!-- Add to homescreen for Safari on iOS -->
  <meta name="apple-mobile-web-app-title" content="Tiekimas">

  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
  <link rel="apple-touch-icon-precomposed" href="{{asset('media/logo.png')}}">

  <!-- Tile icon for Win8 (144x144 + tile color) -->
  <meta name="msapplication-TileImage" content="{{asset('media/logo.png')}}">
  <meta name="msapplication-TileColor" content="#3372DF">
  <!-- Include manifest file in the page -->
  <link rel="manifest" href="manifest.json">
  <script src="{{asset('js/jquery-3.2.1.min.js')}}"></script>
  <script>
  (function($) {
    $(function() {
      var activeurl = window.location + '';
      var active = activeurl.split('?');
      $('a[href="'+active[0]+'"]').addClass('active');
      getChatUnreadMessages();
    });
  })(jQuery);


  var loadDate = null;
  function formatedDate()
  {
    var date = new Date();
    var month = (date.getMonth()+1).toString();
    var day = (date.getDate()).toString();
    var hours = (date.getHours()).toString();
    var minutes = (date.getMinutes()).toString();
    var seconds = (date.getSeconds()).toString();
    if(month.length < 2)
      month = "0"+month;
    if(day.length < 2)
      day = "0"+day;
    if(hours.length < 2)
     hours = "0"+hours;
    if(minutes.length <2)
      minutes = "0"+minutes;
    if(seconds.length <2)
      seconds = "0"+seconds;

    return date.getFullYear()+"-"+month+"-"+day+" "+hours+":"+minutes+":"+seconds;
  }
  $(document).ready(function(){
      loadDate = formatedDate();

    navigator.serviceWorker.register('{{asset('service-worker.js')}}');

    setInterval(function(){
    $.ajax({
      url: '/checkupdates',
      type: 'POST',
      dataType: 'html',
      data: {date: loadDate, _token: "{{ csrf_token() }}"},
      success: function(data){
        if(data != "0")
        {
          sendNotification();
        }
        }
      });
    },3000);

    });

    var user = {!!Auth::user()!!};

    function loadChatMessages(id){
      return fetch('/chat/get/'+id, {credentials: "same-origin"})
      .then(processStatus)
      .then(parseJson)
      .then(function(data){
        $('#chat-content').empty();
        $('#chat-box-order-id').val(id);
        $('#chat-header').html(data['name']);
        data['chat_messages'].forEach(function(message){
          if(message['UserID'] === user['id']){
            $('#chat-content').append('<li class="send-msg float-right mb-2"><p class="pt-1 pb-1 pl-2 pr-2 m-0 rounded">'+message['ChatMessage']+'</p></li>');
          }
          else{
            $('#chat-content').append('<li class="receive-msg float-left mb-2">\
                <div class="sender-img">\
                    <span class="float-left">'+message['user']['name']+'</span>\
                </div>\
                <div class="receive-msg-desc float-left ml-2">\
                    <p class="bg-white m-0 pt-1 pb-1 pl-2 pr-2 rounded">\
                        '+message['ChatMessage']+'\
                    </p>\
                </div>\
            </li>');
          }
        })
        $('.chat-main').show();
        var chatContent = $('#chat');
        chatContent.animate({ scrollTop: chatContent.prop('scrollHeight')}, 100);
        $('#chat-box-message').focus();
      });
    }

    function sendNotification(){
      $.ajax({
        url: '/notification/info',
        type: 'get',
        dataType: 'html',
        success: function(data){
          navigator.serviceWorker.controller.postMessage({'action': data})
          $.ajax({
            url: '/notification/send',
            type: 'GET',
            contentType: 'application/json',
            dataType: 'json',
            complete: function(){
              location.reload();
            }
          });
          }
      });
    }
    function sendChatNotification(){
          navigator.serviceWorker.controller.postMessage({'action': 'Nauja chato žinutė!'});
          $.ajax({
            url: '/notification/send',
            type: 'GET',
            contentType: 'application/json',
            dataType: 'json',
          });
    }

    function updateUserSubscription(subscriptionId){
      $.ajax({
        url: '/updatesub',
        type: 'POST',
        dataType: 'html',
        data: {_token: "{{ csrf_token() }}", subscription: subscriptionId},
        success: function(data){
          if(data == "0"){
            alert('Klaida: notifikacijų įjungti nepavyko.');
          }
        }
      })
    }

    function getChatUnreadMessages(){
      fetch('/chat/unread', {credentials: "same-origin"})
      .then(processStatus)
      .then(parseJson)
      .then((response)=>{
        displayChatNotifications(response);
      })
    }

    function displayChatNotifications(chats){
      if(chats.length === 0)
      {
        $('.notification-counter-badge').addClass('d-none');
        $('.notifications-box').empty();
        $('.notifications-box').append('<div class="row remove-side-margin p-10">\
          <h6 class="text-center">Naujų chato žinučių nėra...</h6>\
        </div>');
        return;
      }
      else {
        $('.notification-counter-badge').text(chats.length);
        $('.notification-counter-badge').removeClass('d-none');
        $('.notifications-box').empty();
        chats.forEach(function(chat){
          $('.notifications-box').append('<div class="row remove-side-margin notifications-box-row" onclick="clickedChatNotification('+chat.orderID+'); $(this).remove();">\
            <span class="badge badge-danger">'+chat.messagesCount+'</span>&#160;'+chat.orderName+'\
          </div>');
        });
      }
    }

    function clickedChatNotification(id){
      return loadChatMessages(id).then(function(){ getChatUnreadMessages(); });
    }
  </script>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="{{asset('media/logo.png')}}" alt="brand_img" />
                </a>
                  <button class="btn btn-warning d-md-none btn-notifications"><span class="fas fa-comments"></span>&#160;<span class="badge badge-danger notification-counter-badge d-none"></span>
                  <div class="notifications-box">
                    <div class="row remove-side-margin p-10">
                      <h6 class="text-center">Naujų chato žinučių nėra...</h6>
                    </div>
                  </div></button>


                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    @if(Auth::user()->role == "Vadybininkas")
                      <ul class="nav nav-tabs nav-stacked mr-auto">
                        <li class="nav-item">
                          <a href="{{ route('active') }}" class="nav-link">Aktyvūs</a>
                        </li>
                        <li class="nav-item">
                          <a href="{{ route('delivered')}}" class="nav-link">Pristatyti</a>
                        </li>
                        <li class="nav-item">
                          <a href="{{ route('deleted')}}" class="nav-link">Atšaukti</a>
                        </li>
                        <li class="nav-item">
                          <a href="{{ route('returned')}}" class="nav-link">Grąžinti</a>
                        </li>
                        <li class="nav-item">
                          <a href="{{ route('history')}}" class="nav-link">Istorija</a>
                        </li>
                      </ul>
                    @endif
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                          <button class="btn btn-warning btn-notifications d-none d-md-inline-block"><span class="fas fa-comments"></span>&#160;<span class="badge badge-danger notification-counter-badge d-none"></span>
                            <div class="notifications-box">
                              <div class="row remove-side-margin p-10">
                                <h6 class="text-center">Naujų chato žinučių nėra...</h6>
                              </div>
                            </div></button>
                        </li>
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                  @if(Auth::user()->role == "Vadybininkas")
                                  <a class="dropdown-item" href="{{route('user.register')}}">Naujas vartotojas</a>
                                @endif
                                  <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{route('user.password')}}">Keisti slaptažodį</a>
                                    <button class="dropdown-item js-on-off-notifications">Įjungti notifikacijas</button>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        Atsijungti
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
          @include('include.messages')
            @yield('content')
            @include('include.chat')

        </main>
    </div>

    <!-- Scripts -->
    <script src="{{asset('js/app.js')}}"></script>
    <script src="{{asset('config.js')}}"></script>
    <script src="{{asset('js/style.js')}}"></script>
    <script>
     /* jshint ignore:start */
     (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
       (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
       m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
     })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
     ga('create', 'UA-53563471-1', 'auto');
     ga('send', 'pageview');
     /* jshint ignore:end */
   </script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
   <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>
