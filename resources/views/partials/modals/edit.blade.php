<div class="modal fade {{ $page['modal_size'] or 'large' }} min-body100" id="edit-form">
    <div class="modal-dialog">
        <div class="modal-loader">
            <div class="spinner"></div>
        </div>

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                @if (isset($page['modal_title_link']) && $page['modal_title_link'] == true)
                    <h4 id="modal-title" class="modal-title"><a href=""></a></h4>
                @else
                    <h4 class="modal-title capitalize">Edit {{ $page['item'] }}</h4>
                @endif
            </div> <!-- end modal-header -->

            @if (! isset($yield) || (isset($yield) && $yield == true))
                @yield('modaledit')
            @else
                {{ Form::open(['route' => [$page['route'] . '.update', null], 'method' => 'put', 'class' => 'modal-form']) }}
                    @include($page['view'] . '.partials.form', ['form' => 'edit'])
                {{ Form::close() }}
            @endif

            <div class="modal-footer space btn-container">
                <button type="button" class="cancel btn btn-default" data-dismiss="modal">Cancel</button>

                @if (isset($page['modal_footer_delete']) && $page['modal_footer_delete'] == true)
                    {{ Form::open(['route' => null, 'method' => 'delete', 'class' => 'left-justify', 'id' => 'modal-footer-delete', 'data-item' => $page['item']]) }}
                        {{ Form::hidden('id', null) }}
                        <button type="submit" class="modal-delete btn btn-danger">Delete</button>
                    {{ Form::close() }}
                @endif

                <button type="button" class="save btn btn-info ladda-button" data-style="expand-right">
                    <span class="ladda-label">Save</span>
                </button>
            </div> <!-- end modal-footer -->
        </div>
    </div> <!-- end modal-dialog -->
</div> <!-- end edit-form -->

