<div class="modal fade" id="import-form">
    <div class="modal-dialog">
        <div class="modal-loader">
            <div class="spinner"></div>
        </div>

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Import <span class="capitalize"></span></h4>
            </div> <!-- end modal-header -->

            <div class="full content">
                @include('partials.modals.import.csv', ['module' => null])
            </div>

            <div class="modal-footer space btn-container">
                <button type="button" class="cancel btn btn-default" data-dismiss="modal">Cancel</button>

                <button type="button" class="save csv btn btn-info ladda-button" data-style="expand-right">
                    <span class="ladda-label">Next</span>
                </button>
            </div> <!-- end modal-footer -->
        </div>
    </div> <!-- end modal-dialog -->
</div> <!-- end import-form -->

@push('scripts')
    <script>
        $(document).ready(function () {
            // Click event: Reset and open a common import modal
            $('.import-btn').on('click', function () {
                $('#import-form .none').hide();
                $('#import-form span.validation-error').html('');
                $('#import-form .error-content').html('');
                $('#import-form .form-group').hide();
                $('#import-form .modal-loader').show();
                $('#import-form .save').show();
                $('#import-form .save').html('Next');
                $('#import-form .save').removeClass('import');
                $('#import-form .save').addClass('csv');
                $('#import-form .cancel').removeClass('btn-info');
                $('#import-form .cancel').addClass('btn-default');
                $('#import-form .cancel').html('Cancel');

                if (typeof $(this).data('item') !== 'undefined') {
                    $('#import-form .modal-title').html("Import <span class='capitalize'>" + $(this).data('item') + 's</span>');
                }

                if (typeof globalVar.ladda !== 'undefined' && globalVar.ladda !== null) {
                    globalVar.ladda.remove();
                }

                $('#import-form').modal({
                    show     : true,
                    backdrop : false,
                    keyboard : false
                });

                $.ajax({
                    type    : 'GET',
                    url     : $(this).data('url'),
                    data    : { module: $(this).data('item') },
                    success : function (data) {
                        if (data.status === true) {
                            $dataObj = $(data.html);

                            if ($dataObj.length) {
                                $('#import-form .modal-content').css('height', 'auto');
                                $('#import-form .content').html($dataObj);
                                $('#import-form form').trigger('reset');
                                $('#import-form form').find('.select2-hidden-accessible').trigger('change');
                                $('#import-form form').find('option').prop('disabled', false);
                                $('#import-form form').find('.white-select-type-single').select2('destroy').select2({ containerCssClass: 'white-container', dropdownCssClass: 'white-dropdown' });
                                $('#import-form form').find('.white-select-type-single-b').select2('destroy').select2({ minimumResultsForSearch: -1, containerCssClass: 'white-container', dropdownCssClass: 'white-dropdown' });
                                var ps = new PerfectScrollbar('#import-form .modal-body');
                                $('#import-form .modal-body').animate({ scrollTop: 0 }, 10);
                                $('#import-form .modal-loader').fadeOut(850);
                                // pluginInit defined in js/app.js
                                pluginInit();
                            }
                        } else {
                            $('#import-form .modal-loader').fadeOut(1000);
                            $('#import-form .form-group').css('opacity', 0).slideDown('slow').animate({ opacity: 1 });
                            // delayModalHide defined in js/app.js
                            delayModalHide('#import-form', 2);
                        }
                    },
                    error : function (jqXHR, textStatus, errorThrown) {
                        // ajaxErrorHandler defined in js/app.js
                        ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
                    }
                });
            });

            // Ajax request to map CSV file with DB columns and response mapping result HTML into the modal body
            $(document).on('click', '#import-form .csv', function (e) {
                e.preventDefault();
                globalVar.ladda = Ladda.create(this);
                globalVar.ladda.start();
                $(this).attr('disabled', true);

                if (!$(this).hasClass('import')) {
                    var form     = $(this).closest('.modal').find('form');
                    var formUrl  = form.prop('action');
                    var formData = new FormData($('#import-file-form').get(0));

                    $.ajax({
                        type        : 'POST',
                        url         : formUrl,
                        data        : formData,
                        dataType    : 'JSON',
                        processData : false,
                        contentType : false,
                        success     : function (data) {
                            if (data.status === true) {
                                $('#import-form span.validation-error').html('');
                                $dataObj = $(data.html);

                                // Render result HTML of CSV mapping with DB columns
                                if ($dataObj.length) {
                                    $('#import-form .modal-content').css('height', 'auto');
                                    $('#import-form .content').html($dataObj);
                                    var ps = new PerfectScrollbar('#import-form .modal-body');
                                    $('#import-form .modal-body').animate({ scrollTop: 0 }, 10);
                                    // pluginInit defined in js/app.js
                                    pluginInit();
                                    $('#import-form .modal-title').html(data.modalTitle);
                                    $('#import-form .save').removeClass('csv');
                                    $('#import-form .save').addClass('import');
                                    $('#import-form .save').html('Import');

                                    $.each(data.info, function (index, value) {
                                        $("#import-form [name='" + index + "']").val(value);
                                    });
                                }
                            } else {
                                $('#import-form span.validation-error').html('');
                                $.each(data.errors, function (index, value) {
                                    $("#import-form span[field='" + index + "']").html(value);
                                });
                                $('#import-form .modal-body').animate({ scrollTop: 0 });
                            }

                            if (typeof globalVar.ladda !== 'undefined' && globalVar.ladda !== null) {
                                var statusClass = data.status ? 'success' : 'error';
                                $('#import-form .ladda-button[data-loading]').find('.ladda-label').addClass(statusClass);
                                globalVar.ladda.stop();
                                globalVar.ladda.remove();

                                setTimeout(function () {
                                    $('#import-form .ladda-label').removeClass(statusClass);
                                }, 1500);
                            }

                            $('#import-form .save').attr('disabled', false);
                        },
                        error : function (jqXHR, textStatus, errorThrown) {
                            // ajaxErrorHandler defined in js/app.js
                            ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
                        }
                    });
                }
            });

            // Ajax request to start import data into DB
            $(document).on('click', '#import-form .import', function (e) {
                e.preventDefault();
                globalVar.ladda = Ladda.create(this);
                globalVar.ladda.start();
                $(this).attr('disabled', true);

                var form     = $(this).closest('.modal').find('form');
                var formUrl  = form.prop('action');
                var formData = form.serialize();

                $.ajax({
                    type     : 'POST',
                    url      : formUrl,
                    data     : formData,
                    dataType : 'JSON',
                    success  : function (data) {
                        if (data.status === true) {
                            $('#import-form .error-content').html('');
                            $('#import-form .modal-content').animate({ height: '225px' });
                            $('#import-form .modal-title').html("Importing <span class='dots-processing'>...</span>");
                            $('#import-form .content').html(
                                "<div class='modal-body perfectscroll'>" +
                                "<div class='form-group'>" +
                                "<div class='col-xs-12'>" +
                                "<p class='para-clean'>It will take a few minutes to complete this import.</p>" +
                                "<div class='alert-note success'>" +
                                '<p>You will be notified when the import is completed.</p>' +
                                '</div></div></div></div>'
                            );
                            var ps = new PerfectScrollbar('#import-form .modal-body');
                            $('#import-form .modal-body').animate({ scrollTop: 0 }, 10);
                            $('#import-form .save').removeClass('import');
                            $('#import-form .save').hide();
                            $('#import-form .cancel').addClass('btn-info');
                            $('#import-form .cancel').removeClass('btn-default');
                            $('#import-form .cancel').html('Okay');
                            delayModalHide('#import-form', 5);
                        } else {
                            $('#import-form .error-content').html('');

                            $.each(data.errors, function (index, fieldErrors) {
                                $.each(fieldErrors, function (key, value) {
                                    if (key === 0) {
                                        $('#import-form .error-content').append("<span class='validation-error'>" + value + '</span>');
                                    } else {
                                        $('#import-form .error-content').append("<br><span class='validation-error'>" + value + '</span>');
                                    }
                                });
                            });

                            $('#import-form .modal-body').animate({ scrollTop: 0 });
                        }

                        if (typeof globalVar.ladda !== 'undefined' && globalVar.ladda !== null) {
                            var statusClass = data.status ? 'success' : 'error';
                            $('#import-form .ladda-button[data-loading]').find('.ladda-label').addClass(statusClass);
                            globalVar.ladda.stop();
                            globalVar.ladda.remove();

                            setTimeout(function () {
                                $('#import-form .ladda-label').removeClass(statusClass);
                            }, 1500);
                        }

                        $('#import-form .save').attr('disabled', false);
                    },
                    error : function (jqXHR, textStatus, errorThrown) {
                        // ajaxErrorHandler defined in js/app.js
                        ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
                    }
                });
            });
        });
    </script>
@endpush
