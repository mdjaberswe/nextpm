@extends('layouts.master')

@section('content')

	<div class="row page-content">
		<div class="full content-header">
		    <div class="col-xs-12 col-sm-7 col-md-7 col-lg-8">
		    	<h4 class="breadcrumb-title">{!! $page['item_title'] !!}</h4>
		    </div>

		    <div class="col-xs-12 col-sm-5 col-md-5 col-lg-4 xs-left-sm-right">
                @if ($project->authCanDo('task_create') || $project->authCanDo('issue_create') || $project->authCanDo('event_create') || $project->authCanDo('milestone_create') || $project->authCanDo('attachment_create'))
    		    	<div class="dropdown clean inline-block">
    		    		<a class="btn md btn-regular first dropdown-toggle" animation="fadeIn|fadeOut" data-toggle="dropdown" aria-expanded="false">
    		    			<i class="mdi mdi-plus-circle-multiple-outline"></i> Add...
    		    		</a>

    		    		<ul class="dropdown-menu up-caret">
                            @if ($project->authCanDo('task_create'))
    		    			   <li><a class="add-multiple" data-item="task" data-action="{{ route('admin.task.store') }}" data-content="task.partials.form" data-default="related_type:project|related_id:{{ $project->id }}" save-new="false"><i class="fa fa-check-square"></i> Add Task</a></li>
    		    			@endif

                            @if ($project->authCanDo('issue_create'))
                                <li><a class="add-multiple" data-item="issue" data-action="{{ route('admin.issue.store') }}" data-content="issue.partials.form" data-default="related_type:project|related_id:{{ $project->id }}" save-new="false"><i class="fa fa-bug"></i> Add Issue</a></li>
    		    			@endif

                            @if ($project->authCanDo('milestone_create'))
                                <li><a class="add-multiple" data-item="milestone" data-action="{{ route('admin.milestone.store') }}" data-content="milestone.partials.form" data-default="project_id:{{ $project->id }}" save-new="false" data-modalsize="medium"><i class="fa fa-map-signs"></i> Add Milestone</a></li>
    		    			@endif

                            @if ($project->authCanDo('event_create'))
                                <li><a class="add-multiple" data-item="event" data-action="{{ route('admin.event.store') }}" data-content="event.partials.form" data-default="related_type:project|related_id:{{ $project->id }}" save-new="false"><i class="fa fa-calendar"></i> Add Event</a></li>
                            @endif

                            @if ($project->authCanDo('attachment_create'))
    		    			   <li><a class="add-multiple" data-item="file" data-action="{{ route('admin.file.store') }}" data-content="partials.modals.upload-file" data-default="linked_type:project|linked_id:{{ $project->id }}" save-new="false" data-modalsize="medium" modal-title="Add Files"><i class="lg mdi mdi-file-plus"></i> Add File</a></li>
    		    			   <li><a class="add-multiple" data-item="link" data-action="{{ route('admin.link.store') }}" data-content="partials.modals.add-link" data-default="linked_type:project|linked_id:{{ $project->id }}" save-new="false" data-modalsize="" modal-title="Add Link"><i class="fa fa-link"></i> Add Link</a></li>
    		    		    @endif
                        </ul>
    		    	</div>
                @endif

                <div class="show-misc-actions dropdown clean inline-block">
                    {!! $project->show_misc_actions !!}
                </div>

		    	<div class="inline-block prev-next">
		    		<a @if ($project->prev_record) href="{{ route('admin.project.show', $project->prev_record->id) }}" @endif class="inline-block prev @if (is_null($project->prev_record)) disabled @endif" data-toggle="tooltip" data-placement="bottom" title="{!! fill_up_space('Previous Record') !!}"><i class="pe pe-7s-angle-left pe-va"></i></a>
		    		<a @if ($project->next_record) href="{{ route('admin.project.show', $project->next_record->id) }}" @endif class="inline-block next @if (is_null($project->next_record)) disabled @endif" data-toggle="tooltip" data-placement="bottom" title="{!! fill_up_space('Next Record') !!}"><i class="pe pe-7s-angle-right pe-va"></i></a>
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
	@include('admin.project.partials.script')
@endpush
