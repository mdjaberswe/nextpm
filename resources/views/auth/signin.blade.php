@extends('layouts.auth')

@section('content')
    <div class="row">
        <div id="auth-form-container" class="form-box-container col-xs-12 col-sm-offset-2 col-sm-8 col-md-offset-3 col-md-6 col-lg-offset-4 col-lg-4">
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

            <h3 class="title"><i class="mdi mdi-login"></i> Sign In</h3>

            {{ Form::open(['route' => 'auth.signin.post', 'method' => 'post', 'class' => 'smooth-validation']) }}
                <div class="form-group">
                    <label for="email" class="lbl">Email</label>
                    <i class="fa fa-envelope"></i>
                    {{ Form::text('email', cache()->has('user_email') && not_null_empty(cache('user_email')) ? cache('user_email') : null, ['class' => 'input']) }}
                    <span error-field="email" class="validation-error">{{ $errors->first('email', ':message') }}</span>
                </div> <!-- end form-group -->

                <div class="form-group">
                    <label for="password" class="lbl">Password</label>
                    <i class="fa fa-key"></i>
                    {{ Form::password('password', ['class' => 'input']) }}
                    <span error-field="password" class="validation-error">{{ $errors->first('password', ':message') }}</span>
                </div> <!-- end form-group -->

                <div class="form-group">
                    <div class="half">
                        <p class="para-checkbox pretty success smooth">
                            <input type="checkbox" name="remember" value="1" @if (cache()->has('remember_checked') && cache('remember_checked')) checked @endif>
                            <label><i class="mdi mdi-check"></i></label>
                            <span>Remember me</span>
                        </p>
                    </div>

                    <div class="half">
                        <a href="{{ route('password.reset.form') }}" class="right-justify">Forget Password?</a>
                    </div>
                </div> <!-- end form-group -->

                @if (\Session::has('error_msg'))
                    <div class="full">
                        <span class="danger-message validation-error">{{ \Session::get('error_msg') }}</span>
                    </div>
                @endif

                <div class="form-group">
                    <button type="submit" class="btn btn-info submit ladda-button stand" data-style="expand-right">
                        <span class="ladda-label" data-status="false">Sign In</span>
                    </button>
                </div> <!-- end form-group -->
            {{ Form::close() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Adjust animation pool box height according to windows height
            if ($(window).height() > 1000) {
                var rotateHeight = parseInt($(window).height() + 500, 10);

                $.keyframe.define([{
                    name : 'rotateUpHeight',
                    '0%' : {
                        '-webkit-transform' : 'translateY(0)',
                        transform           : 'translateY(0)'
                    },
                    '100%' : {
                        '-webkit-transform' : 'translateY(-' + rotateHeight + 'px) rotate(500deg)',
                        transform           : 'translateY(-' + rotateHeight + 'px) rotate(500deg)'
                    }
                }]);

                $('.pools li').playKeyframe({
                    name           : 'rotateUpHeight',
                    duration       : '15s',
                    timingFunction : 'linear',
                    iterationCount : 'infinite',
                    direction      : 'normal',
                    fillMode       : 'forwards'
                });
            }

            // If input value is not empty then add css to focus input field.
            $('.input').each(function (index, uiInput) {
                if ($(uiInput).val() !== '') {
                    $(uiInput).parent().find('label').addClass('focus');
                    $(uiInput).parent().find('i').addClass('focus');
                    $(uiInput).parent().find('input').addClass('focus');
                }
            });

            // Focus label when on clicked.
            $('.lbl').on('click', function () {
                if (!$(this).hasClass('focus')) {
                    $(this).closest('.form-group').find('input').focus();
                    $(this).addClass('focus');
                }
            });

            // Focus input field when on clicked.
            $('.input').on('click', function () {
                if (!$(this).closest('.form-group').find('.lbl').hasClass('focus')) {
                    $(this).focus();
                    $(this).closest('.form-group').find('.lbl').addClass('focus');
                }
            });

            // If input value is not empty then add css to focus input field.
            $('.input').on('focus input keyup keydown blur change', function () {
                if ($(this).val() !== '') {
                    $(this).parent().find('label').addClass('focus');
                    $(this).parent().find('i').addClass('focus');
                    $(this).parent().find('input').addClass('focus');
                }
            });

            // If input value is empty then remove focus on the input field.
            $('.input').on('focusout', function () {
                if ($(this).val() === '') {
                    $(this).parent().find('label').removeClass('focus');
                }

                $(this).parent().find('i').removeClass('focus');
                $(this).parent().find('input').removeClass('focus');
            });
        });
    </script>
@endpush
