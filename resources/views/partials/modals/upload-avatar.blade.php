<div class="modal-body perfectscroll" data-tabledraw="false">
    <div class="form-group">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <span field="photo" class="validation-error block-center"></span>
            <span field="linked_type" class="validation-error block-center"></span>
            <span field="x" class="validation-error block-center"></span>
            <span field="y" class="validation-error block-center"></span>
            <span field="width" class="validation-error block-center"></span>
            <span field="height" class="validation-error block-center"></span>
            <div class="uploadzone">
                <p>Upload your photo here.</p>
                <p class="btn-container"><button class="browse btn btn-info">BROWSE LOCAL FILES...</button></p>
                {{ Form::file('photo', ['accept' => 'image/x-png,image/gif,image/jpeg,image/webp', 'class' => 'none-force']) }}
            </div>
            <div class="cropper-wrap center none">
                <img class="cropper" src="{{ asset('img/white-blank.png') }}"/>
                {{ Form::hidden('x', null) }}
                {{ Form::hidden('y', null) }}
                {{ Form::hidden('width', null) }}
                {{ Form::hidden('height', null) }}
            </div>
        </div>
    </div> <!-- end form-group -->
</div> <!-- end modal-body -->

{{ Form::hidden('linked_id', null) }}
{{ Form::hidden('linked_type', null) }}
