<nav class="{!! $class['nav'] !!}">
    <ul>
        @permission('dashboard.view')
            <li class="{!! active_menu('admin.dashboard.') !!}"><a href="{!! route('admin.dashboard.index') !!}"><i class="fa mdi mdi-view-dashboard"></i>Dashboards</a></li>
        @endpermission

        @permission('project.view')
            <li class="{!! active_menu('admin.project|admin.milestone') !!}"><a href="{!! route('admin.project.index') !!}"><i class="fa mdi mdi-library-books"></i>Projects</a></li>
        @endpermission

        @permission('task.view')
            <li class="{!! active_menu('admin.task.') !!}"><a href="{!! route('admin.task.index') !!}"><i class="fa fa-tasks"></i>Tasks</a></li>
        @endpermission

        @permission('issue.view')
            <li class="{!! active_menu('admin.issue.') !!}"><a href="{!! route('admin.issue.index') !!}"><i class="fa fa-bug"></i>Issues</a></li>
        @endpermission

        @if (permit('event.view') || permit('task.view') || permit('milestone.view') || permit('issue.view'))
            <li class="{!! active_menu('admin.event.') !!}"><a href="{!! route('admin.event.calendar') !!}"><i class="fa fa-calendar-o"></i>Calendar</a></li>
        @endif

        @if (permit('module.settings') || permit('module.custom_dropdowns') || permit('module.user') || permit('module.role'))
            <li class="heading">SETUP</li>
        @endif

        @if (permit('module.settings') || permit('module.custom_dropdowns'))
            <li class="{!! active_menu('admin.administration') !!}">
                <a class="tree"><i class="fa fa-university"></i>Administration<span class="fa fa-angle-left {!! active_menu_arrow('admin.administration') !!}"></span></a>
                <ul class="collapse" {!! active_tree('admin.administration', $class['nav']) !!}>
                    @permission('module.settings')
                        <li><a href="{!! auth_staff()->getInitSubRoute('settings') !!}" class="{!! active_menu('admin.administration-setting') !!}"><i class="fa fa-cogs"></i>Settings</a></li>
                    @endpermission

                    @permission('module.custom_dropdowns')
                        <li><a href="{!! auth_staff()->getInitSubRoute('custom_dropdowns') !!}" class="{!! active_menu('admin.administration-dropdown') !!}"><i class="fa fa-chevron-circle-down"></i>{!! fill_up_space('Custom Dropdowns') !!}</a></li>
                    @endpermission
                </ul>
            </li>
        @endpermission

        @permission('user.view')
            <li class="{!! active_menu('admin.user.') !!}"><a href="{!! route('admin.user.index') !!}"><i class="fa fa-users"></i>Users</a></li>
        @endpermission

        @permission('role.view')
            <li class="{!! active_menu('admin.role.') !!}"><a href="{!! route('admin.role.index') !!}"><i class="fa fa-universal-access"></i>Roles</a></li>
        @endpermission
    </ul>
</nav> <!-- end nav -->
