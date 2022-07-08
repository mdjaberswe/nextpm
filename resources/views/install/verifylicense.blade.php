@extends('layouts.install')

@section('content')
    <div class="full center-panel">
        <div class="full panel-hd">
            <h2 class="border-none">{{ $page['title'] }}</h2>

            <div class="full p20-t0">
                {{ Form::open(['route' => 'license.post.verification', 'class' => 'page-form smooth-save']) }}
                    <div class="form-group">
                        <div class="col-xs-12">
                            <div class="full right-icon clickable">
                                <i class="fa fa-key license-info" data-toggle="tooltip" data-placement="top" title="{{ fill_up_space('Get a license key') }}"></i>
                                {{ Form::text('purchase_code', null, ['class' => 'form-control', 'placeholder' => 'Enter your purchase code']) }}
                            </div>
                            <span field="purchase_code" class="validation-error"></span>
                        </div>
                    </div> <!-- end form-group -->

                    <div class="form-group">
                        <div class="col-xs-12 center">
                            <button type="submit" name="save" class="save btn btn-info ladda-button" data-style="expand-right">
                                <span class="ladda-label" data-status="false">Activate</span>
                            </button>
                        </div>
                    </div> <!-- end form-group -->
                {{ Form::close() }}
            </div>
        </div>
    </div>
@endsection

@section('modals')
    @include('partials.modals.license')
@endsection
