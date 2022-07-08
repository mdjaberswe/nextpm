<div class="modal fade top" id="confirm-bulk-delete">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">CONFIRM</h4>
            </div>
            <div class="modal-body">
                <p class="message para-modal-msg">Are you sure? You won't be able to undo this action.</p>
            </div> <!-- end modal-body -->
            <div class="modal-footer btn-container">
                <button type="button" class="no btn btn-primary">No</button>
                <button type="button" class="yes btn btn-danger">Yes</button>
            </div>
        </div>
    </div> <!-- end modal-dialog -->
</div> <!-- end confirm-bulk-delete -->

@push('scripts')
    <script>
        // Confirm before mass delete action execute
        function confirmBulkDelete (formUrl, formData, table, itemName, message, checkedCount) {
            $('#confirm-bulk-delete').modal({
                show     : true,
                backdrop : false,
                keyboard : false
            });

            $('#confirm-bulk-delete .message').html(message);
            var confirmMessage = itemName + ' has been deleted.';

            if (checkedCount > 1) {
                confirmMessage = itemName + ' have been deleted.';
            }

            // Ajax request to delete mass data
            $('#confirm-bulk-delete .yes').on('click', function () {
                $('#confirm-bulk-delete .message').html("<img src='{!! asset('plugins/datatable/images/preloader.gif') !!}'>");

                $.ajax({
                    type     : 'POST',
                    url      : formUrl,
                    data     : formData,
                    dataType : 'JSON',
                    success  : function (data) {
                        if (data.status === true) {
                            $('#confirm-bulk-delete .message').html("<span class='fa fa-times-circle color-danger'></span> " + confirmMessage);
                            // delayModalHide defined in js/app.js
                            delayModalHide('#confirm-bulk-delete', 1);
                            table.ajax.reload(null, false);
                        } else {
                            $('#confirm-bulk-delete .message').html("<span class='fa fa-exclamation-circle color-danger'></span> Operation failed. Please try again.");
                            delayModalHide('#confirm-bulk-delete', 1);
                        }
                    },
                    error : function (jqXHR, textStatus, errorThrown) {
                        // ajaxErrorHandler defined in js/app.js
                        ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
                    }
                });

                $(this).off('click');
            });

            // Retreat execution of deleting mass data
            $('#confirm-bulk-delete .no').on('click', function () {
                $('#confirm-bulk-delete').modal('hide');
                $(this).off('click');
            });
        }
    </script>
@endpush
