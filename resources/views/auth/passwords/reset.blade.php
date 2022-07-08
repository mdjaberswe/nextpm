@extends('layouts.auth', ['page' => ['animation' => false]])

<!-- Main Content -->
@section('content')
    <div class="container">
        <div class="row">
            <div class="form-box-container col-xs-12 col-sm-offset-2 col-sm-8 col-md-offset-3 col-md-6">
                <div class="center-logo">
                    @if (not_null_empty(config('setting.dark_logo')) && file_exists(public_path(config('setting.dark_logo'))))
                        <a href="{{ route('home') }}" class="logo-link logo-img"><img src="{{ asset(config('setting.dark_logo')) }}" alt="logo" realtime="dark_logo"/></a>
                    @else
                        @if (not_null_empty(config('setting.logo')) && file_exists(public_path(config('setting.logo'))))
                            <a href="{{ route('home') }}" class="logo-link logo-img"><img src="{{ asset(config('setting.logo')) }}" alt="logo" realtime="logo"/></a>
                        @else
                            <a href="{{ route('home') }}" class="logo-link">{{ config('app.name') }}</a>
                        @endif
                    @endif
                </div>

                <h3 class="title"><i class="mdi mdi-lock-reset"></i> Reset Password</h3>

                {{ Form::open(['route' => 'password.reset', 'method' => 'post', 'class' => 'page-form']) }}
                    {{ Form::hidden('token', $token) }}

                    <div class="form-group">
                        <label for="email" class="col-xs-12 col-md-3 sm-align-left">{!! fill_up_space('E-Mail Address') !!}</label>

                        <div class="col-xs-12 col-md-9">
                            {{ Form::text('email', $email or old('email'), ['class' => 'form-control', 'id' => 'email']) }}

                            @if ($errors->has('email'))
                                <span field="email" class="validation-error">{{ $errors->first('email') }}</span>
                            @endif
                        </div>
                    </div> <!-- end form-group -->

                    <div class="form-group">
                        <label for="password" class="col-xs-12 col-md-3 sm-align-left">Password</label>

                        <div class="col-xs-12 col-md-9">
                            {{ Form::password('password', ['class' => 'form-control', 'id' => 'password']) }}

                            @if ($errors->has('password'))
                                <span field="password" class="validation-error">{{ $errors->first('password') }}</span>
                            @endif
                        </div>
                    </div> <!-- end form-group -->

                    <div class="form-group">
                        <label for="password-confirm" class="col-xs-12 col-md-3 sm-align-left">{!! fill_up_space('Confirm Password') !!}</label>

                        <div class="col-xs-12 col-md-9">
                            {{ Form::password('password_confirmation', ['class' => 'form-control', 'id' => 'password-confirm']) }}

                            @if ($errors->has('password_confirmation'))
                                <span field="password" class="validation-error">{{ $errors->first('password_confirmation') }}</span>
                            @endif
                        </div>
                    </div> <!-- end form-group -->

                    <div class="form-group">
                        <div class="col-xs-12 col-md-offset-3 col-md-9">
                            {{ Form::submit('Reset Password', ['name' => 'reset', 'class' => 'btn btn-info']) }}
                            {!! link_to_route('home', 'Cancel', [], ['class' => 'btn btn-default']) !!}
                        </div>
                    </div> <!-- end form-group -->
                {{ Form::close() }}
            </div> <!-- end form-box-container -->
        </div> <!-- end row -->
    </div> <!-- end container -->
@endsection
