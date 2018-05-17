@extends('layouts.manager')

@section('content')
  <script>
  $(document).ready(function(){

    $('#noinfo').change(function(){
    if(this.checked){
      $('#info-form').addClass('disabled-div');
        $('#info-form').find('input').prop({
          required: false
      });
    }
    else {
      $('#info-form').removeClass('disabled-div');
        $('#info-form').find('input').prop({
          required: true
      });
    }
  });

  navigator.geolocation.getCurrentPosition(function(position) {
    $('#cooX').val(position.coords.latitude);
    $('#cooY').val(position.coords.longitude);
  });
});
  function getAddress(){
    var latitude = $('#cooX').val();
    var longitude = $('#cooY').val();

    $.ajax({
      accepts: {mycustomtype: 'application/json'},
      method: 'get',
      url: 'https://maps.googleapis.com/maps/api/geocode/json?latlng='+latitude+','+longitude+'&key=AIzaSyBbxtYPVuS5O-HzP59scvJksaWLZL0byCQ',
    }).done(function ( result ){
      $('#orderAddress').val(result['results'][0]['address_components'][1]['short_name']+" "+result['results'][0]['address_components'][0]['short_name']+" "+result['results'][0]['address_components'][2]['short_name']);
    });
  }
  </script>
  <div class="container">
    <div class="row justify-content-center">
      <div class="card bg-dark" style="margin-top: 15px !important">
        <div class="card-header">
          Iš kur gauta
        </div>
        <div class="card-body">
          <form class="form-horizontal" method="post" action="{{route('order.found.submit')}}">
            {{ csrf_field() }}
            <div class="form-group row">

                  <label for="noinfo" class="control-label col-9">Nereikia rašyti...</label>
                  <div class="col-3">
                    <input id="noinfo" type="checkbox" class="form-control"  style="height:20px" name="noinfo" value="1" />
                  </div>
            </div>
            <div id="info-form">
            <div class="form-group row">
              <div class="input-group">
                <input type="text" id="orderAddress" name="orderAddress" class="form-control" value="{{old('orderAddress')}}" maxlength="50" placeholder="Adresas" required />
                <div class="input-group-append">
                  <div class="input-group-text">
                    <button type="button" class="btn btn-outline-warning" onclick="getAddress()"><span class="fas fa-map-marker-alt"></span></button>
                  </div>
                </div>
              </div>
            </div>
            <div class="form-group row">
              <input id="cooX" type="hidden" name="cooX" />
              <input id="cooY" type="hidden" name="cooY" />
              <input type="hidden" name="id" value="{{$id}}"/>
              <div class="input-group">
                <div class="input-group-prepend">
                  <div class="input-group-text" style="width:42px !important">
                    <i class="fa fa-phone"></i>
                  </div>
                </div>
                    <input type="text" name="orderPhone" class="form-control" value="{{old('orderPhone')}}" maxlength="25" placeholder="Tel. nr. / Vieta" required />

                </div>
              </div>
            <div class="form-group row">
              <div class="input-group">
                <div class="input-group-prepend">
                  <div class="input-group-text" style="width:42px !important">
                    <i class="fas fa-euro-sign"></i>
                  </div>
                </div>
                <input type="number" step="0.01" name="price" class="form-control" value="{{old('price')}}" placeholder="Kaina" />
              </div>
            </div>

            </div>
            <div class="row form-group justify-content-center">
              <button type="submit" class="btn-outline-success">Išsaugoti</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

@endsection
