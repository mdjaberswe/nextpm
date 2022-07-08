<div class="modal-body perfectscroll">
    <div class="form-group">
        <label for="view_name" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">View Name <span class="color-danger">*</span></label>

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            {{ Form::text('view_name', null, ['placeholder' => 'Enter view name', 'class' => 'form-control']) }}
            <span field="view_name" class="validation-error"></span>
            <span field="module" class="validation-error"></span>
        </div>
    </div> <!-- end form-group -->

    <div class="form-group show-if">
        <label for="visible_to" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Visible to</label>

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            {{ Form::select('visible_to', ['only_me' => 'Only me', 'everyone' => 'Everyone', 'selected_users' => 'Selected users'], 'only_me', ['class' => 'form-control white-select-type-single-b']) }}
            <span field="visible_to" class="validation-error"></span>
        </div>
    </div> <!-- end form-group -->

    <div class="full none">
        <div class="form-group none selected_users-list">
            <label for="selected_users" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Selected users</label>

            <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
                {{ Form::select('selected_users[]', $admin_users_list, null, ['class' => 'form-control white-select-type-multiple', 'multiple' => 'multiple', 'data-placeholder' => 'Search...']) }}
                <span field="selected_users" class="validation-error"></span>
            </div>
        </div> <!-- end form-group -->
    </div>

    {{ Form::hidden('module', null) }}
</div> <!-- end modal-body -->

@if (isset($form) && $form == 'edit')
    {{ Form::hidden('id', null) }}
@endif
