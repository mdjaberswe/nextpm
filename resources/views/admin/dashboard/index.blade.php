@extends('layouts.default')

@section('content')

    <div class="row page-content">
        <div class="full content-header">
            <div class="col-xs-8 col-sm-5 col-md-5 col-lg-6">
                <h4 class="breadcrumb-title">{!! $page['breadcrumb'] !!}</h4>
            </div>

            <div class="col-xs-4 col-sm-7 col-md-7 col-lg-6 align-r">
                <button type="button" class="btn btn-regular only-icon common-filter-btn" data-toggle="tooltip" data-placement="bottom" title="Filter" data-item="dashboard" data-url="{{ route('admin.filter.form.content', 'dashboard') }}" data-posturl="{{ route('admin.filter.form.post', 'dashboard') }}" data-modalsize="tiny">
                    <i class="fa fa-filter"></i>
                    @if ($page['current_filter']->param_count)
                        <span class="num-notify">{{ $page['current_filter']->param_count }}</span>
                    @else
                        <span class="num-notify none"></span>
                    @endif
                </button>
            </div>

            <span class="bottom-label">
                Reporting period: <span class="display" data-realtime="timeperiod">{{ $page['current_filter']->optional_param['timeperiod_display'] }}</span>
            </span>
        </div>

        <div class="full board-floor auto-refresh" data-interval="{{ $page['interval'] or 'false' }}" data-refresh="{{ $page['auto_refresh'] or 'false' }}" data-url="{{ route('admin.dashboard.post.index') }}">
            @include('admin.dashboard.content')
        </div> <!-- end board-floor -->
    </div>

@endsection
