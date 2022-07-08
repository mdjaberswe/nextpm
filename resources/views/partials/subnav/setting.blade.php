<div class="col-xs-12 col-sm-3 col-md-3 col-lg-2 panel-nav-container">
	<h4 class="panel-nav-title"><i class="fa fa-cogs"></i> App Settings</h4>

	<ul class="panel-nav">
	    @permission('settings.general')
            <li class="{!! active_menu('administration-setting.general') !!}"><a href="{!! route('admin.administration-setting.general') !!}">General</a></li>
		@endpermission

        @permission('settings.email')
            <li class="{!! active_menu('administration-setting.email') !!}"><a href="{!! route('admin.administration-setting.email') !!}">Email</a></li>
        @endpermission
    </ul>
</div>
