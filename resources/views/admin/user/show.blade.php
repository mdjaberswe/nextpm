@extends('layouts.master')

@section('content')

	<div class="row page-content">
		<div class="full content-header">
		    <div class="col-xs-12 col-sm-7 col-md-7 col-lg-8">
		    	<h4 class="breadcrumb-title">{!! $page['item_title'] !!}</h4>
		    </div>

		    <div class="col-xs-12 col-sm-5 col-md-5 col-lg-4 xs-left-sm-right">
		    	<div class="dropdown clean inline-block">
		    		<a class="btn md btn-regular first dropdown-toggle" animation="fadeIn|fadeOut" data-toggle="dropdown" aria-expanded="false">
		    			<i class="mdi mdi-plus-circle-multiple-outline"></i> Add...
		    		</a>

		    		<ul class="dropdown-menu up-caret">
		    			<li><a class="add-multiple" data-item="project" data-action="{{ route('admin.project.store') }}" data-content="project.partials.form" data-default="project_owner:{{ $staff->id }}" save-new="false" data-modalsize="medium"><i class="x-lg mdi mdi-file-document-box-check"></i> Add Project</a></li>
		    			<li><a class="add-multiple" data-item="task" data-action="{{ route('admin.task.store') }}" data-content="task.partials.form" data-default="task_owner:{{ $staff->id }}" save-new="false"><i class="fa fa-check-square"></i> Add Task</a></li>
		    			<li><a class="add-multiple" data-item="milestone" data-action="{{ route('admin.milestone.store') }}" data-content="milestone.partials.form" data-default="milestone_owner:{{ $staff->id }}" save-new="false" data-modalsize="medium"><i class="fa fa-map-signs"></i> Add Milestone</a></li>
		    			<li><a class="add-multiple" data-item="issue" data-action="{{ route('admin.issue.store') }}" data-content="issue.partials.form" data-default="issue_owner:{{ $staff->id }}" save-new="false"><i class="fa fa-bug"></i> Add Issue</a></li>
		    			<li><a class="add-multiple" data-item="event" data-action="{{ route('admin.event.store') }}" data-content="event.partials.form" data-default="event_owner:{{ $staff->id }}" save-new="false"><i class="fa fa-calendar"></i> Add Event</a></li>

                        @permission('attachment.create')
                            <li><a class="add-multiple" data-item="file" data-action="{{ route('admin.file.store') }}" data-content="partials.modals.upload-file" data-default="linked_type:staff|linked_id:{{ $staff->id }}" save-new="false" data-modalsize="medium" modal-title="Add Files"><i class="lg mdi mdi-file-plus"></i> Add File</a></li>
                            <li><a class="add-multiple" data-item="link" data-action="{{ route('admin.link.store') }}" data-content="partials.modals.add-link" data-default="linked_type:staff|linked_id:{{ $staff->id }}" save-new="false" data-modalsize="" modal-title="Add Link"><i class="fa fa-link"></i> Add Link</a></li>
		    		    @endpermission
                    </ul>
		    	</div>

		    	<div class="dropdown clean inline-block">
		    		<a class="btn thiner btn-regular dropdown-toggle" animation="fadeIn|fadeOut" data-toggle="dropdown" aria-expanded="false">
		    			<i class="mdi mdi-dots-vertical fa-md pe-va"></i>
		    		</a>

		    		<ul class="dropdown-menu up-caret">
                        @if (auth_staff()->id != $staff->id)
                            <li><a class="add-multiple" data-action="{{ route('admin.user.message') }}" data-content="user.partials.modal-message" data-default="{{ 'receiver[]:' . $staff->id }}" modal-title="New Message" data-modalsize="medium" save-new="false" save-txt="Send"><i class="mdi mdi-message"></i> Send Message</a></li>
                        @endif

		    			@if ($staff->auth_can_edit_password)
		    	            <li><a class="add-multiple" data-item="user" data-action="{{ route('admin.user.password', $staff->id) }}" data-content="user.partials.modal-password" data-default="{{ 'id:' . $staff->id }}" modal-title="Change Password" modal-sub-title="{{ $staff->name }}" data-modalsize="tiny" save-new="false"><i class="mdi mdi-lock-open-outline"></i> Change Password</a></li>
                        @endif

		    			@if ($staff->auth_can_delete)
			    			<li>
								{{ Form::open(['route' => ['admin.user.destroy', $staff->id], 'method' => 'delete']) }}
									{{ Form::hidden('id', $staff->id) }}
									{{ Form::hidden('redirect', true) }}
									<button type="submit" class="delete" data-item="user"><i class="mdi mdi-delete"></i> Delete</button>
					  			{{ Form::close() }}
			    			</li>
		    			@endif
		    		</ul>
		    	</div>

                @permission('user.view')
    		    	<div class="inline-block prev-next">
    		    		<a @if ($staff->prev_record) href="{{ route('admin.user.show', $staff->prev_record->id) }}" @endif class="inline-block prev @if (is_null($staff->prev_record)) disabled @endif" data-toggle="tooltip" data-placement="bottom" title="{{ fill_up_space('Previous Record') }}"><i class="pe pe-7s-angle-left pe-va"></i></a>
    		    		<a @if ($staff->next_record) href="{{ route('admin.user.show', $staff->next_record->id) }}" @endif class="inline-block next @if (is_null($staff->next_record)) disabled @endif" data-toggle="tooltip" data-placement="bottom" title="{{ fill_up_space('Next Record') }}"><i class="pe pe-7s-angle-right pe-va"></i></a>
    		    	</div>
                @endpermission
		    </div>
		</div> <!-- end full -->

		@include('partials.tabs.tab-index')

	</div> <!-- end row -->

@endsection

@push('scripts')
	@include('admin.user.partials.script')
	{{ HTML::script('js/tabs.js') }}
@endpush
