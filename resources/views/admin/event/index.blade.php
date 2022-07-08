@extends('templates.listing')

@section('panelbtn')
	<div class="btn-group light">
		<a href="{{ route('admin.event.calendar') }}" class="btn thin btn-regular" data-toggle="tooltip" data-placement="bottom" title="Calendar"><i class="fa fa-calendar"></i></a>
		<a href="{{ route('admin.event.index') }}" class="btn thin btn-regular active" data-toggle="tooltip" data-placement="bottom" title="Tabular"><i class="fa fa-list"></i></a>
	</div>

	<button type="button" class="btn btn-regular only-icon common-filter-btn" data-toggle="tooltip" data-placement="bottom" title="Filter" data-item="event" data-url="{{ route('admin.filter.form.content', 'event') }}" data-posturl="{{ route('admin.filter.form.post', 'event') }}">
		<i class="fa fa-filter"></i>
		@if ($page['current_filter']->param_count)
			<span class="num-notify">{{ $page['current_filter']->param_count }}</span>
		@else
			<span class="num-notify none"></span>
		@endif
	</button>

	@if (permit('event.create') && permit('import.event'))
		<button type="button" class="btn btn-regular only-icon import-btn"  data-item="event" data-url="{{ route('admin.import.csv') }}" data-toggle="tooltip" data-placement="bottom" title="Import Events"><i class="mdi mdi-file-excel pe-va"></i></button>
	@endif
@endsection
