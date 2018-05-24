    <script>
    $('.hide-chat-box').click(function(){
      $('.chat-content').slideToggle();
    });
    </script>
    <link rel="stylesheet" href="{{asset('css/chat.style.css')}}">
            <div class="chat-main" style="display: none">
                <div class="col-md-12 chat-header">
                    <div class="row header-one text-white p-1">
                        <div class="col-8 name pl-2">
                            <h6 class="ml-1 mb-0" id="chat-header"></h6>
                        </div>
                        <div class="col-md-4 options text-right pr-0">
                            <i class="fa fa-times hover text-center pt-1" onclick="$(this).parent().parent().parent().parent().hide()"></i>
                        </div>
                    </div>
                </div>
                <div class="chat-content">
                    <div class="col-md-12 chats pt-3 pl-2 pr-3 pb-3" id="chat">
                        <ul class="p-0" id="chat-content">
                        </ul>
                    </div>
                    <div class="col-md-12 p-2 msg-box border border-primary">
                        <div class="row">
                            <div class="col-md-12 pl-10">
                              <form method="post" id="chatMessageBox">
                                {{ csrf_field() }}
                                <input type="hidden" name="orderID"  id="chat-box-order-id"/>
                                <input type="text" class="border-0" placeholder=" Siųsti žinutę" name="chatMessage" id="chat-box-message" autocomplete="off" style="width:100%"/>
                                <input type="submit" style="display:none;" />
                              </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
            $('#chatMessageBox').submit(function(e){
              $.ajax({
                url:'{{route('chat.new')}}',
                type: 'POST',
                dataType: 'html',
                data: {'orderID': $('#chat-box-order-id').val(), 'chatMessage': $('#chat-box-message').val(), '_token': '{{csrf_token()}}'},
                success: function(){
                  loadChatMessages($('#chat-box-order-id').val());
                  $('#chat-box-message').val('');
                }
              });
              e.preventDefault();
            });

            </script>
