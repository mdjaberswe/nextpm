@extends('templates.listing')

@section('panelbtn')
    <button type="button" class="btn btn-regular only-icon common-filter-btn" data-toggle="tooltip"
        data-placement="bottom" title="Filter" data-item="role" modal-title="Filter Role Data"
        data-url="{{ route('admin.filter.form.content', 'role') }}"
        data-posturl="{{ route('admin.filter.form.post', 'role') }}">
        <i class="fa fa-filter"></i>
        @if ($page['current_filter']->param_count)
            <span class="num-notify">{{ $page['current_filter']->param_count }}</span>
        @else
            <span class="num-notify none"></span>
        @endif
    </button>

	@permission('role.create')
		<a href="{{ route('admin.role.create') }}" class="btn btn-info">
			<i class="fa fa-plus-circle"></i> Add New Role
		</a>
	@endpermission
@endsection

@section('listingextend')
	@include('admin.role.partials.modal-role-users')
@endsection
