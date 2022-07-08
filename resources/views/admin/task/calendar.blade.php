@extends('layouts.default')

@section('content')
	<div class="row m0-imp">
		<div class="full content-header mb20">
            <div class="col-xs-12 col-md-5">
                <h4 class="breadcrumb-title">{!! $page['item_title'] !!}</h4>
            </div>

			<div class="col-xs-12 col-md-7 sm-left-md-right">
				<div class="btn-group light">
					<a href="{{ route('admin.task.index') }}" class="btn thin btn-regular" data-toggle="tooltip" data-placement="bottom" title="Tabular"><i class="fa fa-list"></i></a>
					<a href="{{ route('admin.task.kanban') }}" class="btn thin btn-regular" data-toggle="tooltip" data-placement="bottom" title="Kanban"><i class="fa fa-align-left rot-90"></i></a>
					<a href="{{ route('admin.task.calendar') }}" class="btn thin btn-regular active" data-toggle="tooltip" data-placement="bottom" title="Calendar"><i class="fa fa-calendar"></i></a>
				</div>

				<button type="button" class="btn btn-regular only-icon common-filter-btn" data-toggle="tooltip" data-placement="bottom" title="Filter" data-item="task" data-url="{{ route('admin.filter.form.content', 'task') }}" data-posturl="{{ route('admin.filter.form.post', 'task') }}">
					<i class="fa fa-filter"></i>
					@if ($page['current_filter']->param_count)
						<span class="num-notify">{{ $page['current_filter']->param_count }}</span>
					@else
						<span class="num-notify none"></span>
					@endif
				</button>

				@if (permit('task.create') && permit('import.task'))
					<button type="button" class="btn btn-regular only-icon import-btn"  data-item="task" data-url="{{ route('admin.import.csv') }}" data-toggle="tooltip" data-placement="bottom" title="Import Tasks"><i class="mdi mdi-file-excel pe-va"></i></button>
				@endif

				@permission('task.create')
        			<button type="button" id="add-new-btn" class="btn btn-info" data-default="{{ 'task_owner:' . auth_staff()->id }}"><i class="fa fa-plus-circle"></i> Add Task</button>
	        	@endpermission
			</div>
		</div> <!-- end content-header -->

		<div class="full p20-t0">
			<div class="calendar" data-url="{{ route('admin.task.calendar.data') }}"></div>
		</div>
	</div>
@endsection

@section('modalcreate')
	{{ Form::open(['route' => 'admin.task.store', 'method' => 'post', 'class' => 'modal-form']) }}
	    @include('admin.task.partials.form', ['form' => 'create'])
	{{ Form::close() }}
@endsection
