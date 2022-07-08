@extends('layouts.master')

@section('content')

	<div class="row page-content">
		<div class="full content-header">
		    <div class="col-xs-12 col-sm-7 col-md-7 col-lg-8">
		    	<h4 class="breadcrumb-title">{!! $page['item_title'] !!}</h4>
		    </div>

		    <div class="col-xs-12 col-sm-5 col-md-5 col-lg-4 xs-left-sm-right">
                @if (permit('attachment.create') || $milestone->project->authCanDo('task_create') || $milestone->project->authCanDo('issue_create'))
    		    	<div class="dropdown clean inline-block">
    		    		<a class="btn md btn-regular first dropdown-toggle" animation="fadeIn|fadeOut" data-toggle="dropdown" aria-expanded="false">
    		    			<i class="mdi mdi-plus-circle-multiple-outline"></i> Add...
    		    		</a>

    		    		<ul class="dropdown-menu up-caret">
                            @if ($milestone->project->authCanDo('task_create'))
    		    			   <li><a class="add-multiple" data-item="task" data-action="{{ route('admin.task.store') }}" data-content="task.partials.form" data-default="{{ 'related_type:project|related_id:' . $milestone->project_id . '|milestone_id:' . $milestone->id . '|milestone_val:' . $milestone->id }}" save-new="false"><i class="fa fa-check-square"></i> Add Task</a></li>
    		    			@endif

                            @if ($milestone->project->authCanDo('issue_create'))
                                <li><a class="add-multiple" data-item="issue" data-action="{{ route('admin.issue.store') }}" data-content="issue.partials.form" data-default="{{ 'related_type:project|related_id:' . $milestone->project_id . '|release_milestone_id:' . $milestone->id . '|affected_milestone_id:' . $milestone->default_affected_id . '|milestone_val:' . $milestone->id . '|affected_milestone_val:' . $milestone->default_affected_id }}" save-new="false"><i class="fa fa-bug"></i> Add Issue</a></li>
                            @endif

                            @permission('attachment.create')
                                <li><a class="add-multiple" data-item="file" data-action="{{ route('admin.file.store') }}" data-content="partials.modals.upload-file" data-default="linked_type:milestone|linked_id:{{ $milestone->id }}" save-new="false" data-modalsize="medium" modal-title="Add Files"><i class="lg mdi mdi-file-plus"></i> Add File</a></li>
                                <li><a class="add-multiple" data-item="link" data-action="{{ route('admin.link.store') }}" data-content="partials.modals.add-link" data-default="linked_type:milestone|linked_id:{{ $milestone->id }}" save-new="false" data-modalsize="" modal-title="Add Link"><i class="fa fa-link"></i> Add Link</a></li>
    		    		    @endpermission
                        </ul>
    		    	</div>
                @endif

                <div class="show-misc-actions dropdown clean inline-block">
                    {!! $milestone->show_misc_actions !!}
                </div>

		    	<div class="inline-block prev-next">
		    		<a @if ($milestone->prev_record) href="{{ route('admin.milestone.show', $milestone->prev_record->id) }}" @endif class="inline-block prev @if (is_null($milestone->prev_record)) disabled @endif" data-toggle="tooltip" data-placement="bottom" title="{{ fill_up_space('Previous Record') }}"><i class="pe pe-7s-angle-left pe-va"></i></a>
		    		<a @if ($milestone->next_record) href="{{ route('admin.milestone.show', $milestone->next_record->id) }}" @endif class="inline-block next @if (is_null($milestone->next_record)) disabled @endif" data-toggle="tooltip" data-placement="bottom" title="{{ fill_up_space('Next Record') }}"><i class="pe pe-7s-angle-right pe-va"></i></a>
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
