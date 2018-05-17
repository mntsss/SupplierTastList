@foreach($models as $m)
  <option value="{{$m->model}}">
    {{$m->model}}
  </option>
@endforeach
