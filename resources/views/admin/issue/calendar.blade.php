@extends('layouts.default')

@section('content')
	<div class="row m0-imp">
		<div class="full content-header mb20">
			<div class="col-xs-12 col-md-5">
                <h4 class="breadcrumb-title">{!! $page['item_title'] !!}</h4>
            </div>

			<div class="col-xs-12 col-md-7 sm-left-md-right">
				<div class="btn-group light">
					<a href="{{ route('admin.issue.index') }}" class="btn thin btn-regular" data-toggle="tooltip" data-placement="bottom" title="Tabular"><i class="fa fa-list"></i></a>
					<a href="{{ route('admin.issue.kanban') }}" class="btn thin btn-regular" data-toggle="tooltip" data-placement="bottom" title="Kanban"><i class="fa fa-align-left rot-90"></i></a>
					<a href="{{ route('admin.issue.calendar') }}" class="btn thin btn-regular active" data-toggle="tooltip" data-placement="bottom" title="Calendar"><i class="fa fa-calendar"></i></a>
				</div>

				<button type="button" class="btn btn-regular only-icon common-filter-btn" data-toggle="tooltip" data-placement="bottom" title="Filter" data-item="issue" data-url="{{ route('admin.filter.form.content', 'issue') }}" data-posturl="{{ route('admin.filter.form.post', 'issue') }}">
					<i class="fa fa-filter"></i>
					@if ($page['current_filter']->param_count)
						<span class="num-notify">{{ $page['current_filter']->param_count }}</span>
					@else
						<span class="num-notify none"></span>
					@endif
				</button>

				@if (permit('issue.create') && permit('import.issue'))
					<button type="button" class="btn btn-regular only-icon import-btn"  data-item="issue" data-url="{{ route('admin.import.csv') }}" data-toggle="tooltip" data-placement="bottom" title="Import Issues"><i class="mdi mdi-file-excel pe-va"></i></button>
				@endif

				@permission('issue.create')
        			<button type="button" id="add-new-btn" class="btn btn-info" data-default="{{ 'issue_owner:' . auth_staff()->id }}"><i class="fa fa-plus-circle"></i> Add Issue</button>
	        	@endpermission
			</div>
		</div> <!-- end full -->

		<div class="full p20-t0">
			<div class="calendar" data-url="{{ route('admin.issue.calendar.data') }}"></div>
		</div>
	</div>
@endsection

@section('modalcreate')
	{{ Form::open(['route' => 'admin.issue.store', 'method' => 'post', 'class' => 'modal-form']) }}
	    @include('admin.issue.partials.form', ['form' => 'create'])
	{{ Form::close() }}
@endsection
