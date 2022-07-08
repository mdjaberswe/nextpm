<div class="modal-body perfectscroll">
    <div class="form-group">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 dropzone-container">
            <span field="linked_id" class="validation-error"></span>
            <span field="linked_type" class="validation-error"></span>
            <span field="dropzone-error" class="validation-error none"></span>
            <div id="common-attach" class="dropzone" data-preview="common-attach-preview" data-linked="true" data-url="{!! route('admin.file.upload') !!}" data-removeurl="{!! route('admin.file.remove') !!}"></div>
            <div id="common-attach-preview" class="dz-preview-container"></div>
        </div>
    </div> <!-- end form-group -->
</div> <!-- end modal-body -->

{{ Form::hidden('linked_id', null) }}
{{ Form::hidden('linked_type', null) }}
