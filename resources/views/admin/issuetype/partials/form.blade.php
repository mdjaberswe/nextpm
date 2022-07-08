<div class="modal-body perfectscroll">
    <div class="form-group">
        <label for="name" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Name <span class="color-danger">*</span></label>

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            {{ Form::text('name', null, ['placeholder' => 'Enter issue type name', 'class' => 'form-control']) }}
            <span field="name" class="validation-error"></span>
        </div>
    </div> <!-- end form-group -->

    <div class="form-group">
        <label for="position" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Position <span class="color-danger">*</span></label>

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            {{ Form::select('position', $position_list, $max_position_id, ['class' => 'form-control position white-select-type-single']) }}
            <span field="position" class="validation-error"></span>
        </div>
    </div> <!-- end form-group -->

    <div class="form-group">
        <label for="description" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Description</label>

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            {{ Form::textarea('description', null, ['class' => 'form-control']) }}
            <span field="description" class="validation-error"></span>
        </div>
    </div> <!-- end form-group -->
</div> <!-- end modal-body -->

@if (isset($form) && $form == 'edit')
    {{ Form::hidden('id', null) }}
@endif
