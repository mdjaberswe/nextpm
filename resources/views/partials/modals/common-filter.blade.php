<div class="modal fade large" id="common-filter">
    <div class="modal-dialog">
        <div class="modal-loader">
            <div class="spinner"></div>
        </div>

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title capitalize">Filter Data</h4>
            </div> <!-- end modal-header -->

            {{ Form::open(['route' => ['admin.filter.form.post', null], 'method' => 'post', 'class' => 'modal-form']) }}
                <div id="common-filter-content" class="full"></div>
            {{ Form::close() }}

            <div class="modal-footer space btn-container">
                <button type="button" class="cancel btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="save-as-view btn btn-info" data-item="">Save as View</button>
                <button type="button" class="submit btn btn-info ladda-button" data-style="expand-right">
                    <span class="ladda-label">Submit</span>
                </button>
            </div> <!-- end modal-footer -->
        </div>
    </div> <!-- end modal-dialog -->
</div> <!-- end common-filter-form -->

@push('scripts')
    <script>
        $(document).ready(function () {
            // Click event: Reset and open a common filter modal
            $(document).on('click', '.common-filter-btn', function (event) {
                var data      = { html: true };
                var url       = $(this).attr('data-url');
                var updateUrl = $(this).attr('data-posturl');
                var title     = typeof $(this).attr('modal-title') === 'undefined' ? 'Filter ' + $(this).attr('data-item') + ' Data' : $(this).attr('modal-title');
                var modalSize = typeof $(this).attr('data-modalsize') !== 'undefined' ? $(this).attr('data-modalsize') : null;

                $('#common-filter #common-filter-content').hide();
                $('#common-filter .modal-title').html(title);
                $('#common-filter .save-as-view').attr('data-item', $(this).attr('data-item'));
                $('#common-filter').removeClass('medium');
                $('#common-filter').removeClass('tiny');
                $('#common-filter').removeClass('sub');

                if (modalSize === null) {
                    if (!$('#common-filter').hasClass('large')) {
                        $('#common-filter').addClass('large');
                    }
                } else {
                    $('#common-filter').removeClass('large');
                    $('#common-filter').addClass(modalSize);
                }

                getCommonFilterData(data, url, updateUrl, '#common-filter');
            });

            // Ajax request for updating the filter parameter and get data accordingly by modalCommonFilterUpdate
            $('#common-filter .submit').on('click', function () {
                globalVar.ladda = Ladda.create(this);
                globalVar.ladda.start();
                $(this).attr('disabled', true);
                $(this).closest('.modal-footer').find('.save-as-view').attr('disabled', true);
                var form = $(this).parent().parent().find('form');
                modalCommonFilterUpdate(form, '#common-filter');
            });

            // Add a new filter parameter
            $(document).on('click', '.add-filter-field', function () {
                var modalBody       = $(this).closest('.modal-body');
                var filterTable     = modalBody.find('table');
                var filterFields    = $(this).closest('.form-group').find('select');
                var filterFieldsVal = filterFields.val();

                if (filterFieldsVal.length) {
                    $.each(filterFieldsVal, function (index, field) {
                        if (filterTable.find("tr[data-field='" + field + "']").hasClass('none')) {
                            var defaultConditionVal = filterTable.find("tr[data-field='" + field + "'] *[data-type='condition'] select option:first").val();
                            filterTable.find("tr[data-field='" + field + "'] *[data-type='condition'] select").val(defaultConditionVal);
                            filterTable.find("tr[data-field='" + field + "'] *[data-type='value'] input").val('');
                            filterTable.find("tr[data-field='" + field + "'] *[data-type='value'] select").val('');
                            filterTable.find("tr[data-field='" + field + "']").find('.select2-hidden-accessible').trigger('change');
                            filterTable.find("tr[data-field='" + field + "']").removeClass('none');
                        }
                    });

                    filterFields.val('');
                    $(this).closest('.form-group').find('.select2-hidden-accessible').trigger('change');
                }
            });

            // Remove the filter parameter
            $(document).on('click', '.remove-filter', function () {
                $(this).closest('tr').addClass('none');
            });

            // Render filter parameter value according to condition on change event
            $(document).on('change', "#common-filter table td[data-type='condition'] select", function () {
                var tr = $(this).closest('tr');
                var conditionVal = $(this).val();

                if (conditionVal === 'empty' || conditionVal === 'not_empty') {
                    tr.find("td[data-type='value']").css('opacity', 0);
                    tr.find("td[data-type='value'] input").attr('readOnly', true);
                    tr.find("td[data-type='value'] select").attr('disabled', true);
                } else {
                    tr.find("td[data-type='value']").css('opacity', 1);
                    tr.find("td[data-type='value'] input").attr('readOnly', false);
                    tr.find("td[data-type='value'] select").attr('disabled', false);
                }
            });
        });

        /**
         * Ajax request to update the filter parameter
         *
         * @param {DOMElement} form
         * @param {string}     modalId
         *
         * @return {void}
         */
        function modalCommonFilterUpdate (form, modalId) {
            var table           = globalVar.jqueryDataTable;
            var formUrl         = form.prop('action');
            var selectedTr      = form.find('table tbody tr').not('.none');
            var fields          = [];
            var fieldConditions = [];
            var fieldValues     = [];

            // Get array format of fields name, condition, and value
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

            var formData = { fields: fields, conditions: fieldConditions, values: fieldValues };

            if (form.find('table').length === 0) {
                formData = form.serialize();
            }

            $.ajax({
                type     : 'POST',
                url      : formUrl,
                data     : formData,
                dataType : 'JSON',
                success  : function (data) {
                    if (data.status === true) {
                        $(modalId + ' span.validation-error').html('');
                        delayModalHide(modalId, 1);

                        // Reload tab data table after updating the filter parameter
                        if (typeof data.tabTable !== 'undefined' && typeof globalVar.dataTable[data.tabTable] !== 'undefined') {
                            table = globalVar.dataTable[data.tabTable];
                        }

                        // Reload data table after updating the filter parameter
                        if (typeof table !== 'undefined') {
                            table.ajax.reload(null, false);
                        }

                        // Calendar data load accordingly after updating the filter parameter
                        if ($('.calendar').get(0)) {
                            $($('.calendar').get(0)).fullCalendar('refetchEvents');
                        }

                        // Kanban board load accordingly after updating the filter parameter
                        if ($('.funnel-container').get(0)) {
                            kanbanFilterViewChange(data, false);
                        }

                        // Dashboard report content loads accordingly after updating the filter parameter
                        ajaxAutoRefresh();

                        // Update total no of the filter parameter
                        if (typeof data.filterCount !== 'undefined' && data.filterCount !== null) {
                            if (data.filterCount === 0) {
                                $(".common-filter-btn[data-item='" + data.module + "'] .num-notify").addClass('none');
                            } else {
                                $(".common-filter-btn[data-item='" + data.module + "'] .num-notify").removeClass('none');
                            }

                            $(".common-filter-btn[data-item='" + data.module + "'] .num-notify").html(data.filterCount);
                        }

                        // Render custom "Filter View" name if it updated parameters only but not saved as a view
                        if (typeof data.customViewName !== 'undefined' && data.customViewName === true) {
                            var li = $(".breadcrumb-select[name='view']").closest('li');
                            li.addClass('prestar');
                            li.find('.breadcrumb-action').hide();

                            if (li.find('a.save-as-view').length === 0) {
                                li.find('.view-btns').append("<a class='bread-link save-as-view' data-item='" + data.module + "'>Save as View</a>");
                            }
                        }

                        // Render real-time HTML changes after updating filter parameters
                        if (typeof data.realtime !== 'undefined') {
                            $(data.realtime).each(function (index, value) {
                                $("*[data-realtime='" + value[0] + "']").html(value[1]);
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

                    $(modalId + ' .submit').attr('disabled', false);
                    $(modalId + ' .save-as-view').attr('disabled', false);
                },
                error : function (jqXHR, textStatus, errorThrown) {
                    // ajaxErrorHandler defined in js/app.js
                    ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
                }
            });
        }

        /**
         * Appear filter modal with all filter parameter data and default set in form
         *
         * @param {Object} data
         * @param {string} url
         * @param {string} updateUrl
         * @param {string} modalId
         *
         * @return {void}
         */
        function getCommonFilterData (data, url, updateUrl, modalId) {
            // reset to default values and appear common filter modal
            $(modalId + ' form').trigger('reset');
            $(modalId + ' form').find('.select2-hidden-accessible').trigger('change');
            $(modalId + ' span.validation-error').html('');
            $(modalId + ' .submit').attr('disabled', true);
            $(modalId + ' .save-as-view').attr('disabled', true);
            $(modalId + ' #common-filter-content').hide();
            $(modalId + ' .modal-loader').show();

            if (typeof globalVar.ladda !== 'undefined' && globalVar.ladda !== null) {
                globalVar.ladda.remove();
            }

            $(modalId).modal({
                show     : true,
                backdrop : false,
                keyboard : false
            });

            $.ajax({
                type    : 'GET',
                url     : url,
                data    : data,
                success : function (data) {
                    if (data.status === true) {
                        var $dataObj = $(data.html);

                        // Load filter form content
                        if ($dataObj.length) {
                            $(modalId + ' #common-filter-content').html($dataObj);
                        }

                        // pluginInit and perfectScrollbarInit defined in js/app.js
                        pluginInit();
                        perfectScrollbarInit();

                        $(modalId + ' form').prop('action', updateUrl);
                        $(modalId + ' form').trigger('reset');
                        $(modalId + " form *[data-type='value'] input").val('');
                        $(modalId + " form *[data-type='value'] select").val('');
                        $(modalId + ' form').find('.select2-hidden-accessible').trigger('change');

                        // Load filter parameters and values
                        $.each(data.info, function (index, value) {
                            if ($(modalId + " *[name='" + index + "[]']").get(0)) {
                                index = index + '[]';
                            }

                            if ($(modalId + " *[name='" + index + "']").get(0)) {
                                if ($(modalId + " *[name='" + index + "']").is(':checkbox')) {
                                    if ($(modalId + " *[name='" + index + "']").val() === value) {
                                        $(modalId + " *[name='" + index + "']").prop('checked', true);
                                    } else {
                                        $(modalId + " *[name='" + index + "']").prop('checked', false);
                                    }
                                } else {
                                    if ($(modalId + " *[name='" + index + "']").hasClass('white-select-type-multiple-tags')) {
                                        var tagSelect = $(modalId + " select[name='" + index + "']").empty();

                                        $(value).each(function (index, optVal) {
                                            $('<option/>', {
                                                value : optVal,
                                                text  : optVal
                                            }).appendTo(tagSelect);
                                        });
                                    }

                                    if (typeof $(modalId + " *[name='" + index + "']").closest('.form-group').attr('data-for') !== 'undefined') {
                                        setTimeout(function () {
                                            $(modalId + " *[name='" + index + "']").not(':radio').val(value).trigger('change');
                                        }, 750);
                                    } else {
                                        $(modalId + " *[name='" + index + "']").not(':radio').val(value).trigger('change');
                                    }
                                }

                                if ($(modalId + " *[name='" + index + "']").is(':radio')) {
                                    $(modalId + " *[name='" + index + "']").each(function (index, obj) {
                                        if ($(obj).val() === value) {
                                            $(obj).prop('checked', true);
                                        }
                                    });
                                }
                            }
                        });

                        $(modalId + ' .datepicker').each(function (index, value) {
                            $(this).datepicker('update', $(this).val());
                        });

                        $(modalId + ' .modal-loader').fadeOut(1000);
                        $(modalId + ' .modal-body').animate({ scrollTop: 1 }, 10);
                        $(modalId + ' #common-filter-content').slideDown();
                        $(modalId + ' .modal-body').animate({ scrollTop: 0 }, 10);
                        $(modalId + ' .submit').attr('disabled', false);
                        $(modalId + ' .save-as-view').attr('disabled', false);
                    } else {
                        $(modalId + ' .modal-loader').fadeOut(1000);
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
