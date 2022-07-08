<div class="form-group">
    <label for="name" class="col-xs-12 col-sm-3 col-md-2 col-lg-2">Role Name <span class="color-danger">*</span></label>

    <div class="col-xs-12 col-sm-9 col-md-10 col-lg-10">
    	{{ Form::text('name', isset($role) ? $role->display_name : null, ['class' => 'form-control']) }}
    	<span error-field="name" class="validation-error">{{ $errors->first('name', ':message') }}</span>
    </div>
</div> <!-- end form-group -->

<div class="form-group">
	{{ Form::label('description', 'Role Description', ['class' => 'col-xs-12 col-sm-3 col-md-2 col-lg-2']) }}

	<div class="col-xs-12 col-sm-9 col-md-10 col-lg-10">
		{{ Form::textarea('description', null, ['class' => 'form-control']) }}
		<span error-field="description" class="validation-error">{{ $errors->first('description', ':message') }}</span>
	</div>
</div> <!-- end form-group -->

<div class="form-group permission-container">
	{{ Form::label('permissions', 'Permissions', ['class' => 'mb15 col-xs-12 col-sm-3 col-md-2 col-lg-2']) }}

	<div class="col-xs-12 col-sm-9 col-md-10 col-lg-10">
		@foreach ($permissions_groups as $permissions_group)
			<div class="full permission-group">
				<div class="full permission-group-title">
					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 toggle-header">
						<h2 class="left-justify">{{ $permissions_group['display_name'] }} Permissions</h2>

						<label class="right-justify switch all" data-toggle="tooltip" data-placement="top" title="All On/Off">
							<input type="checkbox" @if ($permissions_group['all_checked']) {{ 'checked' }} @endif>
							<span class="slider round"></span>
						</label>
					</div>
				</div> <!-- end permission-group-title -->

				@foreach ($permissions_group['modules'] as $module)
					<div class="full permission-row">
						<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 permission-row-title">
							<span class="left-justify para-cap {{ in_array(strtolower($module->display_name), ['import', 'mass update', 'mass delete', 'change owner']) ? 'tooltip-lg-min' : null }}">
                                {{ ucfirst($module->display_name) }}

                                @if (strtolower($module->display_name) == 'mass update')
                                    <i class="hints fa fa-info-circle" data-toggle="tooltip" data-html="true" title="{!! fill_up_space('Only modules with edit permission<br>are permitted for mass update') !!}"></i>
                                @elseif(strtolower($module->display_name) == 'mass delete')
                                    <i class="hints fa fa-info-circle" data-toggle="tooltip" data-html="true" title="{!! fill_up_space('Only modules with delete permission<br>are permitted for mass delete') !!}"></i>
                                @elseif(strtolower($module->display_name) == 'change owner')
                                    <i class="hints fa fa-info-circle" data-toggle="tooltip" data-html="true" title="{!! fill_up_space('Only modules with edit permission<br>are permitted for change owner') !!}"></i>
                                @elseif(strtolower($module->display_name) == 'import')
                                    <i class="hints fa fa-info-circle" data-toggle="tooltip" data-html="true" title="{!! fill_up_space('Only modules with create permission<br>are permitted for import') !!}"></i>
                                @elseif(strtolower($module->display_name) == 'user')
                                    <i class="hints fa fa-info-circle" data-toggle="tooltip" data-html="true" title="{!! fill_up_space('Only Administrator can <br> create / delete user') !!}"></i>
                                @elseif(strtolower($module->display_name) == 'role')
                                    <i class="hints fa fa-info-circle" data-toggle="tooltip" data-html="true" title="{!! fill_up_space('Only Administrator can <br> create / edit / delete role') !!}"></i>
                                @endif
                            </span>

							<label class="right-justify switch">
								@if ($permissions_group['module_permissions']['has_permission'][$module->name] == true)
									<input type="checkbox" name="permissions[]" value="{{ $module->id }}" checked>
								@else
									<input type="checkbox" name="permissions[]" value="{{ $module->id }}">
								@endif
								<span class="slider round"></span>
							</label>
						</div> <!-- end permission-row-title -->

						@if (count($permissions_group['module_permissions'][$module->name]) > 0)
		    				{!! HtmlElement::renderModulePermissions($permissions_group['module_permissions'][$module->name]) !!}
						@endif
					</div>
				@endforeach
			</div> <!-- end permission-group -->
		@endforeach
	</div>
</div> <!-- end form-group -->

@if (isset($role->id))
	{{ Form::hidden('id', $role->id) }}
@endif

<div class="form-group">
    <div class="col-xs-12 col-sm-offset-3 col-sm-9 col-md-offset-2 col-md-10 col-lg-offset-2 col-lg-10">
        <button type="submit" name="save"  id="save" class="btn btn-info ladda-button" data-style="expand-right">
            <span class="ladda-label" data-status="true">Save</span>
        </button>
        @if (! isset($role))
        	{{ Form::hidden('add_new', 0) }}
            <button type="submit" name="save_and_new" id="save-and-new" class="btn btn-default ladda-button" data-style="expand-right" data-spinner-color="#666">
                <span class="ladda-label" data-status="true">Save and New</span>
            </button>
        @endif
        {!! link_to_route('admin.role.index', 'Cancel', [], ['class' => 'btn btn-default']) !!}
    </div>
</div> <!-- end form-group -->
