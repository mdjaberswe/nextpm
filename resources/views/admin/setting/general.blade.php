@extends('layouts.master')

@section('content')

	<div class="row">
		@include('partials.subnav.setting')

	    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-10 panel">
	        <h4 class="tab-title">General Settings</h4>

	        {{ Form::open(['route' => 'setting.general.post', 'files' => true, 'class' => 'page-form smooth-save']) }}
	        	<div class="form-group">
	        		<label for="app_name" class="col-xs-12 col-sm-4 col-md-3 col-lg-2">App Name</label>

	        		<div class="col-xs-12 col-sm-8 col-md-9 col-lg-10">
	        			{{ Form::text('app_name', config('setting.app_name'), ['class' => 'form-control']) }}
	        			<span field="app_name" class="validation-error"></span>
	        		</div>
	        	</div> <!-- end form-group -->

	        	<div class="form-group">
	        		<label for="logo" class="col-xs-12 col-sm-4 col-md-3 col-lg-2">Logo</label>

	        		<div class="col-xs-12 col-sm-8 col-md-9 col-lg-10">
	        			<img class="light" src="{{ asset(config('setting.logo')) }}" alt="logo" realtime="logo">
	        			<p class="para-hint">Recommended Dimension : 450 x 75, Max Size : 3000KB</p>
	        			{{ Form::file('logo', ['accept' => 'image/x-png,image/gif,image/jpeg', 'class' => 'plain']) }}
	        			<span field="logo" class="validation-error"></span>
	        		</div>
	        	</div> <!-- end form-group -->

                <div class="form-group">
                    <label for="logo" class="col-xs-12 col-sm-4 col-md-3 col-lg-2">Logo Dark</label>

                    <div class="col-xs-12 col-sm-8 col-md-9 col-lg-10">
                        <img src="{{ asset(config('setting.dark_logo')) }}" alt="dark logo" realtime="dark_logo">
                        <p class="para-hint">Recommended Dimension : 450 x 75, Max Size : 3000KB</p>
                        {{ Form::file('dark_logo', ['accept' => 'image/x-png,image/gif,image/jpeg', 'class' => 'plain']) }}
                        <span field="dark_logo" class="validation-error"></span>
                    </div>
                </div> <!-- end form-group -->

	        	<div class="form-group">
	        		<label for="favicon" class="col-xs-12 col-sm-4 col-md-3 col-lg-2">Favicon</label>

	        		<div class="col-xs-12 col-sm-8 col-md-9 col-lg-10">
	        			<img src="{{ asset(config('setting.favicon')) }}" alt="favicon" realtime="favicon">
	        			<p class="para-hint">Recommended Dimension : 32 x 32, Max Size : 1000KB</p>
	        			{{ Form::file('favicon', ['accept' => 'image/x-png,image/gif,image/jpeg,image/x-icon', 'class' => 'plain']) }}
	        			<span field="favicon" class="validation-error"></span>
	        		</div>
	        	</div> <!-- end form-group -->

	        	<div class="form-group">
	        		<label for="timezone" class="col-xs-12 col-sm-4 col-md-3 col-lg-2">Timezone</label>

	        		<div class="col-xs-12 col-sm-8 col-md-9 col-lg-10">
	        			{{ Form::select('timezone', $time_zones_list, config('setting.timezone'), ['class' => 'form-control select-type-single']) }}
	        			<span field="timezone" class="validation-error"></span>
	        		</div>
	        	</div> <!-- end form-group -->

                @if(config('app.license_type') == 'pro')
    	        	<div class="form-group">
    	        		<label for="purchase_code" class="col-xs-12 col-sm-4 col-md-3 col-lg-2 para-cap tooltip-lg-min">
                            License
                            <i class="hints fa fa-info-circle" data-toggle="tooltip" data-placement="top" data-html="true" title="{{ '1 purchase code = 1 domain, <br>In order to connect the purchase code to a different domain, first click right side deactivate icon and then re-enter the purchase code on a different installation.' }}" ></i>
                        </label>

    	        		<div class="col-xs-12 col-sm-8 col-md-9 col-lg-10">
                            <div class="full right-icon clickable">
                                <i class="fa fa-power-off deactive-license @if (is_null(License::getPurchaseCode())) disabled @endif" data-realtime="deactivate" data-toggle="tooltip" data-placement="top" title="{{ fill_up_space('Deactivate from domain') }}"></i>
    	        			    {{ Form::text('purchase_code', License::getPurchaseCode(), ['class' => 'form-control', 'placeholder' => 'Purchase code']) }}
                                <a class="license-info" type="button">Get a license key</a>
                            </div>
                            <span field="purchase_code" class="validation-error"></span>
    	        		</div>
    	        	</div> <!-- end form-group -->
                @endif

        		<div class="form-group">
        		    <div class="col-xs-12 col-sm-offset-4 col-sm-8 col-md-offset-3 col-md-9 col-lg-offset-2 col-lg-10">
                        <button type="button" class="save btn btn-info ladda-button" data-style="expand-right">
                            <span class="ladda-label" data-status="false">Save</span>
                        </button>
        		    </div>
        		</div> <!-- end form-group -->
	        {{ Form::close() }}
	    </div> <!-- end panel -->
	</div> <!-- end row -->
