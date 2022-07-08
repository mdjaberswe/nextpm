<div class="col-xs-12 col-sm-3 col-md-3 col-lg-2 panel-nav-container">
	<h4 class="panel-nav-title"><i class="fa fa-list-ul"></i> Selection Lists</h4>

	<ul class="panel-nav">
        @permission('custom_dropdowns.project_status.view')
            <li class="{!! active_menu('administration-dropdown-projectstatus.') !!}"><a href="{!! route('admin.administration-dropdown-projectstatus.index') !!}">{!! fill_up_space('Project Status') !!}</a></li>
	    @endpermission

        @permission('custom_dropdowns.task_status.view')
            <li class="{!! active_menu('administration-dropdown-taskstatus.') !!}"><a href="{!! route('admin.administration-dropdown-taskstatus.index') !!}">{!! fill_up_space('Task Status') !!}</a></li>
		@endpermission

        @permission('custom_dropdowns.issue_status.view')
            <li class="{!! active_menu('administration-dropdown-issuestatus.') !!}"><a href="{!! route('admin.administration-dropdown-issuestatus.index') !!}">{!! fill_up_space('Issue Status') !!}</a></li>
		@endpermission

        @permission('custom_dropdowns.issue_type.view')
            <li class="{!! active_menu('administration-dropdown-issuetype.') !!}"><a href="{!! route('admin.administration-dropdown-issuetype.index') !!}">{!! fill_up_space('Issue Type') !!}</a></li>
	    @endpermission
    </ul>
</div>
