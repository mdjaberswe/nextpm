<h4 class="tab-title">Notes</h4>

<div class="full">
	<div class="col-xs-12 col-sm-12 col-md-9 col-lg-8">

        @if ((! isset($can_create) && permit('note.create')) || (isset($can_create) && $can_create == true))
    		<div class="full comment-form" data-posturl="{{ route('admin.note.store') }}">
    			<div class="form-group">
    				{{ Form::textarea('note', null, ['class' => 'form-control atwho-inputor', 'placeholder' => 'Start typing to leave a note...', 'at-who' => $at_who_data]) }}
    				{{ Form::hidden('related_type', $module_name) }}
    				{{ Form::hidden('related_id', $module_id) }}
    			</div>

    			<div class="form-group bottom none">
    				<div class="full">
    					<div class="option-icon">
    						<a class="dropzone-attach rot--90" data-toggle="tooltip" data-placement="bottom" title="Attach File"><i class="fa fa-paperclip"></i></a>
    					</div>

    					<div class="form-btn">
                            <button type="button" class="first btn btn-info save-comment ladda-button" data-style="expand-right">
                                <span class="ladda-label" data-status="true">Save</span>
                            </button>
    						<button class="cancel btn btn-secondary">Cancel</button>
    					</div>
    				</div>

    				<div class="full">
    					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-10 modalfree dropzone-container">
    						<div class="modalfree-dropzone" data-linked="note_info" data-url="{{ route('admin.file.upload') }}" data-removeurl="{{ route('admin.file.remove') }}"></div>
    						<div class="dz-preview-container"></div>
    					</div>
    				</div>
    			</div>
    		</div>
        @endif

		<div class="full timeline-pin">
			{!! $module->pin_note_html !!}
		</div>

		<div class="full timeline" data-url="{{ route('admin.note.data', $module_name) }}" data-relatedtype="{{ $module_name }}" data-relatedid="{{ $module_id }}">
			{!! $module->notes_html !!}

			@if ($module->notes()->wherePin(0)->count() > 1)
				<div class="timeline-info end-down {{ ($module->notes->count() < 11) ? 'disable' : null }}">
					<i class="load-icon fa fa-circle-o-notch fa-spin"></i>
					<div class="timeline-icon"><a class="load-timeline"><i class="fa fa-angle-down"></i></a></div>
				</div> <!-- end timeline-info -->
			@endif
		</div> <!-- end timeline -->
	</div>
</div>
