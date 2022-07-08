/**
 * NextPM - Open Source Project Management Script
 * Copyright (c) Muhammad Jaber. All Rights Reserved
 *
 * Email: mdjaber.swe@gmail.com
 *
 * LICENSE
 * --------
 * Licensed under the Apache License v2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

$(document).ready(function () {
    var tabClickTime = new Date();

    // Tab content load when clicking a tab link
    $('.page-content').on('click', 'a[tabkey]', function () {
        var newTabClickTime = new Date();
        var diffTabTime = (newTabClickTime.getTime() - tabClickTime.getTime()) / 1000;

        if (diffTabTime > 1) {
            loadTabContent($(this));
            tabClickTime = newTabClickTime;
        }
    });

    // Get content from windows history without page loading
    window.onpopstate = function (e) {
        if (e.state) {
            var item = $('#item-tab-details').attr('item').toLowerCase();

            if (e.state.html == null) {
                location.reload();
            } else {
                $('#item-tab li a').removeClass('active');
                $("#item-tab li a[tabkey='" + e.state.tabkey + "']").addClass('active');
                $('#item-tab-content').html(e.state.html);
                resetTabContent(item);
            }
        }
    };

    // Tab data table show|hide column click event
    $('#item-tab-details').on('click.dt', '.show-hide', function (event) {
        // globalVar defined in partials/footer.blade.php
        var table   = globalVar.jqueryDataTable;
        var tableId = '#' + $(this).attr('aria-controls');

        if (tableId !== '#datatable' && typeof globalVar.dataTable[tableId] !== 'undefined') {
            table = globalVar.dataTable[tableId];
        }

        var column = table.column($(this).index());
        column.visible(!column.visible());

        if (column.visible()) {
            $(this).removeClass('unseen');
        } else {
            $(this).addClass('unseen');
        }
    });

    // Tab overview toggle show|hide details information
    $('#item-tab-details').on('click', '.show-hide-details a', function (event) {
        var detailsContent = $(this).closest('.show-hide-details').next('.details-content');
        var hideDetails    = 0;

        if (detailsContent.css('display') === 'none') {
            detailsContent.slideDown();
            $(this).html("HIDE DETAILS <i class='fa fa-angle-up'></i>");
        } else {
            hideDetails = 1;
            detailsContent.slideUp();
            $(this).html("SHOW DETAILS <i class='fa fa-angle-down'></i>");
        }

        var data = { hide_details: hideDetails };

        $.ajax({
            type : 'GET',
            data : data,
            url  : $(this).attr('url')
        });

        if (detailsContent.hasClass('none')) {
            var addHeight = 1;

            $(detailsContent).find('.content-section').each(function (index, obj) {
                addHeight += $(obj).height();
            });

            var newHeight = $('#item-tab-details').height() + addHeight;
            $('#item-tab-details').css('height', newHeight + 'px');
            nicescrollResize('html');
            $('#item-tab-details').css('height', 'auto');
            detailsContent.removeClass('none');
        } else {
            nicescrollResize('html');
        }
    });

    // Ajax request to get all allowed users list with given permissions of the specified resource
    $('#item-tab-details').on('click', '.private-users', function (event) {
        var modalTitle = $(this).attr('modal-title');
        var append     = $("#access-form select[name='staffs[]']");
        var type       = $(this).attr('type');
        var id         = $(this).attr('editid');
        var data       = { id: id, type: type };
        var url        = globalVar.baseAdminUrl + '/allowed-user-data/' + type + '/' + id;
        var saveUrl    = globalVar.baseAdminUrl + '/allowed-user/' + type + '/' + id;

        if (typeof globalVar.ladda !== 'undefined' && globalVar.ladda !== null) {
            globalVar.ladda.remove();
        }

        // Reset modal form fields
        $('#access-form form').trigger('reset');
        $('#access-form').find('.select2-hidden-accessible').trigger('change');
        $('#access-form span.validation-error').html('');
        $('#access-form .form-group').not('.always-show').hide();
        $('#access-form .modal-title .capitalize').html(type);
        $('#access-form .modal-title .shadow').html(modalTitle);
        $('#access-form .modal-body').animate({ scrollTop: 1 });
        $('#access-form .modal-loader').show();
        $('#access-form .save').attr('disabled', false);
        $('#access-form').modal();

        $.ajax({
            type    : 'GET',
            url     : url,
            data    : data,
            success : function (data) {
                if (data.status === true) {
                    if ($(data.list).length) {
                        var appendlist = append.empty();

                        // Render users dropdown list
                        $.each(data.list, function (id, name) {
                            if (Number.isInteger(parseInt(id, 10))) {
                                $('<option/>', { value: id, text: name }).appendTo(appendlist);
                            } else {
                                $('<optgroup/>', { label: id }).appendTo(appendlist);
                                var optgroupList = $(append.find("optgroup[label='" + id + "']")).empty();

                                $.each(name, function (key, display) {
                                    $('<option/>', { value: key, text: display }).appendTo(optgroupList);
                                });
                            }
                        });
                    } else {
                        append.html($("#access-form select[data-stafflist='true']").html());
                        append.val(null);
                        $('#access-form').find('.select2-hidden-accessible').trigger('change');
                    }

                    $('#access-form').find('tbody').html(data.html);
                    $('[data-toggle="tooltip"]').tooltip();
                    $('#access-form form').prop('action', saveUrl);
                    $("#access-form input[name='id']").val(id);
                    $("#access-form input[name='type']").val(type);
                    $('#access-form .modal-loader').fadeOut(1000);
                    $('#access-form .form-group').not('.always-show').css('opacity', 0).slideDown('slow').animate({ opacity: 1 });
                } else {
                    $('#access-form .modal-loader').fadeOut(1000);
                    $('#access-form .form-group').not('.always-show').css('opacity', 0).slideDown('slow').animate({ opacity: 1 });
                    // delayModalHide function defined in js/app.js
                    delayModalHide('#access-form', 2);
                }
            },
            error : function (jqXHR, textStatus, errorThrown) {
                // ajaxErrorHandler function defined in js/app.js
                ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'notify', false, 2500);
            }
        });
    });

    // Click event to show single field update form with the current field value
    $('#item-tab-details').on('click', '.editable .edit', function (event) {
        var field      = $(this).closest('.field');
        var editSingle = field.find('.edit-single');
        editValSync(field, editSingle, false);
    });

    // Update single field value if press enter
    $('#item-tab-details').on('keypress', '.edit-single input, .edit-single textarea', function (event) {
        var charCode = event.which;

        if (charCode === 13) {
            $(this).closest('.edit-single').find('.save-single').click();
        }
    });

    // Ajax request to update single field value
    $('#item-tab-details').on('click', '.edit-single a', function (event) {
        var editSingle = $(this).closest('.edit-single');
        var value      = editSingle.prev('.value');
        var dataValue  = value.attr('data-value');

        if ($(this).hasClass('save-single')) {
            // Get post-action URL and formatted field value
            var actionUrl       = editSingle.attr('data-action');
            var formData        = editSingle.find('select, textarea, input').serialize();
            var formDataArray   = editSingle.find('select, textarea, input').serializeArray();
            var dataValueFormat = formDataArray.length > 1 ? formDataArray[0].value + '|' + formDataArray[1].value : formDataArray[0].value;
            var realtime        = formDataArray[0].name;
            var optionHtml      = editSingle.find('select option:selected').text();

            if (value.attr('data-array') !== 'undefined' && value.attr('data-array') === 'true') {
                var valueArray = [];
                $.each(formDataArray, function (index, field) {
                    valueArray.push(field.value);
                });
                dataValueFormat = valueArray.join('|');
            }

            $.ajax({
                type     : 'POST',
                url      : actionUrl,
                data     : formData,
                dataType : 'JSON',
                success  : function (data) {
                    if (data.status === true) {
                        // Render and update the specified resource timeline history
                        if (typeof data.history !== 'undefined' && data.history !== null && data.history !== '') {
                            $('.timeline-info:not(.start):not(.end)').remove();
                            $(data.history).insertAfter('.timeline-info.start');
                        }

                        // Single field updated new value show and form reset accordingly
                        if (typeof dataValue !== 'undefined') {
                            value.attr('data-value', dataValueFormat);
                            $("*[data-realtime='" + realtime + "']").attr('data-value', dataValueFormat);

                            if (optionHtml !== '' && data.html === null) {
                                optionHtml = dataValueFormat !== '' ? optionHtml : '';
                                value.html(optionHtml);
                                $("*[data-realtime='" + realtime + "']").html(optionHtml);
                            }

                            if (data.html !== null) {
                                value.html(data.html);
                                $("*[data-realtime='" + realtime + "']").html(data.html);

                                if (dataValueFormat.indexOf("'") > -1 || dataValueFormat.indexOf('"') > -1) {
                                    $("*[data-realtime='" + realtime + "']").attr('data-value', data.html);

                                    if (formDataArray.length > 1) {
                                        dataValueFormat = dataValueFormat.replaceAll("'", '');
                                        dataValueFormat = dataValueFormat.replaceAll('"', '');
                                        value.attr('data-value', dataValueFormat);
                                    }
                                }

                                if (typeof value.attr('data-array') !== 'undefined' && value.attr('data-array') === 'true') {
                                    $("*[data-realtime='" + realtime.replace('[]', '') + "']").html(data.html);
                                }
                            }

                            if (typeof $("*[data-realtime='" + realtime + "']").data('datepicker') !== 'undefined') {
                                $("*[data-realtime='" + realtime + "']").closest('.field').find('.datepicker').datepicker('update', dataValueFormat);
                            }
                        } else {
                            value.html(dataValueFormat);
                            $("*[data-realtime='" + realtime + "']").html(dataValueFormat);
                        }

                        // Render last who is updated by
                        if (typeof data.updatedBy !== 'undefined' && data.updatedBy !== null) {
                            $("*[data-realtime='updated_by']").html(data.updatedBy);
                        }

                        // Render last modified time
                        if (typeof data.lastModified !== 'undefined' && data.lastModified !== null) {
                            $("*[data-realtime='last_modified']").html(data.lastModified);
                        }

                        // Render updated modal title if defined
                        if (typeof data.modalTitle !== 'undefined' && data.modalTitle !== null) {
                            $('*[modal-title]').attr('modal-title', data.modalTitle);
                        }

                        // Realtime effect on HTML after updating field
                        if (typeof data.realtime !== 'undefined') {
                            $(data.realtime).each(function (index, value) {
                                $("*[data-realtime='" + value[0] + "']").html(value[1]);
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

                                if ($(value[0]).is('select') && value[2] === true) {
                                    $(value[0]).closest('.field').find('.value').attr('data-value', '');
                                    $(value[0]).closest('.field').find('.value').html('');
                                }
                            });
                        }

                        // Reload tab data table
                        if (typeof data.tabTable !== 'undefined' && typeof globalVar.dataTable[data.tabTable] !== 'undefined') {
                            globalVar.dataTable[data.tabTable].columns.adjust().page('first').draw('page');
                        }

                        $('#item-tab-details').find('.editable').removeClass('edit-false');

                        if (typeof data.editFalse !== 'undefined') {
                            $(data.editFalse).each(function (index, value) {
                                $($("*[name='" + value + "']").closest('.editable')).addClass('edit-false');
                            });
                        }

                        $.notify({ message: 'Update was successful' }, globalVar.successNotify);
                    } else {
                        $.each(data.errors, function (index, value) {
                            $.notify({ message: value }, globalVar.dangerNotify);
                        });

                        if (data.errors == null) {
                            $.notify({ message: 'Something went wrong.' }, globalVar.dangerNotify);
                        }
                    }

                    // Reset overview tab content after updating field value
                    resetOverview(editSingle, data.status, false);
                },
                error : function (jqXHR, textStatus, errorThrown) {
                    // ajaxErrorHandler function defined in js/app.js
                    ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'notify', false, 2500);
                }
            });
        } else if (typeof $(this).attr('data-resetval') !== 'undefined') {
            resetOverview(editSingle, true, true);
        } else {
            resetOverview(editSingle, true, false);
        }
    });
});

/**
 * Load tab HTML content by ajax request.
 *
 * @param {Object} thisTabLink
 *
 * @return {void}
 */
