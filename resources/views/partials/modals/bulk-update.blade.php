<div class="modal fade" id="bulk-update-form">
    <div class="modal-dialog">
        <div class="modal-loader">
            <div class="spinner"></div>
        </div>

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Mass Update</h4>
            </div> <!-- end modal-header -->

            {{ Form::open(['route' => array_key_exists('route', $page) && Route::has($page['route'] . '.bulk.update') ? $page['route'] . '.bulk.update' : null, 'class' => 'modal-form']) }}
                @if (array_key_exists('view', $page) && view()->exists($page['view'] . '.partials.bulk-update-form'))
                    @include($page['view'] . '.partials.bulk-update-form')
                @endif
            {{ Form::close() }}

            <div class="modal-footer space btn-container">
                <button type="button" class="cancel btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="save btn btn-info ladda-button" data-style="expand-right">
                    <span class="ladda-label">Update</span>
                </button>
            </div> <!-- end modal-footer -->
        </div>
    </div> <!-- end modal-dialog -->
</div> <!-- end bulk-update -->

@push('scripts')
    <script>
        $(document).ready(function () {
            // Click event for updating mass data
            $('#bulk-update-form .save').on('click', function () {
                globalVar.ladda = Ladda.create(this);
                globalVar.ladda.start();
                $(this).attr('disabled', true);
                var form = $(this).closest('.modal').find('form');
                massUpdate(form, true);
            });
        });

        /**
         * Appear bulk update modal with total items count
         *
         * @param {number} checkedCount
         *
         * @return {void}
         */
        function bulkUpdate (checkedCount) {
            // Reset and appear mass update modal
            $('#bulk-update-form form').trigger('reset');
            $('#bulk-update-form form').find('.select2-hidden-accessible').trigger('change');
            $('#bulk-update-form form').find('.white-select-type-single').select2('destroy').select2({ containerCssClass: 'white-container', dropdownCssClass: 'white-dropdown' });
            $('#bulk-update-form form').find('.white-select-type-single-b').select2('destroy').select2({ minimumResultsForSearch: -1, containerCssClass: 'white-container', dropdownCssClass: 'white-dropdown' });
            $('#bulk-update-form .none').hide();
            $('#bulk-update-form .validation-error').html('');
            $('#bulk-update-form .save').attr('disabled', false);

            if (typeof globalVar.ladda !== 'undefined' && globalVar.ladda !== null) {
                globalVar.ladda.remove();
            }

            $('#bulk-update-form').modal({
                show     : true,
                backdrop : false,
                keyboard : false
            });
        }

        /**
         * Mass update ajax request and respond accordingly
         *
         * @param {DOMElement} form
         *
         * @return {void}
         */
        function massUpdate (form) {
            var table     = globalVar.jqueryDataTable;
            var formUrl   = form.prop('action');
            var fieldName = '{!! $page['field'] or null !!}' + '[]';
            var formData  = $("input[name='" + fieldName + "']:checked").serialize() + '&' + form.serialize();

            $.ajax({
                type     : 'POST',
                url      : formUrl,
                data     : formData,
                dataType : 'JSON',
                success  : function (data) {
                    if (data.status === true) {
                        $('#bulk-update-form span.validation-error').html('');
                        delayModalHide('#bulk-update-form', 1);

                        if (typeof table !== 'undefined') {
                            table.ajax.reload(null, false);
                        }
                    } else {
                        $('#bulk-update-form span.validation-error').html('');
                        $.each(data.errors, function (index, value) {
                            $("#bulk-update-form span[field='" + index + "']").html(value);
                        });
                    }

                    if (typeof globalVar.ladda !== 'undefined' && globalVar.ladda !== null) {
                        var statusClass = data.status ? 'success' : 'error';
                        $('#bulk-update-form .ladda-button[data-loading]').find('.ladda-label').addClass(statusClass);
                        globalVar.ladda.stop();
                        globalVar.ladda.remove();

                        setTimeout(function() {
                            $('#bulk-update-form .ladda-label').removeClass(statusClass);
                        }, 1500);
                    }

                    $('#bulk-update-form .save').attr('disabled', false);
                },
                error : function (jqXHR, textStatus, errorThrown) {
                    // ajaxErrorHandler defined in js/app.js
                    ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
                }
            });
        }
    </script>
@endpush
