<div class="modal-body perfectscroll">
    <div class="form-group">
        <label for="timeperiod" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Reporting Period</label>

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
        <label for="owner" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Owner</label>

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <select name="owner_condition" class="form-control multiple-child white-select-type-single-b" data-child="activity-owner">
                <option value="all">show all</option>
                <option value="equal" for="owner">is equal to</option>
                <option value="not_equal" for="owner">is not equal to</option>
                <option value="empty">is empty</option>
                <option value="not_empty">is not empty</option>
            </select>
            <span field="owner_condition" class="validation-error"></span>
        </div>
    </div> <!-- end form-group -->

    <div class="form-group activity-owner none" data-for="owner">
        <div class="col-xs-12 col-sm-offset-3 col-sm-9 col-md-offset-3 col-md-9 col-lg-offset-3 col-lg-9">
            {{ Form::select('owner[]', $data['admin_users_list'], null, ['class' => 'form-control white-select-type-multiple', 'multiple' => 'multiple', 'data-placeholder' => 'Please select activity owner']) }}
            <span field="owner" class="validation-error"></span>
        </div>
    </div>

    <div class="form-group">
        <label for="widget_prefix" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Widget Prefix</label>

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            {{ Form::text('widget_prefix', 'My', ['class' => 'form-control']) }}
            <span field="widget_prefix" class="validation-error"></span>
        </div>
    </div> <!-- end form-group -->

    <div class="form-group">
        <label for="auto_refresh" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Auto Refresh</label>

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            {{ Form::select('auto_refresh', $data['auto_refresh_list'], 15, ['class' => 'form-control white-select-type-single-b']) }}
            <span field="auto_refresh" class="validation-error"></span>
        </div>
    </div> <!-- end form-group -->
</div> <!-- end modal-body -->
