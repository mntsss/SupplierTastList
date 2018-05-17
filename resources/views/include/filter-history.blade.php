  <link rel="stylesheet" type="text/css" href="{{ asset('css/datetimepicker.css')}}"/>
  <div class="card bg-dark">
  <div class="card-body">
    <form method="GET" action="{{route('history')}}">
      {{csrf_field()}}
      <div class="row">
          <div class="col-md">
            <input type="text" name="query" class="form-control input-margin" value="{{old('query')}}" placeholder="Paieška..."/>
        </div>
        <div class="col-md">
            <select name="user" class="form-control input-margin" placeholder="Vartotojas">
              <option value="">
                  Visi vartotojai
                </option>
              @foreach($param['users'] as $user)
                <option value="{{$user->name}}" @if(old('user') == str_replace(' ', '+', $user->name))selected @endif>
                  {{$user->name}}
                </option>
              @endforeach
            </select>
        </div>
      </div>
      <div class="row">
        <div class="col-md">
          <input type="text" id="datepickerFrom" class="form-control input-margin" name="from" placeholder="Nuo:" value="{{old('from')}}"/>
        </div>
        <div class="col-md">
          <input type="text" id="datepickerTil" class="form-control input-margin" name="til" placeholder="Iki:" value="{{old('til')}}"/>
        </div>
      </div>
      <div class="row justify-content-center">
              <button type="submit" class="btn btn-outline-warning  input-margin"><span class="fas fa-search"></span></button>
      </div>
    </form>
  </div>
</div>
<script src="{{asset('js/datetimepicker.js')}}"></script>
<script>
  jQuery(document).ready(function () {
      'use strict';
      jQuery('#datepickerFrom').datetimepicker();
      jQuery('#datepickerTil').datetimepicker();
  });
</script>