function loadTabContent (thisTabLink) {
    if (!thisTabLink.hasClass('active')) {
        // NProgress defined in plugins/nprogress
        NProgress.start();
        var item       = $('#item-tab-details').attr('item').toLowerCase();
        var itemId     = $('#item-tab-details').attr('itemid');
        var infoType   = thisTabLink.attr('tabkey');
        var thisTab    = thisTabLink;
        var tabUrl     = $('#item-tab-details').attr('taburl');
        var ItemIdUrl  = itemId === '' ? itemId : '/' + itemId;
        var ajaxUrl    = globalVar.baseAdminUrl + '/' + tabUrl + ItemIdUrl + '/' + infoType;
        var ajaxData   = { id: itemId, type: infoType };
        var lastUrlArg = window.location.href.split('/').last();
        var pushState  = (lastUrlArg === itemId) ? itemId + '/' + infoType : infoType;

        if (thisTabLink.hasClass('tab-link')) {
            thisTab = $('#item-tab').find("a[tabkey='" + infoType + "']");

            if (thisTab.length === 0 && typeof thisTabLink.attr('parent-tabkey') !== 'undefined') {
                thisTab = $('#item-tab').find("a[tabkey='" + thisTabLink.attr('parent-tabkey') + "']");
            }
        }

        // Ajax request to get tab content
        $.ajax({
            type    : 'POST',
            url     : ajaxUrl,
            data    : ajaxData,
            success : function (data) {
                var $dataObj = $(data);

                if ($dataObj.length) {
                    // Keep tab content in window history
                    if (itemId !== '' && data.length < 320000) {
                        window.history.pushState({ html: data, tabkey: infoType }, '', pushState);
                    } else {
                        window.history.pushState({ html: null, tabkey: infoType }, '', pushState);
                    }

                    $('#item-tab li a').removeClass('active');
                    thisTab.addClass('active');
                    $('#item-tab-content').html($dataObj);
                    setTimeout(function () { NProgress.done(); $('.fade').removeClass('out'); }, 500);
                    resetTabContent(item);
                }
            },
            error : function (jqXHR, textStatus, errorThrown) {
                // ajaxErrorHandler function defined in js/app.js
                ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
            }
        });
    }
}