@endsection

@section('modals')
    @include('partials.modals.license')

    <div class="modal fade top" id="confirm-deactivate-license">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">CONFIRM</h4>
                </div>
                <div class="modal-body">
                    <p class="message para-modal-msg">
                        Are you sure to deactivate the purchase code from this domain? <br>
                        You can re-enter the purchase code on a different installation after deactivated.
                    </p>
                </div> <!-- end modal-body -->
                <div class="modal-footer btn-container">
                    <button type="button" class="no btn btn-primary">No</button>
                    <button type="button" class="yes btn btn-danger">Yes</button>
                </div>
            </div>
        </div> <!-- end modal-dialog -->
    </div> <!-- end confirm-deactivate-license -->
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Reset alert modal and confirm before delete action executes
            $(document).on('click', '.deactive-license', function (event) {
                event.preventDefault();

                if ($(this).hasClass('disabled')) {
                    return false;
                }

                confirmDeactivateLicense();
            });
        });

        /**
         * Confirm alert before deactivating purchase code from this domain
         *
         * @return {void}
         */
        function confirmDeactivateLicense () {
            var message = 'Are you sure to deactivate the purchase code from this domain? <br>' +
                          'You can re-enter the purchase code on a different installation after deactivated.';

            $('#confirm-deactivate-license .message').html(message);

            $('#confirm-deactivate-license').modal({
                show     : true,
                backdrop : false,
                keyboard : false
            });

            // Ajax request to deactivate the purchase code
            $('#confirm-deactivate-license .yes').on('click', function () {
                $('#confirm-deactivate-license .message').html("<img src='{{ asset('plugins/datatable/images/preloader.gif') }}'>");

                $.ajax({
                    type     : 'POST',
                    url      : globalVar.baseUrl + '/deactivate-license',
                    data     : { deactivate: true },
                    dataType : 'JSON',
                    success  : function (data) {
                        if (data.status === true) {
                            $('.deactive-license').addClass('disabled');
                            $('.deactive-license').closest('.form-group').find('input').val('');
                            $('#confirm-deactivate-license .message').html("<span class='fa fa-times-circle color-danger'></span> The Purchase code has been deactivated from this domain.");
                            delayModalHide('#confirm-deactivate-license', 2);
                        } else {
                            $('#confirm-deactivate-license .message').html("<span class='fa fa-exclamation-circle color-danger'></span> Operation failed. Please try again.");
                            delayModalHide('#confirm-deactivate-license', 2);
                        }
                    },
                    error : function (jqXHR, textStatus, errorThrown) {
                        // ajaxErrorHandler defined in js/app.js
                        ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
                    }
                });

                $(this).parent().find('.btn').off('click');
            });

            // Retreat execution of deleting data
            $('#confirm-deactivate-license .no').on('click', function () {
                $('#confirm-deactivate-license').modal('hide');
                $(this).parent().find('.btn').off('click');
            });
        }
    </script>
@endpush
