@extends('layouts.manager')

@section('content')
  <link rel="stylesheet" type="text/css" href="{{ asset('css/datetimepicker.css')}}"/>
  <div class="container">
    <div class="row">
      <div class="col-md-10">
        <div class="card bg-dark">
          <div class="card-header">
            Redaguoti užsakymą
          </div>
          <div class="card-body">
            <form method="POST" action="{{ route('order.edit.submit') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{$param['order']->id}}" />
                <div class="form-group row">
                    <label for="name" class="col-md-4 col-form-label text-md-right">Pavadinimas</label>

                    <div class="col-md-6">
                        <input id="name" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ $param['order']->name }}" required autofocus>

                        @if ($errors->has('name'))
                            <span class="invalid-feedback">
                                <strong>{{ $errors->first('name') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <label for="code" class="col-md-4 col-form-label text-md-right">Delatės kodas</label>

                    <div class="col-md-6">
                        <input id="code" type="text" class="form-control{{ $errors->has('code') ? ' is-invalid' : '' }}" name="code" value="{{ $param['order']->code }}">

                        @if ($errors->has('code'))
                            <span class="invalid-feedback">
                                <strong>{{ $errors->first('code') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>
                <div class="form-group row">
                    <label for="desciption" class="col-md-4 col-form-label text-md-right">Detalės aprašymas</label>

                    <div class="col-md-6">
                        <textarea id="description" type="text" class="form-control{{ $errors->has('description') ? ' is-invalid' : '' }}" name="description">{{ $param['order']->description }}</textarea>

                        @if ($errors->has('description'))
                            <span class="invalid-feedback">
                                <strong>{{ $errors->first('description') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>


                <div class="form-group row">
                    <label for="address" class="col-md-4 col-form-label text-md-right">Adresas</label>

                    <div class="col-md-6">
                        <input id="address" type="text" class="form-control{{ $errors->has('address') ? ' is-invalid' : '' }}" name="address" value="{{ $param['order']->address}}">

                        @if ($errors->has('address'))
                            <span class="invalid-feedback">
                                <strong>{{ $errors->first('address') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <label for="address" class="col-md-4 col-form-label text-md-right">Kontaktinis telefonas</label>

                    <div class="col-md-6">
                        <input id="phone" type="text" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" name="phone" value="{{ $param['order']->phone }}" maxlength="15">

                        @if ($errors->has('phone'))
                            <span class="invalid-feedback">
                                <strong>{{ $errors->first('phone') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>


                <div class="form-group row">
                    <label for="important" class="col-md-4 col-form-label text-md-right">Skubu (atvežti kuo greičiau)</label>

                    <div class="col-md-6 text-left">
                        <input id="important" type="checkbox" class="form-control{{ $errors->has('important') ? ' is-invalid' : '' }}" name="important" value="1" style="height: 35px" @if($param['order']->important) checked @endif>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="timeLimit" class="col-md-4 col-form-label text-md-right">Laiko limitas (atvežti iki ...)</label>

                    <div class="col-md-6">
                        <input id="timeLimit" type="text" class="form-control{{ $errors->has('timeLimit') ? ' is-invalid' : '' }}" name="timeLimit" value="{{ $param['order']->timeLimit }}">

                        @if ($errors->has('timeLimit'))
                            <span class="invalid-feedback">
                                <strong>{{ $errors->first('timeLimit') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="form-group row mb-0 justify-content-center">

                        <button type="submit" class="btn btn-outline-light">
                            Išsaugoti
                        </button>

                </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="{{asset('js/datetimepicker.js')}}"></script>

          <script>
              jQuery(document).ready(function () {
                  'use strict';
                  jQuery('#timeLimit').datetimepicker();
              });
          </script>
@endsection
