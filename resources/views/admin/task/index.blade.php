@extends('templates.listing')

@section('panelbtn')
	<div class="btn-group light">
		<a href="{{ route('admin.task.index') }}" class="btn thin btn-regular active" data-toggle="tooltip" data-placement="bottom" title="Tabular"><i class="fa fa-list"></i></a>
		<a href="{{ route('admin.task.kanban') }}" class="btn thin btn-regular" data-toggle="tooltip" data-placement="bottom" title="Kanban"><i class="fa fa-align-left rot-90"></i></a>
		<a href="{{ route('admin.task.calendar') }}" class="btn thin btn-regular" data-toggle="tooltip" data-placement="bottom" title="Calendar"><i class="fa fa-calendar"></i></a>
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
@endsection
