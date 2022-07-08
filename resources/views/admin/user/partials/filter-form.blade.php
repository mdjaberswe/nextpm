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
                    <tr class="{{ ! array_key_exists('city', $current_filter->param_array) ? 'none' : '' }}" data-field="city">
                        <td>City</td>
                        <td data-type="condition">
                            <select name="city_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['string'] !!}
                            </select>
                            <span field="city_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            {{ Form::select('city[]', [], null, ['data-placeholder' => 'Enter a value', 'multiple' => 'multiple', 'class' => 'form-control white-select-type-multiple-tags']) }}
                            <span field="city" class="validation-error"></span>
                        </td>
                        <td class="center">
                            <button type="button" class="close remove-filter" data-toggle="tooltip" data-placement="top" title="Remove"><span aria-hidden="true">&times;</span></button>
                        </td>
                    </tr>

                    <tr class="{{ ! array_key_exists('country_code', $current_filter->param_array) ? 'none' : '' }}" data-field="country_code">
                        <td>Country</td>
                        <td data-type="condition">
                            <select name="country_code_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['dropdown'] !!}
                            </select>
                            <span field="country_code_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            {{ Form::select('country_code[]', $dropdown['country'], null, ['class' => 'form-control white-select-type-multiple', 'multiple' => 'multiple', 'data-placeholder' => 'Please select values']) }}
                            <span field="country_code" class="validation-error"></span>
                        </td>
                        <td class="center">
                            <button type="button" class="close remove-filter" data-toggle="tooltip" data-placement="top" title="Remove"><span aria-hidden="true">&times;</span></button>
                        </td>
                    </tr>

                    <tr class="{{ ! array_key_exists('email', $current_filter->param_array) ? 'none' : '' }}" data-field="email">
                        <td>Email</td>
                        <td data-type="condition">
                            <select name="email_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['string'] !!}
                            </select>
                            <span field="email_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            {{ Form::select('email[]', [], null, ['data-placeholder' => 'Enter a value', 'multiple' => 'multiple', 'class' => 'form-control white-select-type-multiple-tags']) }}
                            <span field="email" class="validation-error"></span>
                        </td>
                        <td class="center">
                            <button type="button" class="close remove-filter" data-toggle="tooltip" data-placement="top" title="Remove"><span aria-hidden="true">&times;</span></button>
                        </td>
                    </tr>

                    <tr class="{{ ! array_key_exists('fax', $current_filter->param_array) ? 'none' : '' }}" data-field="fax">
                        <td>Fax</td>
                        <td data-type="condition">
                            <select name="fax_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['string'] !!}
                            </select>
                            <span field="fax_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            {{ Form::select('fax[]', [], null, ['data-placeholder' => 'Enter a value', 'multiple' => 'multiple', 'class' => 'form-control white-select-type-multiple-tags']) }}
                            <span field="fax" class="validation-error"></span>
                        </td>
                        <td class="center">
                            <button type="button" class="close remove-filter" data-toggle="tooltip" data-placement="top" title="Remove"><span aria-hidden="true">&times;</span></button>
                        </td>
                    </tr>

                    <tr class="{{ ! array_key_exists('first_name', $current_filter->param_array) ? 'none' : '' }}" data-field="first_name">
                        <td>First Name</td>
                        <td data-type="condition">
                            <select name="first_name_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['string'] !!}
                            </select>
                            <span field="first_name_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            {{ Form::select('first_name[]', [], null, ['data-placeholder' => 'Enter a value', 'multiple' => 'multiple', 'class' => 'form-control white-select-type-multiple-tags']) }}
                            <span field="first_name" class="validation-error"></span>
                        </td>
                        <td class="center">
                            <button type="button" class="close remove-filter" data-toggle="tooltip" data-placement="top" title="Remove"><span aria-hidden="true">&times;</span></button>
                        </td>
                    </tr>

                    <tr class="{{ ! array_key_exists('title', $current_filter->param_array) ? 'none' : '' }}" data-field="title">
                        <td>Job Title</td>
                        <td data-type="condition">
                            <select name="title_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['string'] !!}
                            </select>
                            <span field="title_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            {{ Form::select('title[]', [], null, ['data-placeholder' => 'Enter a value', 'multiple' => 'multiple', 'class' => 'form-control white-select-type-multiple-tags']) }}
                            <span field="title" class="validation-error"></span>
                        </td>
                        <td class="center">
                            <button type="button" class="close remove-filter" data-toggle="tooltip" data-placement="top" title="Remove"><span aria-hidden="true">&times;</span></button>
                        </td>
                    </tr>

                    <tr class="{{ ! array_key_exists('last_name', $current_filter->param_array) ? 'none' : '' }}" data-field="last_name">
                        <td>Last Name</td>
                        <td data-type="condition">
                            <select name="last_name_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['string'] !!}
                            </select>
                            <span field="last_name_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            {{ Form::select('last_name[]', [], null, ['data-placeholder' => 'Enter a value', 'multiple' => 'multiple', 'class' => 'form-control white-select-type-multiple-tags']) }}
                            <span field="last_name" class="validation-error"></span>
                        </td>
                        <td class="center">
                            <button type="button" class="close remove-filter" data-toggle="tooltip" data-placement="top" title="Remove"><span aria-hidden="true">&times;</span></button>
                        </td>
                    </tr>

                    <tr class="{{ ! array_key_exists('phone', $current_filter->param_array) ? 'none' : '' }}" data-field="phone">
                        <td>Phone</td>
                        <td data-type="condition">
                            <select name="phone_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['string'] !!}
                            </select>
                            <span field="phone_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            {{ Form::select('phone[]', [], null, ['data-placeholder' => 'Enter a value', 'multiple' => 'multiple', 'class' => 'form-control white-select-type-multiple-tags']) }}
                            <span field="phone" class="validation-error"></span>
                        </td>
                        <td class="center">
                            <button type="button" class="close remove-filter" data-toggle="tooltip" data-placement="top" title="Remove"><span aria-hidden="true">&times;</span></button>
                        </td>
                    </tr>

                    <tr class="{{ ! array_key_exists('role', $current_filter->param_array) ? 'none' : '' }}" data-field="role">
                        <td>Role</td>
                        <td data-type="condition">
                            <select name="role_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['dropdown'] !!}
                            </select>
                            <span field="role_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            {{ Form::select('role[]', $dropdown['role'], null, ['class' => 'form-control white-select-type-multiple', 'multiple' => 'multiple', 'data-placeholder' => 'Please select values']) }}
                            <span field="role" class="validation-error"></span>
                        </td>
                        <td class="center">
                            <button type="button" class="close remove-filter" data-toggle="tooltip" data-placement="top" title="Remove"><span aria-hidden="true">&times;</span></button>
                        </td>
                    </tr>

                    <tr class="{{ ! array_key_exists('signature', $current_filter->param_array) ? 'none' : '' }}" data-field="signature">
                        <td>Signature</td>
                        <td data-type="condition">
                            <select name="signature_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['string'] !!}
                            </select>
                            <span field="signature_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            {{ Form::select('signature[]', [], null, ['data-placeholder' => 'Enter a value', 'multiple' => 'multiple', 'class' => 'form-control white-select-type-multiple-tags']) }}
                            <span field="signature" class="validation-error"></span>
                        </td>
                        <td class="center">
                            <button type="button" class="close remove-filter" data-toggle="tooltip" data-placement="top" title="Remove"><span aria-hidden="true">&times;</span></button>
                        </td>
                    </tr>

                    <tr class="{{ ! array_key_exists('state', $current_filter->param_array) ? 'none' : '' }}" data-field="state">
                        <td>State</td>
                        <td data-type="condition">
                            <select name="state_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['string'] !!}
                            </select>
                            <span field="state_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            {{ Form::select('state[]', [], null, ['data-placeholder' => 'Enter a value', 'multiple' => 'multiple', 'class' => 'form-control white-select-type-multiple-tags']) }}
                            <span field="state" class="validation-error"></span>
                        </td>
                        <td class="center">
                            <button type="button" class="close remove-filter" data-toggle="tooltip" data-placement="top" title="Remove"><span aria-hidden="true">&times;</span></button>
                        </td>
                    </tr>

                    <tr class="{{ ! array_key_exists('street', $current_filter->param_array) ? 'none' : '' }}" data-field="street">
                        <td>Street</td>
                        <td data-type="condition">
                            <select name="street_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['string'] !!}
                            </select>
                            <span field="street_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            {{ Form::select('street[]', [], null, ['data-placeholder' => 'Enter a value', 'multiple' => 'multiple', 'class' => 'form-control white-select-type-multiple-tags']) }}
                            <span field="street" class="validation-error"></span>
                        </td>
                        <td class="center">
                            <button type="button" class="close remove-filter" data-toggle="tooltip" data-placement="top" title="Remove"><span aria-hidden="true">&times;</span></button>
                        </td>
                    </tr>

                    <tr class="{{ ! array_key_exists('website', $current_filter->param_array) ? 'none' : '' }}" data-field="website">
                        <td>Website</td>
                        <td data-type="condition">
                            <select name="website_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['string'] !!}
                            </select>
                            <span field="website_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            {{ Form::select('website[]', [], null, ['data-placeholder' => 'Enter a value', 'multiple' => 'multiple', 'class' => 'form-control white-select-type-multiple-tags']) }}
                            <span field="website" class="validation-error"></span>
                        </td>
                        <td class="center">
                            <button type="button" class="close remove-filter" data-toggle="tooltip" data-placement="top" title="Remove"><span aria-hidden="true">&times;</span></button>
                        </td>
                    </tr>

                    <tr class="{{ ! array_key_exists('zip', $current_filter->param_array) ? 'none' : '' }}" data-field="zip">
                        <td>Zip Code</td>
                        <td data-type="condition">
                            <select name="zip_condition" class="form-control white-select-type-single-b">
                                {!! $options_list['string'] !!}
                            </select>
                            <span field="zip_condition" class="validation-error"></span>
                        </td>
                        <td data-type="value">
                            {{ Form::select('zip[]', [], null, ['data-placeholder' => 'Enter a value', 'multiple' => 'multiple', 'class' => 'form-control white-select-type-multiple-tags']) }}
                            <span field="zip" class="validation-error"></span>
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
