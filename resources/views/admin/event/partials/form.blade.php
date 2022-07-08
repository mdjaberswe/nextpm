<div class="modal-body vertical perfectscroll">
	<div class="full form-group-container">
        <div class="full form-group-row">
            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                <div class="form-group">
                    <label for="name">Event Name <span class="color-danger">*</span></label>
                    {{ Form::text('name', null, ['class' => 'form-control']) }}
                    <span field="name" class="validation-error"></span>
                </div> <!-- end form-group -->
            </div>

            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                <div class="form-group">
                    <label for="event_owner">Event Owner</label>
                    {{ Form::select('event_owner', $admin_users_list, auth_staff()->id, ['class' => 'form-control white-select-type-single', 'data-append' => 'staff', 'data-enabled' => 'true', 'data-keepval' => 'true', 'default-none' => 'false', 'data-container' => '.form-group']) }}
                    {{ Form::select('owner_id', $admin_users_list, null, ['class' => 'none', 'data-default' => 'true', 'data-container' => '.form-group']) }}
                    <span field="event_owner" class="validation-error"></span>
                </div> <!-- end form-group -->
            </div>
        </div> <!-- end form-group-row -->

        <div class="full form-group-row">
            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                <div class="form-group">
                    <label for="location">Location</label>
                    {{ Form::text('location', null, ['class' => 'form-control']) }}
                    <span field="location" class="validation-error"></span>
                </div> <!-- end form-group -->
            </div>

            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                <div class="form-group">
                    <label for="attendees">Attendees</label>
                    {{ Form::select('attendees[]', $attendees_list, null, ['class' => 'form-control white-select-type-multiple', 'multiple' => 'multiple']) }}
                    <span field="attendees" class="validation-error"></span>
                </div> <!-- end form-group -->
            </div>
        </div> <!-- end form-group-row -->

        <div class="full form-group-row">
            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <div class="full left-icon">
                        <i class="fa fa-calendar-check-o"></i>
                        {{ Form::text('start_date', isset($start_date) ? $start_date : null, ['class' => 'form-control datetimepicker', 'placeholder' => 'Start Date']) }}
                        <span field="start_date" class="validation-error"></span>
                    </div>
                </div> <!-- end form-group -->
            </div>

            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <div class="full left-icon">
                        <i class="fa fa-calendar-times-o"></i>
                        {{ Form::text('end_date', isset($end_date) ? $end_date : null, ['class' => 'form-control datetimepicker', 'placeholder' => 'End Date']) }}
                        <span field="end_date" class="validation-error"></span>
                    </div>
                </div> <!-- end form-group -->
            </div>
        </div> <!-- end form-group-row -->

        <div class="full form-group-row">
            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                <div class="form-group">
                    <label for="priority">Priority</label>
                    {{ Form::select('priority', $priority_list, null, ['class' => 'form-control white-select-type-single-b']) }}
                    <span field="priority" class="validation-error"></span>
                </div> <!-- end form-group -->
            </div>

            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                <div class="form-group show-if multiple">
                    <label for="related">Related To</label>

                    <div class="full">
                        <div class="full related-field">
                            <div class="parent-field">
                                {{ Form::select('related_type', $related_type_list, null, ['class' => 'form-control white-select-type-single-b']) }}
                            </div>

                            <div class="child-field">
                                {{ Form::hidden('related_id', null, ['data-child' => 'true']) }}

                                <div class="full" data-field="none" data-default="true">
                                    {{ Form::text('related', null, ['class' => 'form-control', 'disabled' => true]) }}
                                </div>

                                <div class="full none" data-field="project">
                                    {{ Form::select('project_id', $related_to_list['project'], null, ['class' => 'form-control white-select-type-single', 'data-append-request' => 'true', 'data-parent' => 'project', 'data-child' => 'staff|staff[]', 'data-container' => 'form']) }}
                                </div>
                            </div>
                        </div>
                        <span field="related_type" class="validation-error"></span>
                        <span field="related_id" class="validation-error"></span>
                    </div>
                </div> <!-- end form-group -->
            </div>
        </div> <!-- end form-group-row -->

        <div class="full form-group-row">
    		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
    			<div class="form-group">
    				<label for="description">Description</label>
    		        {{ Form::textarea('description', null, ['class' => 'form-control sm']) }}
    		        <span field="description" class="validation-error"></span>
    			</div> <!-- end form-group -->

    			<div class="form-group long-select2-multiple">
    				<div class="full show-if" @if (isset($form) && $form == 'create') scroll="true" flush="true" @endif>
    					<p class="pretty mt3 info smooth">
    					    <input type="radio" name="access" value="private" class="indicator">
    					    <label><i class="mdi mdi-check"></i></label> Private
    					</p>

    					<p class="pretty mt3 info smooth">
    					    <input type="radio" name="access" value="public" checked>
    					    <label><i class="mdi mdi-check"></i></label> Public Read Only
    					</p>

    					<p class="pretty mt3 info smooth">
    					    <input type="radio" name="access" value="public_rwd">
    					    <label><i class="mdi mdi-check"></i></label> Public Read/Write/Delete
    					</p>
    				</div>

    				@if (isset($form) && $form == 'create')
    					<div class="full none">
    						{{ Form::select('staffs[]', $admin_users_list, null, ['class' => 'form-control white-select-type-multiple', 'multiple' => 'multiple', 'data-placeholder' => 'Allow some users only', 'data-append' => 'staff[]', 'data-enabled' => 'true', 'data-keepval' => 'true', 'default-none' => 'false', 'data-container' => '.full']) }}
                            {{ Form::select('staff_ids', $admin_users_list, null, ['class' => 'none', 'data-default' => 'true', 'data-container' => '.full']) }}

    						<p class="para-checkbox-label">Allowed users can</p>

    						<p class="pretty mt3 info smooth">
    						    <input type="checkbox" name="can_read" value="1" checked disabled>
    						    <label><i class="mdi mdi-check"></i></label> Read
    						</p>

    						<p class="pretty mt3 info smooth">
    						    <input type="checkbox" name="can_write" value="1">
    						    <label><i class="mdi mdi-check"></i></label> Write
    						</p>

    						<p class="pretty mt3 info smooth">
    						    <input type="checkbox" name="can_delete" value="1">
    						    <label><i class="mdi mdi-check"></i></label> Delete
    						</p>
    					</div>
    				@endif

    				<span field="access" class="validation-error"></span>
    			</div>
    		</div>
        </div> <!-- end form-group-row -->
	</div> <!-- end form-group-container -->
</div> <!-- end modal-body -->

@if (isset($form) && $form == 'edit')
    {{ Form::hidden('id', null) }}
@endif
