<div class="modal-body vertical perfectscroll">
	<div class="full form-group-container">
		<div class="col-xs-12">
            @if (isset($form) && $form == 'create')
    		    <div class="form-group mb10-imp">
    		        <label for="members">Member</label>
    		        {{ Form::select('members[]', $admin_users_list, null, ['class' => 'form-control white-select-type-multiple', 'multiple' => 'multiple', 'data-placeholder' => 'Add member']) }}
    		        <span field="members" class="validation-error"></span>
    		    </div> <!-- end form-group -->
            @endif

        	<div class="form-group mb10-imp" data-set="permission">
        	    <label for="permissions">Member Permissions
                    <i class="hints-info fa fa-info-circle" data-toggle="tooltip" data-placement="bottom" data-html="true" title="Administrator, Project Owner, and <br> Creator have all permissions"></i>
                </label>

                <div class="inline-input space">
                	@foreach ($project_permissions as $module => $permissions)
                		<div class="toggle-permission">
                			<div class="parent-permission @if (in_array($module, $fixed_modules) || (isset($disabled) && $disabled)) reset-false @endif">
                				<span>
                                    {{ ucfirst($module) }}

                                    @if (in_array($module, ['member', 'note', 'attachment', 'gantt', 'report', 'history']))
                                        <i class="hints-info fa fa-info-circle" data-toggle="tooltip" data-placement="right" data-html="true" title="{{ ucfirst($module) }} view permission auto enabled if the project is public"></i>
                                    @endif
                                </span>

                				<label class="switch @if (in_array($module, $fixed_modules) || (isset($disabled) && $disabled)) disabled blur @endif">
                					<input type="checkbox" name="{{ $module }}" value="1" data-parent="true" @if (in_array($module, $fixed_modules) || (isset($disabled) && $disabled)) checked disabled @endif>
                					<span class="slider round"></span>
                				</label>
                			</div>

                            @if (is_array($permissions) && count($permissions))
                    			<div class="child-permission @if (in_array($module, $fixed_modules) || (isset($disabled) && $disabled)) reset-false @endif">
                    				@foreach ($permissions as $permission => $display_permission)
                    					<div class="inline-info @if (in_array($permission, $fixed_permissions) || (isset($disabled) && $disabled)) force-disabled @endif">
            		        				<p class="pretty mt3 info smooth">
                                                <input type="checkbox" name="{{ $permission }}" value="1" @if (in_array($permission, $fixed_permissions) || (isset($disabled) && $disabled)) checked disabled @endif @if (strpos($permission, 'view') !== false) data-default="true" data-primary="true" @endif>

                                                <label><i class="mdi mdi-check"></i></label> {{ $display_permission }}
            		        				</p>
                    					</div>
                    				@endforeach
                    			</div>
                            @endif
                		</div>
                	@endforeach
                </div>

                <div class="full">
                	<span field="project_permission" class="validation-error block"></span>
                	<span field="task_permission" class="validation-error block"></span>
                	<span field="issue_permission" class="validation-error block"></span>
                	<span field="milestone_permission" class="validation-error block"></span>
                </div>
        	</div> <!-- end form-group -->

            {{ Form::hidden('project_id', null) }}
		</div>
	</div> <!-- end form-group-container -->
</div> <!-- end modal-body -->

@if (isset($form) && $form == 'edit')
    {{ Form::hidden('id', null) }}
@endif
