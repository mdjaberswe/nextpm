<div class="modal-body perfectscroll">
    <div class="form-group">
        <label for="timeperiod" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Created Time</label>

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <select name="timeperiod" class="form-control multiple-child white-select-type-single" data-child="timeperiod-date">
                @foreach ($data['timeperiod_list'] as $option_val => $display_text)
                    <option value="{{ $option_val }}" @if ($option_val == 'between') for="between" @endif>{{ $display_text }}</option>
                @endforeach
            </select>
            <span field="timeperiod" class="validation-error"></span>
        </div>
    </div> <!-- end form-group -->

    <div class="form-group timeperiod-date none" data-for="between">
        <label for="between" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Between Dates</label>

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <div class="full">
                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 double-input">
                    <div class="full left-icon" data-toggle="tooltip" data-placement="top" title="Start Date">
                        <i class="fa fa-calendar-check-o"></i>
                        {{ Form::text('start_date', null, ['class' => 'form-control datepicker', 'placeholder' => 'Start Date']) }}
                        <span field="start_date" class="validation-error"></span>
                    </div> <!-- end form-group -->
                </div>

                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 double-input">
                    <div class="full left-icon" data-toggle="tooltip" data-placement="top" title="End Date">
                        <i class="fa fa-calendar-times-o"></i>
                        {{ Form::text('end_date', null, ['class' => 'form-control datepicker', 'placeholder' => 'End Date']) }}
                        <span field="end_date" class="validation-error"></span>
                    </div> <!-- end form-group -->
                </div>
            </div>
        </div>
    </div> <!-- end form-group -->

    <div class="form-group">
        <label for="owner" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Notification From</label>

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <select name="owner_condition" class="form-control multiple-child white-select-type-single-b" data-child="activity-owner">
                <option value="all">show all</option>
                <option value="equal" for="owner">is equal to</option>
                <option value="not_equal" for="owner">is not equal to</option>
            </select>
            <span field="owner_condition" class="validation-error"></span>
        </div>
    </div> <!-- end form-group -->

    <div class="form-group activity-owner none" data-for="owner">
        <div class="col-xs-12 col-sm-offset-3 col-sm-9 col-md-offset-3 col-md-9 col-lg-offset-3 col-lg-9">
            {{ Form::select('owner[]', $data['admin_users_list'], null, ['class' => 'form-control white-select-type-multiple', 'multiple' => 'multiple', 'data-placeholder' => 'Notification Sender']) }}
            <span field="owner" class="validation-error"></span>
        </div>
    </div>

    <div class="form-group">
        <label for="related" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Related To</label>

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <div class="full related-field">
                <div class="parent-field">
                    {{ Form::select('related_condition', $related_type_list, null, ['class' => 'form-control white-select-type-single-b']) }}
                </div>

                <div class="child-field">
                    {{ Form::hidden('related', null, ['data-child' => 'true']) }}

                    <div class="full" data-field="none" data-default="true">
                        {{ Form::text('related_id', null, ['class' => 'form-control', 'disabled' => true]) }}
                    </div>

                    <div class="full none" data-field="project">
                        {{ Form::select('project_id', $related_to_list['project'], null, ['class' => 'form-control white-select-type-single']) }}
                    </div>

                    <div class="full none" data-field="task">
                        {{ Form::select('task_id', $related_to_list['task'], null, ['class' => 'form-control white-select-type-single']) }}
                    </div>

                    <div class="full none" data-field="milestone">
                        {{ Form::select('milestone_id', $related_to_list['milestone'], null, ['class' => 'form-control white-select-type-single']) }}
                    </div>

                    <div class="full none" data-field="issue">
                        {{ Form::select('issue_id', $related_to_list['issue'], null, ['class' => 'form-control white-select-type-single']) }}
                    </div>

                    <div class="full none" data-field="event">
                        {{ Form::select('event_id', $related_to_list['event'], null, ['class' => 'form-control white-select-type-single']) }}
                    </div>
                </div>
            </div>
            <span field="related_condition" class="validation-error"></span>
            <span field="related" class="validation-error"></span>
        </div>
    </div> <!-- end form-group -->
</div> <!-- end modal-body -->