@push('scripts')
    <script>
        $(document).ready(function () {
            // Ajax request for updating data and respond accordingly by modalDataUpdate
            $('#edit-form .save').on('click', function () {
                globalVar.ladda = Ladda.create(this);
                globalVar.ladda.start();
                $(this).attr('disabled', true);
                var form = $(this).parent().parent().find('form');
                modalDataUpdate(form);
            });
        });

        /**
         * Ajax request to update data and respond accordingly.
         *
         * @param {DOMElement} form
         *
         * @return {void}
         */
        function modalDataUpdate (form) {
            var table    = globalVar.jqueryDataTable;
            var formUrl  = form.prop('action');
            var formData = form.serialize();

            $.ajax({
                type     : 'POST',
                url      : formUrl,
                data     : formData,
                dataType : 'JSON',
                success  : function (data) {
                    if (data.status === true) {
                        $('#edit-form span.validation-error').html('');
                        delayModalHide('#edit-form', 1);

                        // Reload data table after updating data
                        if (typeof table !== 'undefined') {
                            table.ajax.reload(null, false);

                            if (typeof data.saveId !== 'undefined' && data.saveId !== null) {
                                // focusSavedRow defined in js/app.js
                                focusSavedRow(table, data.saveId, false);
                            }
                        }

                        // Calendar data load accordingly after updating data
                        if ($('.calendar').get(0) && typeof data.updateEvent !== 'undefined' && data.updateEvent !== null) {
                            var event = $.parseJSON(data.updateEvent);
                            $('.calendar').fullCalendar('removeEvents', event.id);
                            $('.calendar').fullCalendar('renderEvent', event);
                        }

                        // Kanban board load accordingly after updating data
                        if ($('.funnel-container').get(0)) {
                            if (typeof data.kanbanCardRemove !== 'undefined' && data.kanbanCardRemove !== false) {
                                $.each(data.kanbanCardRemove, function (index, cardId) {
                                    $('.funnel-stage #' + cardId).remove();
                                });

                                // kanbanCountResponse defined in js/app.js
                                kanbanCountResponse(data);
                            } else {
                                // kanbanUpdateResponse defined in js/app.js
                                kanbanUpdateResponse(data);
                            }
                        }

                        // Render HTML accordingly after updating data
                        if (typeof data.realtime !== 'undefined') {
                            $.each(data.realtime, function (index, value) {
                                $("*[data-realtime='" + index + "']").html(value);
                            });
                        }
                    } else {
                        $('#edit-form span.validation-error').html('');
                        $.each(data.errors, function (index, value) {
                            $("#edit-form span[field='" + index + "']").html(value);
                        });
                    }

                    if (typeof globalVar.ladda !== 'undefined' && globalVar.ladda !== null) {
                        var statusClass = data.status ? 'success' : 'error';
                        $('#edit-form .ladda-button[data-loading]').find('.ladda-label').addClass(statusClass);
                        globalVar.ladda.stop();
                        globalVar.ladda.remove();

                        setTimeout(function () {
                            $('#edit-form .ladda-label').removeClass(statusClass);
                        }, 1500);
                    }

                    $('#edit-form .save').attr('disabled', false);
                },
                error : function (jqXHR, textStatus, errorThrown) {
                    // ajaxErrorHandler defined in js/app.js
                    ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
                }
            });
        }

        /**
         * Appear edit modal with default data.
         *
         * @param {number} id
         * @param {Object} data
         * @param {string} url
         * @param {string} updateUrl
         *
         * @return {void}
         */
        function getEditData (id, data, url, updateUrl) {
            $('#edit-form form').trigger('reset');
            $('#edit-form form').find('.select2-hidden-accessible').trigger('change');
            $('#edit-form span.validation-error').html('');
            $('#edit-form .save').hide();
            $('#edit-form .save').attr('disabled', false);
            $('#edit-form .form-group').hide();
            $('#modal-footer-delete').hide();
            $('#edit-form .modal-loader').show();

            if (typeof globalVar.ladda !== 'undefined' && globalVar.ladda !== null) {
                globalVar.ladda.remove();
            }

            $('#edit-form').modal({
                show     : true,
                backdrop : false,
                keyboard : false
            });

            // Ajax request to load default data
            $.ajax({
                type    : 'GET',
                url     : url,
                data    : data,
                success : function (data) {
                    if (data.status === true) {
                        $('#edit-form form').prop('action', updateUrl);
                        $("#edit-form input[name='_method']").val('PUT');
                        $('#edit-form form').find('select').prop('disabled', false);
                        $('#edit-form form').find('input').prop('readOnly', false);

                        var hide = '';
                        var show = '';

                        // Render requested a dropdown list
                        if (typeof data.info.selectlist !== 'undefined') {
                            $.each(data.info.selectlist, function (fieldName, options) {
                                var selectlist = $("#edit-form select[name='" + fieldName + "']").empty();

                                $('<option/>', {
                                    value : '',
                                    text  : '-None-'
                                }).appendTo(selectlist);

                                $.each(options, function (optVal, displayText) {
                                    $('<option/>', {
                                        value : optVal,
                                        text  : displayText
                                    }).appendTo(selectlist);
                                });
                            });
                        }

                        $.each(data.info, function (index, value) {
                            // Load default value
                            if ($("#edit-form *[name='" + index + "']").get(0)) {
                                if ($("#edit-form *[name='" + index + "']").is(':checkbox')) {
                                    if ($("#edit-form *[name='" + index + "']").val() == value) {
                                        $("#edit-form *[name='" + index + "']").prop('checked', true);
                                    } else {
                                        $("#edit-form *[name='" + index + "']").prop('checked', false);
                                    }
                                } else {
                                    if ($("#edit-form *[name='" + index + "']").hasClass('white-select-type-multiple-tags')) {
                                        var tagSelect = $("#edit-form select[name='" + index + "']").empty();

                                        $(value).each(function (index, optVal) {
                                            $('<option/>', {
                                                value : optVal,
                                                text  : optVal
                                            }).appendTo(tagSelect);
                                        });
                                    }

                                    $("#edit-form *[name='" + index + "']").not(':radio').val(value).trigger('change');
                                }

                                if ($("#edit-form *[name='" + index + "']").is(':radio')) {
                                    $("#edit-form *[name='" + index + "']").each(function (index, obj) {
                                        if ($(obj).val() == value) {
                                            $(obj).prop('checked', true);
                                        } else {
                                            $(obj).prop('checked', false);
                                        }
                                    });
                                }
                            }

                            // Freeze fields can not be editable|updatable
                            if (index === 'freeze') {
                                $.each(value, function (key, val) {
                                    if ($("#edit-form *[name='" + val + "']").is('select') || $("#edit-form *[name='" + val + "']").is(':radio')) {
                                        $("#edit-form *[name='" + val + "']").prop('disabled', true);
                                        $("#edit-form *[name='" + val + "']").closest('.child-permission').css('opacity', 0.5);
                                    } else {
                                        $("#edit-form *[name='" + val + "']").prop('readOnly', true);
                                    }
                                });
                            }

                            // Show fields can not be hidden
                            if (index === 'show') {
                                $.each(value, function (key, val) {
                                    show += "#edit-form *[name='" + val + "'],";
                                });

                                show = show.slice(0, -1);
                            }

                            // Hidden fields
                            if (index === 'hide') {
                                $.each(value, function (key, val) {
                                    $('#edit-form .' + val + '-input').hide();
                                    hide += '.' + val + '-input' + ',';
                                });

                                hide = hide.slice(0, -1);
                            }

                            // If the response has the modal title with show page link request then render show page link
                            if (index === 'modal_title_link') {
                                if ($('#modal-title').get(0)) {
                                    $('#modal-title a').html(value.title);
                                    $('#modal-title a').attr('href', value.href);
                                }
                            }

                            // If the response has the footer delete button request then show the delete button
                            if (index === 'modal_footer_delete') {
                                $('#modal-footer-delete').show();

                                if ($('#modal-footer-delete').get(0)) {
                                    $('#modal-footer-delete').attr('action', value.action);
                                    $('#modal-footer-delete input[name="id"]').val(value.id);
                                }
                            }
                        });

                        $('#edit-form .datepicker').each(function (index, value) {
                            $(this).datepicker('update', $(this).val());
                        });

                        $('#edit-form .modal-loader').fadeOut(1000);
                        $('#edit-form .modal-body').animate({ scrollTop: 1 });
                        $(show).closest('.none').css('opacity', 0.5).slideDown('slow').animate({ opacity: 1 });
                        $('#edit-form .form-group').not(hide).css('opacity', 0).slideDown('slow').animate({ opacity: 1 });
                        $('#edit-form .modal-body').animate({ scrollTop: 0 });
                        $('#edit-form .save').show();
                    } else {
                        $('#edit-form .modal-loader').fadeOut(1000);
                        $('#edit-form .form-group').css('opacity', 0).slideDown('slow').animate({ opacity: 1 });
                        // delayModalHide defined in js/app.js
                        delayModalHide('#edit-form', 2);
                    }
                },
                error : function (jqXHR, textStatus, errorThrown) {
                    // ajaxErrorHandler defined in js/app.js
                    ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
                }
            });
        }
    </script>
@endpush
