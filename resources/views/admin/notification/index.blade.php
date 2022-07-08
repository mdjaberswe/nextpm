@extends('templates.listing')

@section('panelbtn')
	<button type="button" class="btn btn-regular only-icon common-filter-btn" data-toggle="tooltip"
        data-placement="bottom" title="Filter" data-item="notification"  data-modalsize="tiny"
        data-url="{{ route('admin.filter.form.content', 'notification') }}"
        data-posturl="{{ route('admin.filter.form.post', 'notification') }}">
		<i class="fa fa-filter"></i>
		@if ($page['current_filter']->param_count)
			<span class="num-notify">{{ $page['current_filter']->param_count }}</span>
		@else
			<span class="num-notify none"></span>
		@endif
	</button>
@endsection
