@extends('layouts.default')

@section('content')
	<div class="row m0-imp">
    	<div class="full content-header mb20">
            <div class="col-xs-12 col-md-5">
    	        <h4 class="breadcrumb-title">
    	        	<ol class="breadcrumb dropdown-view">
    	        		<li>
    	        			<a href="{{ route('admin.event.calendar') }}">Calendar</a>
    	        		</li>
    	        		<li class="active">
            				<select name="calendar_filter" class="form-control breadcrumb-select" data-default="{{ $page['filter_param'] }}">
        						<optgroup label="SYSTEM">
        							{!! $page['filter_dropdown'] !!}
        						</optgroup>
            				</select>
    	        		</li>
    	        	</ol>
    	        </h4>
            </div>

	        <div class="col-xs-12 col-md-7 sm-left-md-right">
                @permission('event.view')
		        	<div class="btn-group light">
		        		<a href="{{ route('admin.event.calendar') }}" class="btn thin btn-regular active" data-toggle="tooltip" data-placement="bottom" title="Calendar"><i class="fa fa-calendar"></i></a>
		        		<a href="{{ route('admin.event.index') }}" class="btn thin btn-regular" data-toggle="tooltip" data-placement="bottom" title="Tabular"><i class="fa fa-list"></i></a>
		        	</div>
                @endpermission

	        	@permission('event.create')
                    <button type="button" id="add-new-btn" class="btn btn-info" data-default="{{ 'event_owner:' . auth_staff()->id }}"><i class="fa fa-plus-circle"></i> Add Event</button>
                @endpermission
	        </div>
	    </div>

	    <div class="full p20-t0">
	    	<div class="calendar" data-url="{{ route('admin.event.calendar.data') }}"></div>
	    </div>
	</div> <!-- end row -->
@endsection

@section('modalcreate')
    {{ Form::open(['route' => 'admin.event.store', 'method' => 'post', 'class' => 'modal-form']) }}
        @include('admin.event.partials.form', ['form' => 'create'])
    {{ Form::close() }}
@endsection

@push('scripts')
	@include('admin.event.partials.script')
@endpush
