<div class="modal-body min-h150 perfectscroll">
    <div class="form-group always-show">
        <div class="col-xs-9">
            {{ Form::select('filter_fields[]', $filter_fields_list, null, ['class' => 'form-control white-select-type-multiple', 'multiple' => 'multiple', 'data-placeholder' => 'Add fields to filter']) }}
            <span field="filter_fields" class="validation-error"></span>
        </div>

        <div class="inline-block btn-container">
            <button type="button" class="add-filter-field btn thin-both btn-warning">Add</button>
        </div>
    </div> <!-- end form-group -->

    <div class="form-group">
        <div class="col-xs-12 table-responsive min-h150">
            <table class="table table-hover less-border space">
                <thead>
                    <tr>
                        <th>FIELD</th>
                        <th class="w200">CONDITION</th>
                        <th class="w275">VALUE</th>
                        <th class="w30"></th>
                    </tr>
                </thead>

                <tbody>
                    <tr class="{{ ! array_key_exists('access', $current_filter->param_array) ? 'none' : '' }}" data-field="access">
                        <td>Access</td>
                        <td data-type="condition">
                            <select name="access_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['dropdown'] !!}
                            </select>
                            <span field="access_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            {{ Form::select('access[]', $dropdown['access'], null, ['class' => 'form-control white-select-type-multiple', 'multiple' => 'multiple', 'data-placeholder' => 'Please select values']) }}
                            <span field="access" class="validation-error"></span>
                        </td>
                        <td class="center">
                            <button type="button" class="close remove-filter" data-toggle="tooltip" data-placement="top" title="Remove"><span aria-hidden="true">&times;</span></button>
                        </td>
                    </tr>

                    <tr class="{{ ! array_key_exists('description', $current_filter->param_array) ? 'none' : '' }}" data-field="description">
                        <td>Description</td>
                        <td data-type="condition">
                            <select name="description_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['string'] !!}
                            </select>
                            <span field="description_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            {{ Form::select('description[]', [], null, ['data-placeholder' => 'Enter a value', 'multiple' => 'multiple', 'class' => 'form-control white-select-type-multiple-tags']) }}
                            <span field="description" class="validation-error"></span>
                        </td>
                        <td class="center">
                            <button type="button" class="close remove-filter" data-toggle="tooltip" data-placement="top" title="Remove"><span aria-hidden="true">&times;</span></button>
                        </td>
                    </tr>

                    <tr class="{{ ! array_key_exists('end_date', $current_filter->param_array) ? 'none' : '' }}" data-field="end_date">
                        <td>End Date</td>
                        <td data-type="condition">
                            <select name="end_date_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['date'] !!}
                            </select>
                            <span field="end_date_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            {{ Form::select('end_date', $dropdown['days'], null, ['class' => 'form-control white-select-type-single-b', 'data-placeholder' => 'Please select a value']) }}
                            <span field="end_date" class="validation-error"></span>
                        </td>
                        <td class="center">
                            <button type="button" class="close remove-filter" data-toggle="tooltip" data-placement="top" title="Remove"><span aria-hidden="true">&times;</span></button>
                        </td>
                    </tr>

                    <tr class="{{ ! array_key_exists('name', $current_filter->param_array) ? 'none' : '' }}" data-field="name">
                        <td>Event Name</td>
                        <td data-type="condition">
                            <select name="name_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['string'] !!}
                            </select>
                            <span field="name_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            {{ Form::select('name[]', [], null, ['data-placeholder' => 'Enter a value', 'multiple' => 'multiple', 'class' => 'form-control white-select-type-multiple-tags']) }}
                            <span field="name" class="validation-error"></span>
                        </td>
                        <td class="center">
                            <button type="button" class="close remove-filter" data-toggle="tooltip" data-placement="top" title="Remove"><span aria-hidden="true">&times;</span></button>
                        </td>
                    </tr>

                    <tr class="{{ ! array_key_exists('event_owner', $current_filter->param_array) ? 'none' : '' }}" data-field="event_owner">
                        <td>Event Owner</td>
                        <td data-type="condition">
                            <select name="event_owner_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['dropdown'] !!}
                            </select>
                            <span field="event_owner_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            {{ Form::select('event_owner[]', $dropdown['event_owner'], $current_filter->getParamVal('event_owner'), ['class' => 'form-control white-select-type-multiple', 'multiple' => 'multiple', 'data-placeholder' => 'Please select values']) }}
                            <span field="event_owner" class="validation-error"></span>
                        </td>
                        <td class="center">
                            <button type="button" class="close remove-filter" data-toggle="tooltip" data-placement="top" title="Remove"><span aria-hidden="true">&times;</span></button>
                        </td>
                    </tr>

                    <tr class="{{ ! array_key_exists('location', $current_filter->param_array) ? 'none' : '' }}" data-field="location">
                        <td>Location</td>
                        <td data-type="condition">
                            <select name="location_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['string'] !!}
                            </select>
                            <span field="location_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            {{ Form::select('location[]', [], null, ['data-placeholder' => 'Enter a value', 'multiple' => 'multiple', 'class' => 'form-control white-select-type-multiple-tags']) }}
                            <span field="location" class="validation-error"></span>
                        </td>
                        <td class="center">
                            <button type="button" class="close remove-filter" data-toggle="tooltip" data-placement="top" title="Remove"><span aria-hidden="true">&times;</span></button>
                        </td>
                    </tr>

                    <tr class="{{ ! array_key_exists('priority', $current_filter->param_array) ? 'none' : '' }}" data-field="priority">
                        <td>Priority</td>
                        <td data-type="condition">
                            <select name="priority_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['dropdown'] !!}
                            </select>
                            <span field="priority_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            {{ Form::select('priority[]', $dropdown['priority'], null, ['class' => 'form-control white-select-type-multiple', 'multiple' => 'multiple', 'data-placeholder' => 'Please select values']) }}
                            <span field="priority" class="validation-error"></span>
                        </td>
                        <td class="center">
                            <button type="button" class="close remove-filter" data-toggle="tooltip" data-placement="top" title="Remove"><span aria-hidden="true">&times;</span></button>
                        </td>
                    </tr>

                    <tr class="{{ ! array_key_exists('linked_type', $current_filter->param_array) ? 'none' : '' }}" data-field="linked_type">
                        <td>Related</td>
                        <td data-type="condition">
                            <select name="linked_type_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['dropdown'] !!}
                            </select>
                            <span field="linked_type_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            <div class="full related-field">
                                <div class="parent-field">
                                    {{ Form::select('linked_type', $dropdown['related_type'], null, ['class' => 'form-control white-select-type-single-b']) }}
                                </div>

                                <div class="child-field">
                                    {{ Form::hidden('linked_id', null, ['data-child' => 'true']) }}

                                    <div class="full" data-field="none" data-default="true">
                                        {{ Form::text('linked', null, ['class' => 'form-control', 'disabled' => true]) }}
                                    </div>

                                    <div class="full none" data-field="project">
                                        {{ Form::select('project_id', $dropdown['related_to']['project'], null, ['class' => 'form-control white-select-type-single']) }}
                                    </div>
                                </div>
                            </div>
                            <span field="linked_type" class="validation-error"></span>
                            <span field="linked_id" class="validation-error"></span>
                        </td>
                        <td class="center">
                            <button type="button" class="close remove-filter" data-toggle="tooltip" data-placement="top" title="Remove"><span aria-hidden="true">&times;</span></button>
                        </td>
                    </tr>

                    <tr class="{{ ! array_key_exists('start_date', $current_filter->param_array) ? 'none' : '' }}" data-field="start_date">
                        <td>Start Date</td>
                        <td data-type="condition">
                            <select name="start_date_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['date'] !!}
                            </select>
                            <span field="start_date_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            {{ Form::select('start_date', $dropdown['days'], null, ['class' => 'form-control white-select-type-single-b', 'data-placeholder' => 'Please select a value']) }}
                            <span field="start_date" class="validation-error"></span>
                        </td>
                        <td class="center">
                            <button type="button" class="close remove-filter" data-toggle="tooltip" data-placement="top" title="Remove"><span aria-hidden="true">&times;</span></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div> <!-- end modal-body -->
