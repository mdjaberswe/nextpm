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

                    <tr class="{{ ! array_key_exists('display_name', $current_filter->param_array) ? 'none' : '' }}" data-field="display_name">
                        <td>Role Name</td>
                        <td data-type="condition">
                            <select name="display_name_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['string'] !!}
                            </select>
                            <span field="display_name_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            {{ Form::select('display_name[]', [], null, ['data-placeholder' => 'Enter a value', 'multiple' => 'multiple', 'class' => 'form-control white-select-type-multiple-tags']) }}
                            <span field="name" class="validation-error"></span>
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
