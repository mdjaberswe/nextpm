<div class="modal-body perfectscroll">
    <div class="form-group">
        <label for="send_to" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Send To</label>

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <select name="send_to_condition" class="form-control multiple-child white-select-type-single-b" data-child="user-send_to">
                <option value="all">All</option>
                <option value="equal" for="send_to">is equal to</option>
                <option value="not_equal" for="send_to">is not equal to</option>
            </select>
            <span field="send_to_condition" class="validation-error"></span>
        </div>
    </div> <!-- end form-group -->

    <div class="form-group user-send_to none" data-for="send_to">
        <div class="col-xs-12 col-sm-offset-3 col-sm-9 col-md-offset-3 col-md-9 col-lg-offset-3 col-lg-9">
            {{ Form::select('send_to[]', $data['admin_users_list'], null, ['class' => 'form-control white-select-type-multiple', 'multiple' => 'multiple', 'data-placeholder' => 'Please select users']) }}
            <span field="send_to" class="validation-error"></span>
        </div>
    </div>

    <div class="form-group">
        <label for="message" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Message <span class="color-danger">*</span></label>

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            {{ Form::textarea('message', null, ['class' => 'form-control sm']) }}
            <span field="message" class="validation-error"></span>
        </div>
    </div> <!-- end form-group -->

    {{ Form::hidden('active_chatroom_id', null) }}
</div> <!-- end modal-body -->
