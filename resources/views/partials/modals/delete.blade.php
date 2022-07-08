<div class="modal fade top" id="confirm-delete">
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
</div> <!-- end confirm-delete -->

@push('scripts')
    <script>
        $(document).ready(function () {
            // Reset alert modal and confirm before delete action executes
            $(document).on('click', '.delete', function (event) {
                event.preventDefault();

                if (typeof $(this).attr('data-item') === 'undefined') {
                    return false;
                }

                if ($(this).hasClass('disabled')) {
                    $.notify({ message: 'This {!! isset($page['item']) ? strtolower($page['item']) : null !!} is used in other modules.' }, globalVar.dangerNotify);
                    return false;
                }

                var formUrl       = $(this).parent('form').prop('action');
                var formData      = $(this).parent('form').serialize();
                var itemName      = typeof $(this).attr('data-item') === 'undefined' ? '{!! $page['item'] or null !!}' : $(this).attr('data-item');
                var itemLowerCase = itemName.toLowerCase();
                var parentItem    = typeof $(this).attr('data-parentitem') !== 'undefined' ? $(this).attr('data-parentitem') : null;
                var message       = '';

                if (typeof $(this).attr('data-associated') === 'undefined' || $(this).attr('data-associated') === 'true') {
                    message += 'This ' + itemLowerCase + ' will be removed along with all associated data.<br>';
                }

                message += 'Are you sure you want to delete this ' + itemLowerCase + '?';
                var tableExist = typeof globalVar.jqueryDataTable !== 'undefined';

                if (parentItem != null) {
                    message = 'This ' + itemLowerCase + ' will be removed from the ' + parentItem + '.<br>Are you sure you want to remove this ' + itemLowerCase + '?';
                }

                var title = typeof $(this).attr('modal-title') === 'undefined' ? 'CONFIRM' : $(this).attr('modal-title');
                title += typeof $(this).attr('modal-sub-title') === 'undefined' ? '' : " <span class='shadow bracket'>" + $(this).attr('modal-sub-title') + '</span>';
                $('#confirm-delete .modal-title').html(title);

                confirmDelete(formUrl, formData, tableExist, itemName, message, parentItem);
            });
        });

        /**
         * Confirm alert before deleting an item.
         *
         * @param {string}  formUrl
         * @param {Object}  formData
         * @param {boolean} tableExist
         * @param {string}  itemName
         * @param {string}  message
         * @param {string}  parentItem
         *
         * @return {void}
         */
        function confirmDelete (formUrl, formData, tableExist, itemName, message, parentItem) {
            $('#confirm-delete').modal({
                show     : true,
                backdrop : false,
                keyboard : false
            });

            $('#confirm-delete .message').html(message);

            $('#confirm-delete .yes').on('click', function () {
                $('#confirm-delete .message').html("<img src='{!! asset('plugins/datatable/images/preloader.gif') !!}'>");

                // Ajax request to delete data
                $.ajax({
                    type     : 'DELETE',
                    url      : formUrl,
                    data     : formData,
                    dataType : 'JSON',
                    success  : function (data) {
                        if (data.status === true) {
                            var actionTaken = parentItem != null ? 'removed' : 'deleted';
                            $('#confirm-delete .message').html("<span class='fa fa-times-circle color-danger'></span> <span class='capitalize'>" + itemName + '</span> has been ' + actionTaken + '.');
                            delayModalHide('#confirm-delete', 1);

                            // Reload data table after deleting data
                            if (typeof data.tabTable !== 'undefined' && typeof globalVar.dataTable[data.tabTable] !== 'undefined') {
                                globalVar.dataTable[data.tabTable].ajax.reload(null, false);
                            } else if (tableExist) {
                                globalVar.jqueryDataTable.ajax.reload(null, false);
                            }

                            if (typeof data.modalDatatable !== 'undefined' && typeof globalVar.jqueryModalDataTable !== 'undefined') {
                                globalVar.jqueryModalDataTable.ajax.reload(null, false);
                            }

                            @if (isset($page['modal_footer_delete']) && $page['modal_footer_delete'] == true)
                                // delayModalHide defined in js/app.js
                                delayModalHide('#edit-form', 1);
                                delayModalHide('#common-edit', 1);
                            @endif

                            // Calendar data load after deleting data
                            if ($('.calendar').get(0)) {
                                if (data.eventId !== null) {
                                    $('.calendar').fullCalendar('removeEvents', data.eventId);
                                }
                            }

                            // Kanban board load after deleting data
                            if ($('.funnel-container').get(0)) {
                                $.each(data.kanban, function (index, cardId) {
                                    $('.funnel-stage #' + cardId).remove();
                                });

                                // kanbanCountResponse defined in js/app.js
                                kanbanCountResponse(data);
                            }

                            // Breadcrumb views dropdown list load accordingly after deleting data
                            if (typeof data.defaultViewId !== 'undefined' && data.defaultViewId !== null) {
                                $(".breadcrumb-select[name='view'] option[value='" + data.deletedViewId + "']").remove();
                                $(".breadcrumb-select[name='view']").val(data.defaultViewId);
                                $(".breadcrumb-select[name='view']").trigger('change');
                                $(".breadcrumb-select[name='view']").closest('li').find('.view-btns').html('');

                                if (data.filterCount === 0) {
                                    $(".common-filter-btn[data-item='" + data.module + "'] .num-notify").addClass('none');
                                } else {
                                    $(".common-filter-btn[data-item='" + data.module + "'] .num-notify").removeClass('none');
                                }

                                $(".common-filter-btn[data-item='" + data.module + "'] .num-notify").html(data.filterCount);

                                // select2PluginInit defined in js/app.js
                                select2PluginInit();
                            }

                            // Timeline notes load accordingly after deleting data
                            if (typeof data.timelineInfoId !== 'undefined' && data.timelineInfoId !== null) {
                                $(".timeline-info[data-id='" + data.timelineInfoId + "']").fadeOut(750);

                                setTimeout(function () {
                                    if ($(".timeline-info[data-id='" + data.timelineInfoId + "']").hasClass('top')) {
                                        $(".timeline-info[data-id='" + data.timelineInfoId + "']").next('.timeline-info').addClass('top');
                                    }

                                    if (typeof data.timelineInfoCount !== 'undefined' && data.timelineInfoCount === 0) {
                                        $($(".timeline-info[data-id='" + data.timelineInfoId + "']").closest('.timeline')).html('');
                                    }

                                    $(".timeline-info[data-id='" + data.timelineInfoId + "']").remove();
                                }, 700);
                            }

                            // Render HTML accordingly after deleting data
                            if (typeof data.realtime !== 'undefined') {
                                $.each(data.realtime, function (index, value) {
                                    $("*[data-realtime='" + index + "']").html(value);
                                });
                            }

                            if (typeof data.realReplace !== 'undefined') {
                                $(data.realReplace).each(function (index, value) {
                                    $(value[0]).replaceWith(value[1]);
                                });
                            }

                            if (typeof data.innerHtml !== 'undefined') {
                                $(data.innerHtml).each(function (index, value) {
                                    $(value[0]).html(value[1]);
                                });
                            }

                            if (typeof data.history !== 'undefined' && data.history !== null && data.history !== '') {
                                $('.timeline-info:not(.start):not(.end)').remove();
                                $(data.history).insertAfter('.timeline-info.start');
                            }

                            if (typeof data.updatedBy !== 'undefined' && data.updatedBy !== null) {
                                $("*[data-realtime='updated_by']").html(data.updatedBy);
                            }

                            if (typeof data.lastModified !== 'undefined' && data.lastModified !== null) {
                                $("*[data-realtime='last_modified']").html(data.lastModified);
                            }

                            if (typeof data.redirect !== 'undefined' && data.redirect !== null) {
                                window.location = data.redirect;
                            }
                        } else {
                            if (typeof data.errorMsg !== 'undefined') {
                                $('#confirm-delete .message').html("<span class='fa fa-exclamation-triangle color-danger'></span> " + data.errorMsg);
                                delayModalHide('#confirm-delete', 2);
                            } else {
                                $('#confirm-delete .message').html("<span class='fa fa-exclamation-circle color-danger'></span> Operation failed. Please try again.");
                                delayModalHide('#confirm-delete', 1);
                            }
                        }
                    },
                    error : function (jqXHR, textStatus, errorThrown) {
                        // ajaxErrorHandler defined in js/app.js
                        ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
                    }
                });

                $(this).parent().find('.btn').off('click');
            });

            // Retreat execution of deleting data
            $('#confirm-delete .no').on('click', function () {
                $('#confirm-delete').modal('hide');
                $(this).parent().find('.btn').off('click');
            });
        }
    </script>
@endpush
