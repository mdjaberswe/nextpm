@extends('layouts.install')

@section('content')
	<div class="full center-panel">
		<div class="full panel-hd">
			<h2>{{ $page['title'] }}</h2>
		</div>

		@include('install.partials.progress-nav')

		{{ Form::open(['route' => 'install.post.config', 'class' => 'page-form smooth-save']) }}
			<div class="full panel-cont">
				<div class="full">
					<h3><i class="mdi mdi-domain"></i> General</h3>
				</div>

				<div class="full form-cont">
					<div class="full">
						<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
							<div class="form-group">
								<label for="app_name">App Name</label>
								{{ Form::text('app_name', $form_data['app_name'], ['class' => 'form-control']) }}
								<span field="app_name" class="validation-error"></span>
							</div>
						</div>

						<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
							<div class="form-group">
								<label for="timezone">Timezone <span class="color-danger">*</span></label>
								{{ Form::select('timezone', $time_zones_list, $form_data['timezone'], ['class' => 'form-control select-type-single']) }}
								<span field="timezone" class="validation-error"></span>
							</div>
						</div>
					</div>

                    @if(config('app.license_type') == 'pro')
    					<div class="full">
    						<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
    							<div class="form-group">
    								<label for="purchase_code">License - <a class="license-info" type="button">Get a license key</a></label>
    								{{ Form::text('purchase_code', $form_data['purchase_code'], ['class' => 'form-control', 'placeholder' => 'Purchase code']) }}
                                    <span field="purchase_code" class="validation-error"></span>
    							</div>
    						</div>
    					</div>
                    @endif
				</div> <!-- end form-cont -->

				<div class="full">
					<h3><i class="mdi mdi-account-tie"></i> Administrator information</h3>
				</div>

				<div class="full form-cont">
					<div class="full">
						<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
							<div class="form-group">
								<label for="first_name">First Name <span class="color-danger">*</span></label>
								{{ Form::text('first_name', $form_data['first_name'], ['class' => 'form-control']) }}
								<span field="first_name" class="validation-error"></span>
							</div>
						</div>

						<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
							<div class="form-group">
								<label for="last_name">Last Name <span class="color-danger">*</span></label>
								{{ Form::text('last_name', $form_data['last_name'], ['class' => 'form-control']) }}
								<span field="last_name" class="validation-error"></span>
							</div>
						</div>
					</div>

					<div class="full">
						<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
							<div class="form-group">
								<label for="email">Email <span class="color-danger">*</span></label>
								{{ Form::text('email', $form_data['email'], ['class' => 'form-control']) }}
								<span field="email" class="validation-error"></span>
							</div>
						</div>

						<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
							<div class="form-group">
								<label for="password">Password <span class="color-danger">*</span></label>
								{{ Form::password('password', ['class' => 'form-control']) }}
								<span field="password" class="validation-error"></span>
							</div>
						</div>
					</div>
				</div> <!-- end form-cont -->

                <div class="full">
                    <h3><i class="mdi mdi-email"></i> System email configuration</h3>
                </div>

                <div class="full form-cont">
                    <div class="full">
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                            <div class="form-group small-select">
                                <label for="mail_driver">Default Mailer</label>
                                <select name="mail_driver" class="form-control select-type-single parentfield" data-init="{{ $form_data['mail_driver'] }}">
                                    <option value="mail" childfield="mail" {{ tag_attr('mail', $form_data['mail_driver'], 'selected') }}>PHP mail()</option>
                                    <option value="smtp" childfield="smtp" {{ tag_attr('smtp', $form_data['mail_driver'], 'selected') }}>SMTP</option>
                                </select>
                                <div class="full">
                                    <span field="mail_driver" class="validation-error"></span>
                                    <span field="smtp_connection" class="validation-error"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="full">
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                            <div class="{{ $mail_css['smtp'] }}">
                                <label for="mail_host">Hostname <span class="color-danger">*</span></label>
                                {{ Form::text('mail_host', $form_data['mail_host'], ['class' => 'form-control', 'parent' => 'smtp']) }}
                                <span field="mail_host" class="validation-error"></span>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                            <div class="{{ $mail_css['smtp'] }}">
                                <label for="mail_port">Port <span class="color-danger">*</span></label>
                                {{ Form::text('mail_port', $form_data['mail_port'], ['class' => 'form-control', 'parent' => 'smtp']) }}
                                <span field="mail_port" class="validation-error"></span>
                            </div>
                        </div>
                    </div>

                    <div class="full">
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                            <div class="{{ $mail_css['smtp'] }}">
                                <label for="mail_username">Username <span class="color-danger">*</span></label>
                                {{ Form::text('mail_username', $form_data['mail_username'], ['class' => 'form-control', 'parent' => 'smtp']) }}
                                <span field="mail_username" class="validation-error"></span>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                            <div class="{{ $mail_css['smtp'] }}">
                                <label for="mail_password">Password <span class="color-danger">*</span></label>
                                {{ Form::text('mail_password', $form_data['mail_password'], ['class' => 'form-control', 'parent' => 'smtp']) }}
                                <span field="mail_password" class="validation-error"></span>
                            </div>
                        </div>
                    </div>

                    <div class="full">
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                            <div class="{{ $mail_css['smtp'] }}">
                                <label for="mail_encryption">Encryption <span class="color-danger">*</span></label>
                                {{ Form::select('mail_encryption', ['tls' => 'TLS', 'ssl' => 'SSL'], $form_data['mail_encryption'], ['class' => 'form-control select-type-single-b', 'parent' => 'smtp']) }}
                                <span field="mail_encryption" class="validation-error"></span>
                            </div>
                        </div>
                    </div>
                </div> <!-- end form-cont -->
			</div> <!-- end panel-cont -->

			<div class="full panel-ft">
				<button class="save btn btn-info ladda-button">
                    <span class="ladda-label" data-status="false">
                        Next <i class="mdi mdi-arrow-right-bold right"></i>
                    </span>
                </button>
			</div>
		{{ Form::close() }}
	</div>
@endsection

@section('modals')
    @include('partials.modals.license')
@endsection