/**
 * Reset tab content.
 *
 * @param {string} item
 *
 * @return {void}
 */
function resetTabContent (item) {
    nicescrollResize('html');
    $('[data-toggle="tooltip"]').tooltip();
    $('html, body').animate({ scrollTop: 0 }, 'fast');
    $('.fn-gantt-hint').remove();

    // All defined in js/app.js
    counterUpInit('#item-tab-details');
    heightAdjustment();
    initChart();
    atWhoInit();
    pluginInit();
    sortableInit();
    calendarInit();
    perfectScrollbarInit();
    tabDatatableInit(item);

    $('[data-toggle="tooltip"]').tooltip();
    nicescrollResize('html');
}

/**
 * Reset overview tab.
 *
 * @param {Object} editSingle
 * @param {bool}   reset
 * @param {bool}   valueReset
 *
 * @return {void}
 */
function resetOverview (editSingle, reset, valueReset) {
    if (reset) {
        if (editSingle.parent().hasClass('intro-field')) {
            editSingle.css('cssText', 'width: auto!important;');
        }

        editSingle.prev('.value').show();
        editSingle.hide();
        $('#item-tab-details').find('.editable').removeClass('edit-disabled');
        $('[data-toggle="tooltip"]').tooltip();
        nicescrollResize('html');
    }

    // Reset value by sync with the current value
    if (valueReset === true) {
        editValSync(editSingle.closest('.field'), editSingle, true);
    }
}

