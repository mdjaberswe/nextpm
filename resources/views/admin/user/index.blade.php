@extends('templates.listing')

@section('panelbtn')
    <button type="button" class="btn btn-regular only-icon common-filter-btn" data-toggle="tooltip" data-placement="bottom" title="Filter" data-item="staff" modal-title="Filter User Data" data-url="{{ route('admin.filter.form.content', 'staff') }}" data-posturl="{{ route('admin.filter.form.post', 'staff') }}">
        <i class="fa fa-filter"></i>
        @if ($page['current_filter']->param_count)
            <span class="num-notify">{{ $page['current_filter']->param_count }}</span>
        @else
            <span class="num-notify none"></span>
        @endif
    </button>
@endsection
