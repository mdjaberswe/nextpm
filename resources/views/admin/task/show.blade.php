@extends('layouts.master')

@section('content')

	<div class="row page-content">
		<div class="full content-header">
		    <div class="col-xs-12 col-sm-7 col-md-7 col-lg-8">
		    	<h4 class="breadcrumb-title">{!! $page['item_title'] !!}</h4>
		    </div>

		    <div class="col-xs-12 col-sm-5 col-md-5 col-lg-4 xs-left-sm-right">
		    	<div class="show-add-actions dropdown clean inline-block">
                    @permission('attachment.create')
    		    		<a class="btn md btn-regular first dropdown-toggle" animation="fadeIn|fadeOut" data-toggle="dropdown" aria-expanded="false">
    		    			<i class="mdi mdi-plus-circle-multiple-outline"></i> Add...
    		    		</a>

    		    		<ul class="dropdown-menu up-caret">
    		    			<li><a class="add-multiple" data-item="file" data-action="{{ route('admin.file.store') }}" data-content="partials.modals.upload-file" data-default="linked_type:task|linked_id:{{ $task->id }}" save-new="false" data-modalsize="medium" modal-title="Add Files"><i class="lg mdi mdi-file-plus"></i> Add File</a></li>
    		    			<li><a class="add-multiple" data-item="link" data-action="{{ route('admin.link.store') }}" data-content="partials.modals.add-link" data-default="linked_type:task|linked_id:{{ $task->id }}" save-new="false" data-modalsize="" modal-title="Add Link"><i class="fa fa-link"></i> Add Link</a></li>
    		    		</ul>
                    @endpermission
		    	</div>

                <div class="show-misc-actions dropdown clean inline-block">
                    {!! $task->show_misc_actions !!}
    		    </div>

		    	<div class="inline-block prev-next">
		    		<a @if ($task->prev_record) href="{{ route('admin.task.show', $task->prev_record->id) }}" @endif class="inline-block prev @if (is_null($task->prev_record)) disabled @endif" data-toggle="tooltip" data-placement="bottom" title="{!! fill_up_space('Previous Record') !!}"><i class="pe pe-7s-angle-left pe-va"></i></a>
		    		<a @if ($task->next_record) href="{{ route('admin.task.show', $task->next_record->id) }}" @endif class="inline-block next @if (is_null($task->next_record)) disabled @endif" data-toggle="tooltip" data-placement="bottom" title="{!! fill_up_space('Next Record') !!}"><i class="pe pe-7s-angle-right pe-va"></i></a>
		    	</div>
		    </div>
		</div> <!-- end full -->

		@include('partials.tabs.tab-index')

	</div> <!-- end row -->

@endsection

@section('modals')
	@include('partials.modals.access')
@endsection

@push('scripts')
	{{ HTML::script('js/tabs.js') }}
@endpush
