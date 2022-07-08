@extends('layouts.master')

@section('content')

    <div class="row">
        @include('partials.subnav.setting')

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-10 panel">
            <h4 class="tab-title">Email Settings</h4>

            {{ Form::open(['route' => 'setting.email.post', 'class' => 'page-form smooth-save']) }}
                <div class="form-group">
                    <label for="mail_from_address" class="col-xs-12 col-sm-4 col-md-3 col-lg-2">From Email <span class="color-danger">*</span></label>

                    <div class="col-xs-12 col-sm-8 col-md-9 col-lg-10">
                        {{ Form::text('mail_from_address', $data['mail_from_address'], ['class' => 'form-control']) }}
                        <span field="mail_from_address" class="validation-error"></span>
                    </div>
                </div> <!-- end form-group -->

                <div class="form-group">
                    <label for="mail_from_name" class="col-xs-12 col-sm-4 col-md-3 col-lg-2">From Name <span class="color-danger">*</span></label>

                    <div class="col-xs-12 col-sm-8 col-md-9 col-lg-10">
                        {{ Form::text('mail_from_name', $data['mail_from_name'], ['class' => 'form-control']) }}
                        <span field="mail_from_name" class="validation-error"></span>
                    </div>
                </div> <!-- end form-group -->

                <div class="form-group">
                    <label for="mail_driver" class="col-xs-12 col-sm-4 col-md-3 col-lg-2">Default Mailer</label>

                    <div class="col-xs-12 col-sm-8 col-md-9 col-lg-10">
                        <select name="mail_driver" class="form-control select-type-single parentfield" data-init="{{ $data['mail_driver'] }}">
                            <option value="mail" childfield="mail" {{ tag_attr('mail', $data['mail_driver'], 'selected') }}>PHP mail()</option>
                            <option value="smtp" childfield="smtp" {{ tag_attr('smtp', $data['mail_driver'], 'selected') }}>SMTP</option>
                        </select>
                        <span field="mail_driver" class="validation-error"></span>
                        <span field="smtp_connection" class="validation-error"></span>
                    </div>
                </div> <!-- end form-group -->

                <div class="{{ append_css_class('form-group', 'none', $data['mail_driver_type'], 'smtp', false) }}">
                    <label for="mail_host" class="col-xs-12 col-sm-4 col-md-3 col-lg-2">Hostname <span class="color-danger">*</span></label>

                    <div class="col-xs-12 col-sm-8 col-md-9 col-lg-10">
                        {{ Form::text('mail_host', config('setting.mail_host'), ['class' => 'form-control', 'parent' => 'smtp']) }}
                        <span field="mail_host" class="validation-error"></span>
                    </div>
                </div> <!-- end form-group -->

                <div class="{{ append_css_class('form-group', 'none', $data['mail_driver_type'], 'smtp', false) }}">
                    <label for="mail_username" class="col-xs-12 col-sm-4 col-md-3 col-lg-2">Username <span class="color-danger">*</span></label>

                    <div class="col-xs-12 col-sm-8 col-md-9 col-lg-10">
                        {{ Form::text('mail_username', check_before_decrypt(config('setting.mail_username')), ['class' => 'form-control', 'parent' => 'smtp']) }}
                        <span field="mail_username" class="validation-error"></span>
                    </div>
                </div> <!-- end form-group -->

                <div class="{{ append_css_class('form-group', 'none', $data['mail_driver_type'], 'smtp', false) }}">
                    <label for="mail_password" class="col-xs-12 col-sm-4 col-md-3 col-lg-2">Password <span class="color-danger">*</span></label>

                    <div class="col-xs-12 col-sm-8 col-md-9 col-lg-10">
                        {{ Form::text('mail_password', check_before_decrypt(config('setting.mail_password')), ['class' => 'form-control', 'parent' => 'smtp']) }}
                        <span field="mail_password" class="validation-error"></span>
                    </div>
                </div> <!-- end form-group -->

                <div class="{{ append_css_class('form-group', 'none', $data['mail_driver_type'], 'smtp', false) }}">
                    <label for="mail_port" class="col-xs-12 col-sm-4 col-md-3 col-lg-2">Port <span class="color-danger">*</span></label>

                    <div class="col-xs-12 col-sm-8 col-md-9 col-lg-10">
                        {{ Form::text('mail_port', config('setting.mail_port'), ['class' => 'form-control', 'parent' => 'smtp']) }}
                        <span field="mail_port" class="validation-error"></span>
                    </div>
                </div> <!-- end form-group -->

                <div class="{{ append_css_class('form-group', 'none', $data['mail_driver_type'], 'smtp', false) }}">
                    <label for="mail_encryption" class="col-xs-12 col-sm-4 col-md-3 col-lg-2">Encryption <span class="color-danger">*</span></label>

                    <div class="col-xs-12 col-sm-8 col-md-9 col-lg-10">
                        {{ Form::select('mail_encryption', ['tls' => 'TLS', 'ssl' => 'SSL'], config('setting.mail_encryption'), ['class' => 'form-control select-type-single-b', 'parent' => 'smtp']) }}
                        <span field="mail_encryption" class="validation-error"></span>
                    </div>
                </div> <!-- end form-group -->

                <div class="form-group">
                    <div class="col-xs-12 col-sm-offset-4 col-sm-8 col-md-offset-3 col-md-9 col-lg-offset-2 col-lg-10">
                        <button type="button" class="save btn btn-info ladda-button" data-style="expand-right">
                            <span class="ladda-label" data-status="false">Save</span>
                        </button>
                    </div>
                </div> <!-- end form-group -->
            {{ Form::close() }}
        </div> <!-- end div-type-a -->
    </div> <!-- end row -->

@endsection