/**
 * Sync field value with the current field value in overview edit field form.
 *
 * @param {Object} field
 * @param {Object} editSingle
 * @param {bool}   onlySync
 *
 * @return {void}
 */
function editValSync (field, editSingle, onlySync) {
    var input          = editSingle.find('input:text:first-child');
    var select         = !editSingle.find('.choose-select').length ? editSingle.find('select') : editSingle.find('select.choose-select');
    var textarea       = editSingle.find('textarea');
    var value          = field.find('.value');
    var dataValue      = value.attr('data-value');
    var fieldVal       = null;
    var secondFieldVal = null;

    // Format field value
    if (typeof value.attr('data-multiple') !== 'undefined') {
        fieldVal       = dataValue.split('|')[0];
        secondFieldVal = dataValue.split('|')[1];
    } else if (typeof value.attr('data-array') !== 'undefined' && value.attr('data-array') === 'true') {
        fieldVal = dataValue.split('|');
    } else {
        fieldVal = typeof dataValue !== 'undefined' ? dataValue : value.html().trim();
    }

    // If not only sync but show
    if (!onlySync) {
        $('#item-tab-details').find('.editable').addClass('edit-disabled');

        if (field.hasClass('intro-field')) {
            editSingle.css('cssText', 'width: 250px!important;');
        }

        field.find('.value').hide();
        editSingle.show();
    }

    // Set the updated value in the field input
    if (input.length && typeof input.attr('disabled') === 'undefined') {
        input.val(fieldVal);
        input.focus();
        input.setCursorPosition(input.val().length);

        if (secondFieldVal != null) {
            editSingle.find('input:nth-child(2)').val(secondFieldVal);
        }
    }

    if (typeof input.attr('disabled') !== 'undefined') {
        input.val('');
    }

    // Set the updated value in the select type field
    if (select.length) {
        select.val(fieldVal);

        if (!onlySync) {
            editSingle.attr('data-appear', 'true');
            editSingle.find('.select2-hidden-accessible').trigger('change');
            select.select2('open');
        }
    }

    // Update append select type field
    if (editSingle.find('.choose-select').length) {
        if (!onlySync) {
            editSingle.find("select[name='" + fieldVal + "_id']").val(secondFieldVal);
            editSingle.find('.child-field .select2-hidden-accessible').trigger('change');
            editSingle.attr('data-appear', 'false');
        } else {
            editSingle.find("select[name='" + fieldVal + "_id']").val(secondFieldVal).trigger('change');
        }
    }

    // Update Textarea field
    if (textarea.length) {
        textarea.val(fieldVal);
        textarea.focus();
        textarea.setCursorPosition(textarea.val().length);
    }
}
