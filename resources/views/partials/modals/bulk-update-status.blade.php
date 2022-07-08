<div class="modal fade top" id="confirm-bulk-status">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">SET STATUS</h4>
            </div>
            <div class="modal-body">
                <p class="message para-modal-msg">
                    The selected {!! strtolower($page['item']) !!}(s) status will be set to new status.<br>
                    Are you sure you want to update status?
                </p>
            </div> <!-- end modal-body -->
            <div class="modal-footer btn-container">
                <button type="button" class="no btn btn-default">No</button>
                <button type="button" class="yes btn">Yes</button>
            </div>
        </div>
    </div> <!-- end modal-dialog -->
</div> <!-- end confirm-bulk-status -->

@push('scripts')
    <script>
        /**
         * Confirm before mass update status
         *
         * @param {string} formUrl
         * @param {Object} formData
         * @param {string} statusType
         * @param {number} statusDanger
         * @param {Object} table
         * @param {string} itemName
         * @param {string} message
         * @param {number} checkedCount
         *
         * @return {void}
         */
        function confirmUpdateStatus (formUrl, formData, statusType, statusDanger, table, itemName, message, checkedCount) {
            // Reset and appear mass update status modal
            var yesClass    = statusDanger === 1 ? 'btn-danger' : 'btn-info';
            var confirmIcon = statusDanger === 1 ? 'fa-exclamation-circle color-danger' : 'fa-check-circle color-success';
            $('#confirm-bulk-status .modal-title').html('SET STATUS - ' + statusType.toUpperCase());
            $('#confirm-bulk-status .message').html(message);
            $('#confirm-bulk-status .yes').attr('class', 'yes btn');
            $('#confirm-bulk-status .yes').addClass(yesClass);

            $('#confirm-bulk-status').modal({
                show     : true,
                backdrop : false,
                keyboard : false
            });

            var confirmMessage = itemName + ' status has been set to ' + statusType + '.';

            if (checkedCount > 1) {
                confirmMessage = itemName + ' status have been set to ' + statusType + '.';
            }

            // Ajax request for mass update status
            $('#confirm-bulk-status .yes').on('click', function () {
                $('#confirm-bulk-status .message').html("<img src='{!! asset('plugins/datatable/images/preloader.gif') !!}'>");

                $.ajax({
                    type     : 'POST',
                    url      : formUrl,
                    data     : formData,
                    dataType : 'JSON',
                    success  : function (data) {
                        if (data.status === true) {
                            $('#confirm-bulk-status .message').html("<span class='fa " + confirmIcon + "'></span> " + confirmMessage);
                            table.ajax.reload(null, false);
                            // delayModalHide defined in js/app.js
                            delayModalHide('#confirm-bulk-status', 1);
                        } else {
                            $('#confirm-bulk-status .message').html("<span class='fa fa-exclamation-circle color-danger'></span> Operation failed. Please try again.");
                            delayModalHide('#confirm-bulk-status', 1);
                        }
                    },
                    error : function (jqXHR, textStatus, errorThrown) {
                        // ajaxErrorHandler defined in js/app.js
                        ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
                    }
                });

                $(this).off('click');
            });

            // Retreat execution of mass update status
            $('#confirm-bulk-status .no').on('click', function () {
                $('#confirm-bulk-status').modal('hide');
                $(this).off('click');
            });
        }
    </script>
@endpush
