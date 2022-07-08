<div class="full progress-tab">
	<ul>
		<li class="{{ $page['install_step'] == 'system' ? 'active' : null }}"><i class="fa fa-server"></i> Pre-Installation</li>
		<li class="{{ $page['install_step'] == 'config' ? 'active' : null }}"><i class="fa fa-cog"></i> Configuration</li>
		<li class="{{ $page['install_step'] == 'database' ? 'active' : null }}"><i class="mdi mdi-database"></i> Database</li>
		<li class="{{ $page['install_step'] == 'complete' ? 'active' : null }} last"><i class="fa fa-check-circle"></i> Complete</li>
	</ul>
</div>
