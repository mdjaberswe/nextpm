<div class="modal-body perfectscroll">
    <div class="form-group">
        <label for="name" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Name <span class="color-danger">*</span></label>

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            {{ Form::text('name', null, ['placeholder' => 'Enter task status name', 'class' => 'form-control']) }}
            <span field="name" class="validation-error"></span>
        </div>
    </div> <!-- end form-group -->

    <div class="form-group">
        <label for="category" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Category</label>

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <select name="category" class="form-control white-select-type-single-b" data-option-related="completion_percentage">
                <option value="open" relatedval="0">Open</option>
                <option value="closed" relatedval="100" freeze="true">Closed</option>
            </select>
            <span field="category" class="validation-error"></span>
        </div>
    </div> <!-- end form-group -->

    <div class="form-group percentage-options">
        <label for="completion_percentage" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">{!! fill_up_space('Completion Percentage') !!}</label>

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <select name="completion_percentage" class="form-control white-select-type-single-b">
                {!! HtmlElement::renderNumericOptions(0, 100, 10) !!}
            </select>
            <span field="completion_percentage" class="validation-error"></span>
        </div>
    </div> <!-- end form-group -->

    <div class="form-group">
        <label for="position" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Position</label>

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
