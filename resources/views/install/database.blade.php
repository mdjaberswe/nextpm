@extends('layouts.install')

@section('content')
	<div class="full center-panel">
		<div class="full panel-hd">
			<h2>{{ $page['title'] }}</h2>
		</div>

		@include('install.partials.progress-nav')

		{{ Form::open(['route' => 'install.post.database', 'class' => 'page-form smooth-save']) }}
			<div class="full panel-cont">
				<div class="full">
					<h3><i class="mdi mdi-database"></i> Database configuration</h3>
					<span field="mysql_connection" class="validation-error"></span>
				</div>

				<div class="full form-cont">
					<div class="full">
						<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
							<div class="form-group">
								<label for="hostname">Hostname <span class="color-danger">*</span></label>
								{{ Form::text('hostname', $form_data['hostname'], ['class' => 'form-control']) }}
								<span field="hostname" class="validation-error"></span>
							</div>
						</div>

						<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
							<div class="form-group">
								<label for="port">Port <span class="color-danger">*</span></label>
								{{ Form::text('port', $form_data['port'], ['class' => 'form-control']) }}
								<span field="port" class="validation-error"></span>
							</div>
						</div>
					</div>

					<div class="full">
						<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
							<div class="form-group">
								<label for="username">Username <span class="color-danger">*</span></label>
								{{ Form::text('username', $form_data['username'], ['class' => 'form-control']) }}
								<span field="username" class="validation-error"></span>
							</div>
						</div>

						<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
							<div class="form-group">
								<label for="password">Password</label>
								{{ Form::password('password', ['class' => 'form-control']) }}
								<span field="password" class="validation-error"></span>
							</div>
						</div>
					</div>

					<div class="full">
						<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
							<div class="form-group">
								<label for="database_name">Database Name <span class="color-danger">*</span></label>
								{{ Form::text('database_name', $form_data['database_name'], ['class' => 'form-control']) }}
								<span field="database_name" class="validation-error"></span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="full panel-ft">
                <button class="save btn btn-info ladda-button">
                    <span class="ladda-label" data-status="false">
                        Save <i class="mdi mdi-arrow-right-bold right"></i>
                    </span>
                </button>
			</div>
		{{ Form::close() }}
	</div>
@endsection
