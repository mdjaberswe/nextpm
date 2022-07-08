<div class="modal fade large" id="common-edit">
    <div class="modal-dialog">
        <div class="modal-loader">
            <div class="spinner"></div>
        </div>

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                @if (isset($page['modal_title_link']) && $page['modal_title_link'] == true)
                    <h4 id="common-edit-modal-title" class="modal-title"><a href=""></a></h4>
                @else
                    <h4 class="modal-title capitalize">Edit</h4>
                @endif
            </div> <!-- end modal-header -->

            {{ Form::open(['route' => null, 'method' => 'put', 'class' => 'modal-form']) }}
                <div id="common-edit-content" class="full"></div>
            {{ Form::close() }}

            <div class="modal-footer space btn-container">
                <button type="button" class="cancel btn btn-default" data-dismiss="modal">Cancel</button>

                @if (isset($page['modal_footer_delete']) && $page['modal_footer_delete'] == true)
                    {{ Form::open(['route' => null, 'method' => 'delete', 'class' => 'left-justify', 'id' => 'common-edit-footer-delete', 'data-item' => 'item']) }}
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
            // Click event: To reset and open a common edit modal
            $(document).on('click', '.common-edit-btn', function (event) {
                var id          = $(this).attr('editid');
                var defaultData = typeof $(this).attr('data-default') !== 'undefined' ? $(this).attr('data-default') : null;
                var data        = { id: id, html: true, default: defaultData };
                var tr          = $($(this).closest('tr'));
                var url         = $(this).attr('data-url');
                var updateUrl   = $(this).attr('data-posturl');
                var title       = typeof $(this).attr('modal-title') === 'undefined' ? 'Edit ' + $(this).attr('data-item') : $(this).attr('modal-title');
                title          += typeof $(this).attr('modal-sub-title') === 'undefined' ? '' : " <span class='shadow bracket'>" + $(this).attr('modal-sub-title') + '</span>';

                $('#common-edit .cancel').html('Cancel');
                $('#common-edit .save').html('Save');
                $('#common-edit .save').attr('disabled', false);
                $('#common-edit .save').show();

                if (typeof $(this).attr('save-hide') !== 'undefined' && $(this).attr('save-hide') === 'true') {
                    $('#common-edit .save').hide();
                }

                if (typeof $(this).attr('save-txt') !== 'undefined') {
                    $('#common-edit .save').html($(this).attr('save-txt'));
                }

                if (typeof $(this).attr('cancel-txt') !== 'undefined') {
                    $('#common-edit .cancel').html($(this).attr('cancel-txt'));
                }

                $('#common-edit-footer-delete').hide();
                $('#common-edit #common-edit-content').hide();
                $('#common-edit .modal-title').html(title);
                $('#common-edit').addClass('large');

                if (typeof $(this).attr('modal-small') !== 'undefined') {
                    $('#common-edit').removeClass('large');
                    $('#common-edit').removeClass('medium');

                    if ($(this).attr('modal-small') !== 'true') {
                        $('#common-edit').addClass($(this).attr('modal-small'));
                    }
                }

                getCommonEditData(id, data, url, updateUrl, tr, '#common-edit');
            });

            // Ajax request for updating data and respond accordingly by modalCommonUpdate
            $('#common-edit .save').on('click', function () {
                globalVar.ladda = Ladda.create(this);
                globalVar.ladda.start();
                $(this).attr('disabled', true);
                var form = $(this).parent().parent().find('form');
                modalCommonUpdate(form, '#common-edit');
            });
        });

        /**
         * Ajax request to update data in a common modal
         *
         * @param {DOMElement} form
         * @param {string}     modalId
         *
         * @return {void}
         */
        function modalCommonUpdate (form, modalId) {
            var formUrl  = form.prop('action');
            var formData = form.serialize();

            $.ajax({
                type     : 'POST',
                url      : formUrl,
                data     : formData,
                dataType : 'JSON',
                success  : function (data) {
                    if (data.status === true) {
                        $(modalId + ' span.validation-error').html('');
                        delayModalHide(modalId, 1);

                        // Reload data table after updating data
                        if (typeof data.falseReload === 'undefined') {
                            var table = globalVar.jqueryDataTable;

                            if (typeof data.tabTable !== 'undefined' && typeof globalVar.dataTable[data.tabTable] !== 'undefined') {
                                table = globalVar.dataTable[data.tabTable];
                            }

                            if (typeof table !== 'undefined') {
                                table.ajax.reload(null, false);

                                if (typeof data.saveId !== 'undefined' && data.saveId !== null) {
                                    // focusSavedRow defined in js/app.js
                                    focusSavedRow(table, data.saveId, false);
                                }
                            }
                        }

                        // Calendar data load accordingly after updating data
                        if ($('.calendar').get(0) && typeof data.updateEvent !== 'undefined' && data.updateEvent !== null) {
                            var event = $.parseJSON(data.updateEvent);
                            $('.calendar').fullCalendar('removeEvents', event.id);
                            $('.calendar').fullCalendar('renderEvent', event);
                        }

                        // Breadcrumb views dropdown list load accordingly after updating data
                        if (typeof data.viewName !== 'undefined' && data.viewName !== null) {
                            $('.breadcrumb-action.delete').attr('modal-sub-title', data.viewName);
                            $(".breadcrumb-select[name='view'] option[value='" + data.viewId + "']").html(data.viewName);
                            $(".breadcrumb-select[name='view']").val(parseInt(data.viewId, 10));
                            $(".breadcrumb-select[name='view']").trigger('change');
                            select2PluginInit();
                        }

                        // Gantt chart loads accordingly after updating data
                        if ($('.gantt').get(0) && typeof data.gantt !== 'undefined' && data.gantt === true) {
                            initGanttChart();
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

                        // Pie chart loads accordingly after updating data
                        if (typeof data.pieData !== 'undefined') {
                            initChartJsPie(data.pieId, data.pieData.labels, data.pieData.values, data.pieData.backgrounds);
                        }

                        // Render HTML accordingly after updating data
                        if (typeof data.realtime !== 'undefined') {
                            $(data.realtime).each(function (index, value) {
                                $("*[data-realtime='" + value[0] + "']").html(value[1]);
                            });
                        }

                        if (typeof data.notifyMsgs !== 'undefined' && data.notifyMsgs.length) {
                            $.each(data.notifyMsgs, function (index, msg) {
                                $.notify({ message: msg }, globalVar.successNotify);
                            });
                        }
                    } else {
                        $(modalId + ' span.validation-error').html('');
                        $.each(data.errors, function (index, value) {
                            $(modalId + " span[field='" + index + "']").html(value);
                        });
                    }

                    if (typeof globalVar.ladda !== 'undefined' && globalVar.ladda !== null) {
                        var statusClass = data.status ? 'success' : 'error';
                        $(modalId + ' .ladda-button[data-loading]').find('.ladda-label').addClass(statusClass);
                        globalVar.ladda.stop();
                        globalVar.ladda.remove();

                        setTimeout(function () {
                            $(modalId + ' .ladda-label').removeClass(statusClass);
                        }, 1500);
                    }

                    $(modalId + ' .save').attr('disabled', false);
                },
                error : function (jqXHR, textStatus, errorThrown) {
                    // ajaxErrorHandler defined in js/app.js
                    ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
                }
            });
        }

        /**
         * Preset common edit modal before appear
         *
         * @param {number}      id
         * @param {Object}      data
         * @param {string}      item
         * @param {string}      url
         * @param {string}      updateUrl
         * @param {string|null} modalSmall
         * @param {DOMElement}  tr
         * @param {string}      modalId
         * @param {string}      modalContentId
         *
         * @return {void}
         */
        function getCommonEdit (id, data, item, url, updateUrl, modalSmall, tr, modalId, modalContentId) {
            $(modalId + ' ' + modalContentId).hide();
            var title = 'Edit ' + item;
            $(modalId + ' .modal-title').html(title);
            $(modalId).addClass('large');

            if (modalSmall !== null) {
                $(modalId).removeClass('large');
                $(modalId).removeClass('medium');

                if (modalSmall !== true) {
                    $(modalId).addClass(modalSmall);
                }
            }

            getCommonEditData(id, data, url, updateUrl, null, '#common-edit');
        }

        /**
         * Appear common edit modal with default data
         *
         * @param {number}     id
         * @param {Object}     data
         * @param {string}     url
         * @param {string}     updateUrl
         * @param {DOMElement} tr
         * @param {string}     modalId
         *
         * @return {void}
         */
        function getCommonEditData (id, data, url, updateUrl, tr, modalId) {
            // reset to default values
            $(modalId + ' form').trigger('reset');
            $(modalId + ' form').find('.select2-hidden-accessible').trigger('change');
            $(modalId + ' span.validation-error').html('');
            $(modalId + ' .save').attr('disabled', true);
            $(modalId + ' #common-edit-footer-delete').hide();
            $(modalId + ' #common-edit-content').hide();
            $(modalId + ' .form-group').hide();
            $(modalId + ' .modal-loader').show();

            if (typeof globalVar.ladda !== 'undefined' && globalVar.ladda !== null) {
                globalVar.ladda.remove();
            }

            $(modalId).modal({
                show     : true,
                backdrop : false,
                keyboard : false
            });

            // Ajax request to load common edit form content with default data
            $.ajax({
                type    : 'GET',
                url     : url,
                data    : data,
                success : function (data) {
                    if (data.status === true) {
                        $dataObj = $(data.html);

                        if ($dataObj.length) {
                            $(modalId + ' #common-edit-content').html($dataObj);

                            // Reset toggle switch permission in modal form
                            if ($(modalId + ' .toggle-permission').get(0)) {
                                $(modalId + ' .child-permission').css('opacity', 1);
                                $(modalId + ' .child-permission').find('input').attr('disabled', false);
                                $(modalId + ' .child-permission').find("input[data-default='true']").prop('checked', true);
                            }

                            var ps = new PerfectScrollbar(modalId + ' .modal-body');
                            // pluginInit defined in js/app.js
                            pluginInit();
                        }

                        $(modalId + ' form').prop('action', updateUrl);
                        $(modalId + " input[name='_method']").val('PUT');
                        $(modalId + ' form').find('select').prop('disabled', false);
                        $(modalId + ' form').find('input').prop('readOnly', false);

                        var hide = '';
                        var show = '';

                        // Render requested a dropdown list
                        if (typeof data.info.selectlist !== 'undefined') {
                            $.each(data.info.selectlist, function (fieldName, options) {
                                var selectlist = $(modalId + " select[name='" + fieldName + "']").empty();

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
                            if ($(modalId + " *[name='" + index + "']").get(0)) {
                                if ($(modalId + " *[name='" + index + "']").is(':checkbox')) {
                                    if ($(modalId + " *[name='" + index + "']").val() == value) {
                                        $(modalId + " *[name='" + index + "']").prop('checked', true);
                                    } else {
                                        $(modalId + " *[name='" + index + "']").prop('checked', false);
                                    }
                                } else {
                                    $(modalId + " *[name='" + index + "']").not(':radio').val(value).trigger('change');
                                }

                                if ($(modalId + " *[name='" + index + "']").is(':radio')) {
                                    $(modalId + " *[name='" + index + "']").each(function (index, obj) {
                                        if ($(obj).val() == value) {
                                            $(obj).prop('checked', true);
                                        }
                                    });
                                }
                            }

                            // Freeze fields can not be editable|updatable
                            if (index === 'freeze') {
                                $.each(value, function (key, val) {
                                    if ($(modalId + " *[name='" + val + "']").is('select')) {
                                        $(modalId + " *[name='" + val + "']").prop('disabled', true);
                                    } else {
                                        $(modalId + " *[name='" + val + "']").prop('readOnly', true);
                                    }
                                });
                            }

                            // Show fields can not be hidden
                            if (index === 'show') {
                                $.each(value, function (key, val) {
                                    show += modalId + " *[name='" + val + "'],";
                                });

                                show = show.slice(0, -1);
                            }

                            // Hidden fields
                            if (index === 'hide') {
                                $.each(value, function (key, val) {
                                    $(modalId + ' .' + val + '-input').hide();
                                    hide += '.' + val + '-input' + ',';
                                });

                                hide = hide.slice(0, -1);
                            }

                            // If the response has the modal title with show page link request then render show page link
                            if (index === 'modal_title_link') {
                                if ($('#common-edit-modal-title').get(0)) {
                                    $('#common-edit-modal-title').html("<a href=''></a>");
                                    $('#common-edit-modal-title a').html(value.title);
                                    $('#common-edit-modal-title a').attr('href', value.href);
                                }
                            }

                            // If the response has the footer delete button request then show the delete button
                            if (index === 'modal_footer_delete') {
                                $('#common-edit-footer-delete').show();

                                if ($('#common-edit-footer-delete').get(0)) {
                                    $('#common-edit-footer-delete').attr('action', value.action);
                                    $('#common-edit-footer-delete').attr('data-item', value.item);
                                    $('#common-edit-footer-delete input[name="id"]').val(value.id);
                                }
                            }
                        });

                        $(modalId + ' .datepicker').each(function (index, value) {
                            $(this).datepicker('update', $(this).val());
                        });

                        $(modalId + ' .modal-loader').fadeOut(1000);
                        $(show).closest('.none').show();
                        $(show).closest('.none').parent('.none').show();
                        $(modalId + ' .form-group').not(hide).show();
                        $(modalId + ' .modal-body').animate({ scrollTop: 1 }, 10);
                        $(modalId + ' #common-edit-content').slideDown();
                        $(modalId + ' .modal-body').animate({ scrollTop: 0 }, 10);
                        $(modalId + ' .save').attr('disabled', false);
                    } else {
                        $(modalId + ' .modal-loader').fadeOut(1000);
                        $(modalId + ' .form-group').css('opacity', 0).slideDown('slow').animate({ opacity: 1 });
                        // delayModalHide defined in js/app.js
                        delayModalHide(modalId, 2);
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
