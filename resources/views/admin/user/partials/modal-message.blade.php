<div class="modal-body perfectscroll">
    <div class="form-group">
        <label for="receiver" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">To <span class="color-danger">*</span></label>

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            {{ Form::select('receiver[]', $receivers_list, null, ['class' => 'form-control white-select-type-multiple', 'multiple' => 'multiple']) }}
            <span field="receiver" class="validation-error"></span>
        </div>
    </div> <!-- end form-group -->

    <div class="form-group">
        <label for="message" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Message <span class="color-danger">*</span></label>

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            {{ Form::textarea('message', null, ['class' => 'form-control md', 'placeholder' => 'Write a message...']) }}
            <span field="message" class="validation-error"></span>
        </div>
    </div> <!-- end form-group -->
</div> <!-- end modal-body -->
