@extends('layouts.master')

@section('content')
    <div class="row page-content">
        <div class="full content-header mb20 bottom-border">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <h4 class="breadcrumb-title">{!! $page['item_title'] !!}</h4>
            </div>
        </div>

        <div class="full">
            {{ Form::open(['route' => 'admin.role.store', 'method' => 'post', 'class' => 'page-form']) }}
                @include('admin.role.partials.form')
            {{ Form::close() }}
        </div>
    </div> <!-- end row -->
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('.switch').find('input').prop('checked', false);
            $('.para-checkbox').find('input').prop('checked', false);
        });
    </script>

    @include('admin.role.partials.script')
@endpush
