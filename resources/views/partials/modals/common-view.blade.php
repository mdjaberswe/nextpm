<div class="modal fade sub" id="common-view">
    <div class="modal-dialog">
        <div class="modal-loader">
            <div class="spinner"></div>
        </div>

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title capitalize">Save View</h4>
            </div> <!-- end modal-header -->

            {{ Form::open(['route' => ['admin.view.store', null], 'method' => 'post', 'class' => 'modal-form']) }}
                @include('partials.modals.common-view-form', ['form' => 'create'])
            {{ Form::close() }}

            <div class="modal-footer space btn-container">
                <button type="button" class="cancel btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="save btn btn-info ladda-button" data-style="expand-right">
                    <span class="ladda-label">Save</span>
                </button>
            </div> <!-- end modal-footer -->
        </div>
    </div> <!-- end modal-dialog -->
</div> <!-- end common-view-form -->

@push('scripts')
    <script>
        $(document).ready(function () {
            // Click event to open common view modal
            $(document).on('click', '.save-as-view', function (event) {
                var moduleItem = $(this).attr('data-item');

                // Save "view" with filter parameters
                if ($('#common-filter').css('display') === 'block') {
                    var selectedTr      = $('#common-filter').find('table tbody tr').not('.none');
                    var form            = $('#common-filter form');
                    var formUrl         = form.prop('action');
                    var formData        = { validationOnly: true };
                    var fields          = [];
                    var fieldConditions = [];
                    var fieldValues     = [];

                    // Get an array format of fields name, condition, and value
                    $.each(selectedTr, function (index, tr) {
                        var trCondition = $(tr).find("td[data-type='condition'] select");
                        var trValue     = '';

                        if (trCondition.val() !== 'empty' && trCondition.val() !== 'not_empty') {
                            trValue = $(tr).find("td[data-type='value'] *[name]").val();

                            if ($.isArray(trValue) && trValue.length === 0) {
                                trValue = '';
                            }

                            if (trCondition.attr('name') === 'linked_type_condition') {
                                trValue += '|' + $(tr).find("td[data-type='value'] *[name='linked_id']").val();
                            }
                        }

                        fields.push($(tr).data('field'));
                        fieldConditions.push(trCondition.val());
                        fieldValues.push(trValue);
                    });

                    formData.fields     = fields;
                    formData.conditions = fieldConditions;
                    formData.values     = fieldValues;

                    if (form.find('table').length === 0) {
                        formData = form.serialize();
                    }

                    // Ajax request for filter parameters validation before saved as view
                    $.ajax({
                        type     : 'POST',
                        url      : formUrl,
                        data     : formData,
                        dataType : 'JSON',
                        success  : function (data) {
                            if (data.status === true) {
                                $('#common-filter span.validation-error').html('');
                                openViewModal(moduleItem);
                            } else {
                                $('#common-filter span.validation-error').html('');
                                $.each(data.errors, function (index, value) {
                                    $("#common-filter span[field='" + index + "']").html(value);
                                });
                            }
                        },
                        error : function (jqXHR, textStatus, errorThrown) {
                            // ajaxErrorHandler defined in js/app.js
                            ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
                        }
                    });
                } else {
                    openViewModal(moduleItem);
                }
            });

            // Ajax request to save "Filter View"
            $('#common-view .save').on('click', function () {
                globalVar.ladda = Ladda.create(this);
                globalVar.ladda.start();
                $(this).attr('disabled', true);

                var table          = globalVar.jqueryDataTable;
                var loadFilterView = false;
                var form           = $(this).parent().parent().find('form');
                var formUrl        = form.prop('action') + '/' + form.find("input[name='module']").val();
                var formData       = {
                    view_name      : form.find("input[name='view_name']").val(),
                    visible_to     : form.find("select[name='visible_to']").val(),
                    selected_users : form.find("select[name='selected_users[]']").val(),
                    module         : form.find("input[name='module']").val()
                };

                // Save "view" with filter parameters
                if ($('#common-filter').css('display') === 'block') {
                    var fields               = [];
                    var fieldConditions      = [];
                    var fieldValues          = [];
                    var selectedTr           = $('#common-filter').find('table tbody tr').not('.none');
                    loadFilterView           = true;
                    formData.has_filter_data = true;

                    // Get an array format of fields name, condition, and value
                    $.each(selectedTr, function (index, tr) {
                        var trCondition = $(tr).find("td[data-type='condition'] select");
                        var trValue     = '';

                        if (trCondition.val() !== 'empty' && trCondition.val() !== 'not_empty') {
                            trValue = $(tr).find("td[data-type='value'] *[name]").val();

                            if ($.isArray(trValue) && trValue.length === 0) {
                                trValue = '';
                            }

                            if (trCondition.attr('name') === 'linked_type_condition') {
                                trValue += '|' + $(tr).find("td[data-type='value'] *[name='linked_id']").val();
                            }
                        }

                        fields.push($(tr).data('field'));
                        fieldConditions.push(trCondition.val());
                        fieldValues.push(trValue);
                    });

                    if ($('#common-filter form').find('table').length === 0) {
                        $('#common-filter form').find('input, select').each(function (index, ui) {
                            var fieldName       = $(ui).attr('name');
                            var fieldValue      = $(ui).val();
                            formData[fieldName] = fieldValue;
                        });
                    } else {
                        formData.fields     = fields;
                        formData.conditions = fieldConditions;
                        formData.values     = fieldValues;
                    }
                }

                $.ajax({
                    type     : 'POST',
                    url      : formUrl,
                    data     : formData,
                    dataType : 'JSON',
                    success  : function (data) {
                        if (data.status === true) {
                            $('#common-view span.validation-error').html('');
                            // delayModalHide defined in js/app.js
                            delayModalHide('#common-view', 1);
                            delayModalHide('#common-filter', 1);

                            // Reload tab data table after saving "Filter View"
                            if (typeof data.tabTable !== 'undefined' && typeof globalVar.dataTable[data.tabTable] !== 'undefined') {
                                table = globalVar.dataTable[data.tabTable];
                            }

                            // Reload data table after saving "Filter View"
                            if (typeof table !== 'undefined') {
                                table.ajax.reload(null, false);
                            }

                            // Calendar data load accordingly after saving "Filter View"
                            if ($('.calendar').get(0)) {
                                $($('.calendar').get(0)).fullCalendar('refetchEvents');
                            }

                            // Kanban board load accordingly after saving "Filter View"
                            if ($('.funnel-container').get(0) && loadFilterView) {
                                kanbanFilterViewChange(data, false);
                            }

                            // Update total no of the filter parameter
                            if (typeof data.filterCount !== 'undefined' && data.filterCount !== null) {
                                if (data.filterCount === 0) {
                                    $(".common-filter-btn[data-item='" + data.module + "'] .num-notify").addClass('none');
                                } else {
                                    $(".common-filter-btn[data-item='" + data.module + "'] .num-notify").removeClass('none');
                                }

                                $(".common-filter-btn[data-item='" + data.module + "'] .num-notify").html(data.filterCount);
                            }

                            // Breadcrumb "Filter View" dropdown list add new option group "MY VIEWS"
                            if ($(".breadcrumb-select[name='view'] optgroup[label='MY VIEWS']").length === 0) {
                                $(".breadcrumb-select[name='view'] optgroup[label='SYSTEM']").after("<optgroup label='MY VIEWS'></optgroup>");
                            }

                            var li = $(".breadcrumb-select[name='view']").closest('li');
                            li.removeClass('prestar');
                            li.find("optgroup[label='MY VIEWS']").append(data.viewHtml);
                            li.find('.view-btns').html(data.viewActionHtml);
                            $(".breadcrumb-select[name='view'] option:selected").removeAttr('selected');
                            $(".breadcrumb-select[name='view'] optgroup[label='MY VIEWS'] option:last-child").attr('selected', 'selected');
                            $(".breadcrumb-select[name='view']").trigger('change');
                            $('[data-toggle="tooltip"]').tooltip();
                        } else {
                            $('#common-view span.validation-error').html('');
                            $.each(data.errors, function (index, value) {
                                $("#common-view span[field='" + index + "']").html(value);
                            });
                        }

                        if (typeof globalVar.ladda !== 'undefined' && globalVar.ladda !== null) {
                            var statusClass = data.status ? 'success' : 'error';
                            $('#common-view .ladda-button[data-loading]').find('.ladda-label').addClass(statusClass);
                            globalVar.ladda.stop();
                            globalVar.ladda.remove();

                            setTimeout(function () {
                                $('#common-view .ladda-label').removeClass(statusClass);
                            }, 1500);
                        }

                        $('#common-view .save').attr('disabled', false);
                    },
                    error : function (jqXHR, textStatus, errorThrown) {
                        // ajaxErrorHandler defined in js/app.js
                        ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
                    }
                });
            });

            // "Filter View" dropdown list on change event
            $(document).on('change', ".breadcrumb-select[name='view']", function (e) {
                var thisSelect     = $(this);
                var thisOption     = thisSelect.find("option[value='" + $(this).val() + "']");
                var kanbanLoadType = typeof thisOption.attr('data-load-kanban') !== 'undefined' && thisOption.attr('data-load-kanban') === 'reverse';
                var table          = globalVar.jqueryDataTable;
                var formUrl        = globalVar.baseAdminUrl + '/dropdown-view/' + $(this).val();
                var formData       = { id: $(this).val(), module: $(this).data('module') };

                $.ajax({
                    type     : 'POST',
                    url      : formUrl,
                    data     : formData,
                    dataType : 'JSON',
                    success  : function (data) {
                        var li = $(".breadcrumb-select[name='view']").closest('li');

                        if (data.status === true) {
                            li.removeClass('prestar');

                            // Reload tab data table
                            if (typeof data.tabTable !== 'undefined' && typeof globalVar.dataTable[data.tabTable] !== 'undefined') {
                                table = globalVar.dataTable[data.tabTable];
                            }

                            // Reload data table
                            if (typeof table !== 'undefined') {
                                table.ajax.reload(null, false);
                            }

                            // Calendar data load according to the current view
                            if ($('.calendar').get(0)) {
                                thisSelect.closest('.row').find('.calendar').fullCalendar('refetchEvents');
                            }

                            // Kanban board load according to the current view
                            if ($('.funnel-container').get(0)) {
                                kanbanFilterViewChange(data, kanbanLoadType);
                            }

                            // Dashboard report content load according to the current view
                            ajaxAutoRefresh();

                            // Total no of the current filter parameters
                            if (typeof data.filterCount !== 'undefined' && data.filterCount !== null) {
                                if (data.filterCount === 0) {
                                    $(".common-filter-btn[data-item='" + data.module + "'] .num-notify").addClass('none');
                                } else {
                                    $(".common-filter-btn[data-item='" + data.module + "'] .num-notify").removeClass('none');
                                }

                                $(".common-filter-btn[data-item='" + data.module + "'] .num-notify").html(data.filterCount);
                            }

                            if (typeof data.realtime !== 'undefined') {
                                $(data.realtime).each(function (index, value) {
                                    $("*[data-realtime='" + value[0] + "']").html(value[1]);
                                });
                            }

                            li.find('.view-btns').html(data.viewActionHtml);
                            $('[data-toggle="tooltip"]').tooltip();
                            nicescrollResize('html');
                        } else {
                            if (typeof data.viewId !== 'undefined' && data.viewId !== null) {
                                $(".breadcrumb-select[name='view']").val(data.viewId);
                                $(".breadcrumb-select[name='view']").trigger('change');
                                $(".breadcrumb-select[name='view']").closest('.breadcrumb').find('.select2-hidden-accessible').trigger('change');
                            }

                            $.notify({ message: 'Something went wrong.' }, globalVar.dangerNotify);
                        }
                    },
                    error : function (jqXHR, textStatus, errorThrown) {
                        // ajaxErrorHandler defined in js/app.js
                        ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
                    }
                });
            });
        });

        /**
         * Preset data and open common view modal
         *
         * @param {string} moduleItem
         *
         * @return {void}
         */
        function openViewModal (moduleItem) {
            $('#common-view form').trigger('reset');
            $('#common-view form').find('.select2-hidden-accessible').trigger('change');
            $('#common-view form').find('.white-select-type-single').select2('destroy').select2({ containerCssClass: 'white-container', dropdownCssClass: 'white-dropdown' });
            $('#common-view form').find('.white-select-type-single-b').select2('destroy').select2({ minimumResultsForSearch: -1, containerCssClass: 'white-container', dropdownCssClass: 'white-dropdown' });
            $('#common-view .none').hide();
            $('#common-view .save').attr('disabled', false);
            $('#common-view span.validation-error').html('');
            $("#common-view input[name='module']").val(moduleItem);

            if (typeof globalVar.ladda !== 'undefined' && globalVar.ladda !== null) {
                globalVar.ladda.remove();
            }

            $('#common-view').modal({
                show     : true,
                backdrop : false,
                keyboard : false
            });
        }
    </script>
@endpush
