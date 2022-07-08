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

// Laravel CSRF token for ajax request
$.ajaxSetup({
    headers : {
        'X-CSRF-TOKEN' : $('meta[name="csrf-token"]').attr('content')
    },
    beforeSend : function (jqXHR) {
        // globalVar defined in partials/footer.blade.php
        globalVar.ajaxRequest.push(jqXHR);
    },
    complete : function (jqXHR) {
        var i = globalVar.ajaxRequest.indexOf(jqXHR);

        if (i > -1) {
            globalVar.ajaxRequest.splice(i, 1);
        }
    }
});

// NProgress defined in plugins/nprogress
NProgress.start();

// Set internet connection test URL
Offline.options = { checks: { xhr: { url: globalVar.baseUrl + '/plugins/offline/img/tiny-image.gif' } } };

$(document).ready(function () {
    $('body').not('.multiple-section').css('min-height', $(window).height() + 'px');

    setTimeout(function () {
        NProgress.done();
        $('.fade').removeClass('out');
    }, 1500);

    // Page content load and flush session alert
    $('main').fadeIn(1500).css('display', 'inline-block');
    $('.pg-loader').fadeOut(1500);
    $('[data-toggle="tooltip"]').tooltip();
    $('.alert').not('.slight').delay(5000).fadeOut(1500);
    $('.middle-content').css('marginTop', nonNegative($(window).height() - $('.middle-content').height()) / 3 + 'px');

    $.fn.dataTable.ext.errMode = 'none';
    resetCheckboxRadio();

    // Header CSS when page scroll
    $(window).scroll(function () {
        if ($(this).scrollTop() > 20) {
            $('header').addClass('scroll');
        } else {
            $('header').removeClass('scroll');
        }
    });

    // Sidebar Navigation CSS and events
    $('nav').css('height', $(window).height() + 'px');

    // Navigation hierarchy
    for (var i = 0; i <= $('nav ul li a').size() - 1; i++) {
        var parentArray = $('nav ul li a').eq(i).parents().map(function () {
            return this.tagName;
        }).get();

        var navIndex = jQuery.inArray('NAV', parentArray);
        var navArray = parentArray.splice(0, navIndex);
        var left = -1;

        jQuery.each(navArray, function (key, value) {
            if (value === 'UL') {
                left = left + 1;
            }
        });

        if (left > 0) {
            $('nav ul li a').eq(i).css('text-indent', 26 + left * 15 + 'px');
            $('nav ul li a i').eq(i).css('text-indent', left * 15 + 'px');
        }
    }

    // Calculate nav row list and adjust the height according to rows
    var firstRowListCount = 0;

    for (var li = 0; li <= $('nav ul li').not('.heading').size() - 1; li++) {
        var getParentArray = $('nav ul li').eq(li).parents().map(function () {
            return this.tagName;
        }).get();

        var getNavIndex = jQuery.inArray('NAV', getParentArray);
        var getNavArray = getParentArray.splice(0, getNavIndex);

        if (getNavArray.length === 1 && getNavArray[0] === 'UL') {
            firstRowListCount = firstRowListCount + 1;
        }
    }

    var requiredUlHeight = firstRowListCount * 39;
    var navHeight = $(window).height() - 51;

    if (requiredUlHeight > navHeight) {
        $('nav.compress ul').css('height', requiredUlHeight + 'px');
        $('nav.compress ul li ul').css('height', 'auto');
    }

    // Logo width
    var logoWidth    = Math.floor($('.header-logo').width());
    var ulChildWidth = 205 - logoWidth;

    // Sidebar navigation in compress mode CSS
    $('nav.compress ul').css('width', logoWidth + 'px');
    $('.header-logo').css('width', $('nav.compress ul').width() + 'px');
    $('nav.compress ul li ul').css('width', ulChildWidth + 'px');

    // Responsive height adjustment
    heightAdjustment();

    // Reset breadcrumb
    $('.breadcrumb form').trigger('reset');
    $('.breadcrumb form').find('.select2-hidden-accessible').trigger('change');

    // Window resize effect on the body, sidebar nav, responsive elements, and height adjustment
    $(window).resize(function () {
        responsiveMediaQuery();
        heightAdjustment();

        $('body').css('min-height', $(this).height() + 'px');
        $('nav').css('height', $(this).height() + 'px');

        if ($('.header-nav').hasClass('expand')) {
            $('.header-nav').css('width', $(this).width() - $('.header-logo').width() + 'px');
        }

        // Calculate nav row list and adjust the height according to rows
        var firstRowListCount = 0;

        for (var i = 0; i <= $('nav ul li').not('.heading').size() - 1; i++) {
            var parentArray = $('nav ul li').eq(i).parents().map(function () {
                return this.tagName;
            }).get();

            var navIndex = jQuery.inArray('NAV', parentArray);
            var navArray = parentArray.splice(0, navIndex);

            if (navArray.length === 1 && navArray[0] === 'UL') {
                firstRowListCount = firstRowListCount + 1;
            }
        }

        var requiredUlHeight = firstRowListCount * 39;
        var navHeight = $(window).height() - 51;

        if (requiredUlHeight > navHeight) {
            $('nav.compress ul').css('height', requiredUlHeight + 'px');
            $('nav.compress ul li ul').css('height', 'auto');
        } else {
            $('nav.compress ul').css('height', navHeight + 'px');
            $('nav.compress ul li ul').css('height', 'auto');
        }

        nicescrollResize('html');
        nicescrollResize('nav');
    });

    // Sidebar navigation toggle event to expand|compress mode
    $('.menu-toggler').on('click', function () {
        $('.header-logo').toggleClass('compress');
        $('.header-nav').toggleClass('expand');
        $('nav').toggleClass('compress');
        $('main').toggleClass('expand');

        var hasCompress = $('nav.compress ul').get(0);

        // If sidebar navigation in compress mode
        if (hasCompress) {
            $('nav.compress').find('.collapse').css('display', 'none');
            $('nav.compress').find('span.fa-angle-left').removeClass('down');
            $('nav.compress').find('.tree').removeClass('active');

            // Adjust logo width with compress mode
            var logoWidth    = Math.floor($('.header-logo').width()) - 1;
            var ulChildWidth = 205 - logoWidth;

            $('nav.compress ul').css('width', logoWidth + 'px');
            $('.header-logo').css('width', $('nav.compress ul').width() + 'px');
            $('nav.compress ul li ul').css('width', ulChildWidth + 'px');

            // Calculate nav row list and adjust the height according to rows
            var firstRowListCount = 0;

            for (var i = 0; i <= $('nav ul li').not('.heading').size() - 1; i++) {
                var parentArray = $('nav ul li').eq(i).parents().map(function () {
                    return this.tagName;
                }).get();

                var navIndex = jQuery.inArray('NAV', parentArray);
                var navArray = parentArray.splice(0, navIndex);

                if (navArray.length === 1 && navArray[0] === 'UL') {
                    firstRowListCount = firstRowListCount + 1;
                }
            }

            var requiredUlHeight = firstRowListCount * 39;
            var navHeight = $(window).height() - 51;

            if (requiredUlHeight > navHeight) {
                $('nav.compress ul').css('height', requiredUlHeight + 'px');
                $('nav.compress ul li ul').css('height', 'auto');
            }
        }

        // If sidebar navigation in expand mode
        if (typeof hasCompress === 'undefined') {
            $('nav ul').removeAttr('style');
            $('.header-logo').removeAttr('style');
            $('nav ul li ul').removeAttr('style');
            $('.header-nav').removeAttr('style');

            for (var j = 0; j <= $('nav ul li a').size() - 1; j++) {
                var getParentArray = $('nav ul li a').eq(j).parents().map(function () {
                    return this.tagName;
                }).get();

                var getNavIndex = jQuery.inArray('NAV', getParentArray);
                var getNavArray = getParentArray.splice(0, getNavIndex);
                var left = -1;

                jQuery.each(getNavArray, function (key, value) {
                    if (value === 'UL') {
                        left = left + 1;
                    }
                });

                if (left > 0) {
                    $('nav ul li a').eq(j).css('text-indent', 26 + left * 15 + 'px');
                    $('nav ul li a i').eq(j).css('text-indent', left * 15 + 'px');
                }
            }
        }
    });

    // Sidebar navigation toggle event expands|compress in mobile devices
    $('.mob-menu-toggler').on('click', function () {
        $('.header-logo').removeClass('compress');
        $('.header-nav').removeClass('expand');
        $('nav').removeClass('compress');
        $('main').removeClass('expand');

        $('.header-logo').removeAttr('style');
        $('.header-nav').removeAttr('style');
        $('nav ul').removeAttr('style');
        $('nav ul li ul').removeAttr('style');

        $('nav').find('.collapse').css('display', 'none');
        $('nav').find('span.fa-angle-left').removeClass('down');
        $('nav').find('.tree').removeClass('active');

        $('nav').toggleClass('show');
        $('main').toggleClass('shadow');
    });

    $('main').on('click', function () {
        $('nav').removeClass('show');
        $(this).removeClass('shadow');
    });

    $('.alert button.close').on('click', function () {
        $(this).parent().hide();
    });

    // Tooltip appears with an animation
    $(document).on('shown.bs.tooltip', '[data-toggle="tooltip"]', function () {
        if (typeof $(this).attr('data-animation') !== 'undefined') {
            var animationType = $(this).attr('data-animation');
            $('.tooltip').addClass('animated ' + animationType);
        }
    });

    $(document).on('click', 'table.dataTable.dtr-inline.collapsed tbody tr td:first-child', function () {
        $('[data-toggle="tooltip"]').tooltip();
        nicescrollResize('html');

        var tr     = $(this).closest('tr');
        var syncTd = tr.find('td.sync-val');
        var nextTr = tr.next('tr');

        // Sync expanded bottom table row data with real data
        if (nextTr.hasClass('child') && syncTd.size() > 0) {
            $(tr.find('td')).each(function (index, td) {
                if ($(td).hasClass('sync-val')) {
                    var childSyncTd = nextTr.find("li[data-dtr-index='" + index + "'] .dtr-data");

                    if (childSyncTd.get(0)) {
                        childSyncTd.html($(td).html());
                    }
                }
            });
        }
    });

    // Navigation scroll by up|down arrow
    $('nav').on('keydown', function (e) {
        switch (e.which) {
            case 38:
                nicescrollResize('nav');
                break;
            case 40:
                nicescrollResize('nav');
                break;
            default:
                return;
        }

        e.preventDefault();
    });

    $('nav').mousewheel(function () {
        nicescrollResize('nav');
    });

    // Sidebar navigation link events hover, focus, click, mouseleave, etc.
    $('nav ul li a').on('hover', function () {
        if ($(this).hasClass('active') === false) {
            $(this).css('background-position', '-' + $(this).width() + 'px');
        }
    });

    $('nav ul li a').on('focus', function () {
        if ($(this).hasClass('active') === false) {
            $(this).css('background-position', '-' + $(this).width() + 'px');
        }
    });

    $('nav ul li a').on('click', function () {
        if ($(this).hasClass('active') === false) {
            $(this).css('background-position', '-' + $(this).width() + 'px');
        }
    });

    $('nav ul li a').on('mouseleave', function () {
        $(this).css('background-position', 0 + 'px');
    });

    // Smooth dropdown append nav link toggle show|hide effect.
    var previousClickTime = new Date();

    $('nav .tree').on('click', function () {
        var currentClickTime = new Date();
        var diffTime = (currentClickTime.getTime() - previousClickTime.getTime()) / 1000;

        if (diffTime > 0.5) {
            $(this).parent('li').parent('ul').find('.collapse').not($(this).next('.collapse')).slideUp();
            $(this).parent('li').parent('ul').find('.tree').not($(this)).removeClass('active');
            $(this).parent('li').parent('ul').find('span.fa-angle-left').not($(this).children('span.fa-angle-left')).removeClass('down');
            $(this).toggleClass('active');
            $(this).children('span.fa-angle-left').toggleClass('down');
            $(this).next('.collapse').slideToggle();

            previousClickTime = currentClickTime;
        }
    });

    // The dropdown list box appears with an animation
    $(document).on('click', '.dropdown-toggle', function () {
        var defaultAnimation = 'fadeIn|fadeIn';
        var dropdownMenu = $(this).parent().find('.dropdown-menu');
        var animation = typeof $(this).attr('animation') !== 'undefined' ? $(this).attr('animation').split('|') : defaultAnimation.split('|');

        $($(this).closest('.dropdown-menu')).removeClass('animated ' + animation[1]);
        dropdownMenu.addClass('animated ' + animation[0]);
        nicescrollResize('html');
    });

    // Modal form submit if press enter
    $(document).on('keypress', '.modal input, .modal textarea', function (e) {
        e.stopPropagation();
        var charCode = event.which;

        if (charCode === 13) {
            $(this).closest('.modal').find('.save').click();
        }
    });

    // Delete an item by clicking the "Delete" button in the modal form
    $('.modal-delete').on('click', function (event) {
        event.preventDefault();
        var formUrl  = $(this).parent('form').prop('action');
        var formData = $(this).parent('form').serialize();
        var itemName = $(this).parent('form').attr('data-item');
        var message  = 'This ' + itemName.toLowerCase() + ' ' +
                       'will be removed along with all associated data.<br>Are you sure you want to delete this ' +
                       itemName.toLowerCase() + '?';

        // confirmDelete function defined in modals/delete.blade.php
        confirmDelete(formUrl, formData, null, itemName, message, null);
    });

    $('.parentfield').val($('.parentfield').data('init')).trigger('change');

    // Show child append field corresponding to the parent field
    $('.parentfield').on('change', function () {
        var form = $(this).closest('form');
        var childInputs = form.find('input, select').not(this);
        var validChildsChecker = $('option:selected', this).attr('childfield').split('.');

        childInputs.each(function (index, obj) {
            var childParent = $(obj).attr('parent');

            if (typeof childParent !== 'undefined' && childParent !== false) {
                if ($.inArray(childParent, validChildsChecker) !== -1) {
                    $(obj).closest('.form-group').show();
                } else {
                    $(obj).closest('.form-group').hide();
                }
            }
        });

        nicescrollResize('html');
    });

    $(document).on('click', '.show-if input', function () {
        var showIf             = $(this).closest('.show-if');
        var indicatorChecked   = showIf.find('.indicator').prop('checked');
        var noneBox            = $(showIf.next('.none'));
        var modalBody          = $($(this).closest('.modal-body'));
        var fromGroupContainer = $(modalBody.find('.form-group-container'));
        var containerHeight    = parseInt(fromGroupContainer.height(), 10);
        var noneHeight         = parseInt(noneBox.height(), 10);
        var down               = containerHeight + noneHeight;
        var up                 = nonNegative(containerHeight - noneHeight - 465);
        var scroll             = 0;

        // If the indicator field checked then show the corresponded select type field
        if ($(this).hasClass('indicator') && $(this).prop('checked')) {
            if (showIf.attr('flush')) {
                noneBox.find("input[type='checkbox']:enabled").prop('checked', false);
                noneBox.find('select').val('');
                noneBox.find('.select2-hidden-accessible').trigger('change');
            }

            showIf.next('.none').slideDown();
            scroll = down;
        } else {
            if (!indicatorChecked) {
                showIf.next('.none').slideUp();
                scroll = up;
            }
        }

        if ((showIf.attr('scroll') && $(this).hasClass('indicator')) ||
            ($(this).is(':radio') && showIf.next('.none').css('display') === 'block')
        ) {
            modalBody.animate({ scrollTop: scroll });
        }
    });

    $(document).on('change', '.multiple-child', function () {
        var form            = $(this).closest('form');
        var childGroupClass = '.' + $(this).attr('data-child');
        var optionGroup     = $('option:selected', this).attr('for');

        // Show all child field group if parent field is not null or empty
        if ($(this).val() !== '' && $(this).val() !== null) {
            if (typeof optionGroup !== 'undefined') {
                var showGroup = $(form.find(childGroupClass + "[data-for='" + optionGroup + "']"));

                form.find(childGroupClass).not(showGroup).each(function (index, group) {
                    $(group).find('select').val('');
                    $(group).find('.select2-hidden-accessible').trigger('change');
                    $(group).find('input').val('');
                    $(group).find('.validation-error').html('');
                });

                form.find(childGroupClass).not(showGroup).hide();

                if (showGroup.css('display') !== 'block') {
                    showGroup.find('select').val('');
                    showGroup.find('.select2-hidden-accessible').trigger('change');
                    showGroup.find('input').val('');
                    showGroup.find('.validation-error').html('');
                    showGroup.slideDown();
                }
            } else {
                form.find(childGroupClass).each(function (index, group) {
                    $(group).find('select').val('');
                    $(group).find('.select2-hidden-accessible').trigger('change');
                    $(group).find('input').val('');
                    $(group).find('.validation-error').html('');
                });

                form.find(childGroupClass).hide();
            }
        } else {
            form.find(childGroupClass).hide();
        }
    });

    $(document).on('change', '.show-if select', function () {
        var modalDialog = $($(this).closest('.modal-dialog'));
        var modalLoader = modalDialog.find('.modal-loader').css('display');

        if (modalLoader === 'block') {
            return false;
        }

        var selectVal     = $(this).val();
        var dropdownClass = '.' + selectVal + '-list';
        var showIf        = $(this).closest('.show-if');
        var noneBox       = $(showIf.next('.none'));
        var modalBody     = $(this).closest('.modal-body');

        // Show next from group fields if select type field value is not empty
        if (selectVal !== '') {
            if (typeof noneBox.find(dropdownClass).data('default') !== 'undefined') {
                var defaultVal = noneBox.find(dropdownClass).data('default');
                noneBox.find(dropdownClass + ' select').val(defaultVal);
                noneBox.find(dropdownClass + ' input').val(defaultVal);
            } else {
                noneBox.find('select').val('');
                noneBox.find('input').val('');
            }

            noneBox.find('.select2-hidden-accessible').trigger('change');
            noneBox.find('.validation-error').html('');
            noneBox.find('.none').not(dropdownClass).hide();
            noneBox.find(dropdownClass).show();

            if (typeof $(this).attr('data-for-child') !== 'undefined' &&
                typeof $(noneBox.find(dropdownClass)).attr('data-for-parent') !== 'undefined'
            ) {
                $(noneBox.find(dropdownClass)).find('option').each(function (index, option) {
                    if (typeof $(option).attr('for') !== 'undefined') {
                        $(option).attr('for', selectVal);
                    }
                });
            }

            if (typeof showIf.attr('data-slide') !== 'undefined') {
                noneBox.hide();
            }

            if (typeof showIf.data('show-only') !== 'undefined') {
                if (showIf.data('show-only') === selectVal) {
                    noneBox.slideDown();
                } else {
                    noneBox.hide();
                    noneBox.find('.none').hide();
                }
            } else {
                noneBox.slideDown();
            }
        } else {
            noneBox.hide();
            noneBox.find('.none').hide();
        }
    });

    // Ajax request to load append dropdown list by "appendDropdownLoad" if this select value is not empty
    $(document).on('change', "select[data-append-request='true']", function () {
        var id        = $(this).val();
        var field     = $(this).attr('name');
        var child     = $(this).data('child').split('|');
        var parent    = $(this).data('parent');
        var form      = $($(this).closest($(this).data('container')));
        var selectObj = $(this);

        $(child).each(function (key, thisChild) {
            appendDropdownLoad(selectObj, form, parent, thisChild, field, id);
        });
    });

    $(document).on('change', '.related-field .parent-field select', function () {
        var container  = $($(this).closest('.related-field'));
        var parent     = $($(this).closest('.parent-field'));
        var child      = $(container.find('.child-field'));
        var editSingle = $($(this).closest('.edit-single'));

        // If the parent field is not empty then show the corresponding child field
        if ($(this).val() === '') {
            child.find('*[data-field]').hide();
            $(child.find('*[data-field]')).find('select').val('');
            $(child.find('*[data-field]')).find('.select2-hidden-accessible').trigger('change');
            child.find("*[data-default='true']").show();
            child.find("*[data-child='true']").val(null);
        } else {
            child.find('*[data-field]').hide();
            var selectChild      = $(child.find("*[data-field='" + $(this).val() + "']"));
            var appearEditSingle = typeof editSingle.attr('data-appear') !== 'undefined' &&
                                   editSingle.attr('data-appear') === 'false';

            if (!editSingle.length || appearEditSingle) {
                selectChild.find('select').val('');
                selectChild.find('.select2-hidden-accessible').trigger('change');
            }

            var selectVal = selectChild.find('select').val();
            selectChild.show();
            child.find("*[data-child='true']").val(selectVal);
        }
    });

    $(document).on('change', '.related-field .child-field select', function () {
        var container = $($(this).closest('.child-field'));
        container.find("*[data-child='true']").val($(this).val());
    });

    $(document).on('change', ".related-field .child-field *[data-child='true']", function () {
        var container = $($(this).closest('.related-field'));
        var parent    = $(container.find('.parent-field'));
        var parentVal = parent.find('select').val();
        var child     = $($(this).closest('.child-field'));

        // If the hidden child value is not empty then show the corresponding child field
        // else show the default input field
        if ($(this).val() === '') {
            child.find('*[data-field]').hide();
            child.find("*[data-default='true']").show();
        } else {
            child.find('*[data-field]').hide();
            var selectChild = $(child.find("*[data-field='" + parentVal + "']"));
            selectChild.find('select').val($(this).val());
            selectChild.find('.select2-hidden-accessible').trigger('change');
            selectChild.show();
        }
    });

    // Toggle switch permission enable|disable event effect on child checkbox permissions
    $(document).on('click', '.toggle-permission .switch', function () {
        var checked          = $(this).find('input').prop('checked');
        var togglePermission = $($(this).closest('.toggle-permission'));
        var childPermission  = $(togglePermission.find('.child-permission'));

        if (checked === true) {
            childPermission.css('opacity', 1);
            childPermission.find('input').attr('disabled', false);
            childPermission.find("input[data-default='true']").prop('checked', true);
        } else {
            childPermission.css('opacity', 0.5);
            childPermission.find('input').attr('disabled', true);
            childPermission.find('input').prop('checked', false);
        }
    });

    $(document).on('change', ".child-permission input[data-primary='true']", function () {
        // If primary child permission is false, then disable parents and the rest of all child permissions
        if ($(this).prop('checked') === false) {
            var togglePermission = $($(this).closest('.toggle-permission'));
            togglePermission.find('.parent-permission').not('.reset-false').find('input').prop('checked', false);
            togglePermission.find('.child-permission').not('.reset-false').css('opacity', 0.5);
            togglePermission.find('.child-permission').not('.reset-false').find('input').prop('checked', false);
            togglePermission.find('.child-permission').not('.reset-false').find('input').attr('disabled', true);
        }
    });

    // Display text on click effect toggle enable|disable the corresponding checkbox
    $(document).on('click', '.inline-input span', function () {
        var checked   = $(this).find('input').prop('checked');
        var inputType = $(this).find('input').prop('type');

        if (checked === true) {
            if (inputType !== 'radio') {
                $(this).find('input').prop('checked', false);
            }
        } else {
            $(this).find('input').prop('checked', true);
        }
    });

    $(document).on('click', '.inline-input span input', function () {
        var checked = $(this).prop('checked');

        if (checked === true) {
            $(this).prop('checked', false);
        } else {
            $(this).prop('checked', true);
        }
    });

    // Message input field key events for adjusting CSS height
    $('.input-msg').on('keyup keydown blur change', function () {
        var currentHeight       = $(this).height() + 22;
        var currentScrollHeight = $(this).prop('scrollHeight');
        var messageBox          = $(this).parent().find('.message-box');

        if ($(this).val() === '') {
            $(this).css('height', '50px');
            messageBox.css('height', '425px');
            $(this).css('overflow', 'hidden');
        } else {
            if (currentHeight !== currentScrollHeight && currentScrollHeight < 150) {
                var minusHeight  = currentScrollHeight - 50;
                var changeHeight = 425 - minusHeight;
                $(this).css('height', currentScrollHeight + 'px');
                $(this).css('overflow', 'hidden');
                messageBox.css('height', changeHeight + 'px');
            } else {
                if (currentScrollHeight > 150) {
                    $(this).css('overflow', 'auto');
                }
            }
        }
    });

    $('.left-icon, .right-icon').tooltip('disable');

    $(document).on('mouseover', '.left-icon, .right-icon', function () {
        if ($(this).find('input').val() === '') {
            $(this).tooltip('disable');
        } else {
            $(this).tooltip('enable');

            if ($(this).parent().children('.tooltip').length === 0) {
                $(this).tooltip('show');
            }
        }
    });

    // Hide left|right move arrow when user leave kanban board
    $(document).on('mouseleave', '.funnel-wrap', function () {
        $(this).children('.funnel-container-arrow.left').css('left', '-70px');
        $(this).children('.funnel-container-arrow.right').css('right', '-70px');
    });

    // Mouseover on kanban stage event for left|right move board
    $(document).on('mouseover', '.funnel-stage', function () {
        var container = $($(this).parent('.funnel-container'));
        kanbanLeftRight(container);
    });

    // When the mouse enters in kanban stage then send an ajax request to load more kanban items
    $(document).on('mouseenter', '.funnel-stage', function () {
        if ($(this).find('.li-container').height() < $(this).height() && $(this).hasClass('loading')) {
            ajaxKanbanCard($(this).find('.funnel-card-container'), 10, null);
        }
    });

    // Kanban left arrow move board to left
    $(document).on('mouseover', '.funnel-container-arrow.left', function () {
        var container = $($(this).parent('.funnel-wrap').find('.funnel-container'));
        container.stop(true);
        container.animate({ scrollLeft: 0 }, 1500);
    });

    // Kanban right arrow move board to right
    $(document).on('mouseover', '.funnel-container-arrow.right', function () {
        var container            = $($(this).parent('.funnel-wrap').find('.funnel-container'));
        var containerWidth       = container.innerWidth();
        var totalStages          = container.children('.funnel-stage').size();
        var totalStagesWidth     = totalStages * 300;
        var containerMaxRightPos = totalStagesWidth > containerWidth ? totalStagesWidth : containerWidth;
        var goRightVal           = nonNegative(containerMaxRightPos - containerWidth);

        container.stop(true);
        container.animate({ scrollLeft: goRightVal }, 1500);
    });

    // Stop moving board when mouse leave kanban left|right arrow
    $(document).on('mouseleave', '.funnel-container-arrow', function () {
        var container = $($(this).parent('.funnel-wrap').find('.funnel-container'));
        container.stop(true);
    });

    // Kanban card bottom action button group slide toggle show|hide
    $(document).on('click', '.funnel-bottom-btn', function () {
        $(this).closest('.funnel-card').find('.funnel-btn-group').slideToggle();
    });

    // Ajax form submit for smooth validation and saving experience
    $(document).on('click', '.smooth-save .save, .smooth-save .submit', function (e) {
        e.preventDefault();
        var form = $(this).closest('form');
        var formUrl = form.prop('action');
        var formData = new FormData($('.smooth-save').get(0));
        globalVar.ladda = Ladda.create(this);
        globalVar.ladda.start();
        $(this).attr('disabled', true);
        smoothSave(formUrl, formData);
    });

    // Ajax form submission for getting smooth validation experience
    $('#save, #save-and-new, .smooth-validation .submit').on('click', function (e) {
        e.preventDefault();

        if ($('input[name=add_new]').get(0)) {
            $('input[name=add_new]').val(0);

            if ($(this).attr('name') === 'save_and_new') {
                $('input[name=add_new]').val(1);
            }
        }

        var form = $(this).closest('form');
        var formUrl = form.prop('action');
        var formData = form.serialize();

        if (!$(this).is(':disabled')) {
            globalVar.ladda = Ladda.create(this);
            globalVar.ladda.start();
            form.find('.btn-info').attr('disabled', true);
            form.find(".btn[type='submit']").attr('disabled', true);
            ajaxValidation(form, formUrl, formData);
        }
    });

    // Generate a strong password in one click
    $(document).on('click', '.password-generator', function () {
        var password = $.passGen({
            length    : 10,
            numeric   : true,
            lowercase : true,
            uppercase : true,
            special   : false
        });

        $(this).parent().find('input.password').val(password);
    });

    // Show password in a readable format
    $(document).on('click', '.show-password', function () {
        var input     = $(this).parent().find('input.password');
        var inputType = input.prop('type');

        if (inputType === 'password') {
            input.prop('type', 'text');
        } else {
            input.prop('type', 'password');
        }
    });

    // Toggle view of expand|compress paragraph
    $(document).on('click', 'p .more', function () {
        var para   = $($(this).closest('p'));
        var extend = para.find('.extend');

        if (extend.css('display') === 'none') {
            extend.show();
            $(this).html('<br>show less');
        } else {
            extend.hide();
            $(this).html('<span>...</span> more');
        }

        nicescrollResize('html');
    });

    // Timeline new note circle mark fade out when mouse leave
    $(document).on('mouseleave', '.timeline-info', function () {
        $(this).find('.circle').fadeOut(1500);
    });

    $(document).on('mouseleave', '.timeline-details', function () {
        $($(this).closest('.timeline-info')).find('.circle').fadeOut(1500);
    });

    // Expand note text area on focus in event
    $(document).on('focusin', '.comment-form textarea', function () {
        var form = $($(this).closest('.comment-form'));
        var timelineContainer = $(form.parent());
        $(this).css('height', '60px');
        form.find('.form-group.bottom').slideDown('fast');
        $(timelineContainer.find('.timeline-info')).find('.cancel').trigger('click');
    });

    // Reset note text area when click cancel
    $(document).on('click', '.comment-form .cancel', function (e) {
        e.preventDefault();
        var form = $($(this).closest('.comment-form'));
        resetCommentForm(form, false);
    });

    // Ajax request to save a new note
    $(document).on('click', '.save-comment', function (e) {
        e.preventDefault();

        var saveBtn  = this;
        var form     = $($(this).closest('.comment-form'));
        var timeline = $(form.parent().find('.timeline'));
        var postUrl  = form.data('posturl');
        var comment  = $(form.find('textarea'));

        if (comment.val() !== null && comment.val() !== '') {
            var data = form.find('textarea, input').serialize();

            $.ajax({
                type       : 'POST',
                url        : postUrl,
                data       : data,
                dataType   : 'JSON',
                beforeSend : function (xhr, opts) {
                    globalVar.ladda = Ladda.create(saveBtn);
                    globalVar.ladda.start();
                    $(saveBtn).attr('disabled', true);
                },
                success : function (data, textStatus, jqXHR) {
                    if (data.status === true) {
                        if (typeof data.html !== 'undefined') {
                            timeline.find('.timeline-info').removeClass('top');
                            timeline.find('.end-down:gt(0)').hide();
                            $(data.html).hide().prependTo('.timeline').fadeIn(1350);
                            $('[data-toggle="tooltip"]').tooltip();
                            resetCommentForm(form, true);
                            nicescrollResize('html');
                        }
                    } else {
                        notifyErrors(data.errors, false);
                    }

                    removeLadda(data.status, '.comment-form', 1000);
                    $(saveBtn).attr('disabled', false);
                },
                error : function (jqXHR, textStatus, errorThrown) {
                    ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'notify', false, 2500);
                    $(saveBtn).attr('disabled', false);

                    if (typeof globalVar.ladda !== 'undefined' && globalVar.ladda !== null) {
                        globalVar.ladda.stop();
                        globalVar.ladda.remove();
                    }
                }
            });
        } else {
            comment.focus();
        }
    });

    // Ajax request to update note and response note position accordingly
    $(document).on('click', '.update-comment', function (e) {
        e.preventDefault();

        var saveBtn      = this;
        var form         = $($(this).closest('.timeline-form'));
        var postUrl      = form.data('posturl');
        var comment      = $(form.find('textarea'));
        var timeline     = $($(this).closest('.timeline'));
        var timelineInfo = $($(this).closest('.timeline-info'));

        if (timelineInfo.hasClass('pin')) {
            timeline = $(this).closest('.timeline-pin').next('.timeline');
        }

        if (comment.val() !== null && comment.val() !== '') {
            var data = form.find('textarea, input').serialize();

            $.ajax({
                type       : 'POST',
                url        : postUrl,
                data       : data,
                dataType   : 'JSON',
                beforeSend : function (xhr, opts) {
                    globalVar.ladda = Ladda.create(saveBtn);
                    globalVar.ladda.start();
                    $(saveBtn).attr('disabled', true);
                },
                success : function (data) {
                    if (data.status === true) {
                        if (typeof data.html !== 'undefined') {
                            if (data.location === 0) {
                                $(timeline.prev('.timeline-pin')).html(data.html);
                            } else {
                                timeline.find(".timeline-info[data-id='" + data.location + "']").replaceWith(data.html);
                            }

                            timeline.find('.timeline-info:first-child').addClass('top');
                            timeline.find('.timeline-info:not(:first-child)').removeClass('top');
                            timeline.find('.end-down:gt(0)').hide();

                            $('[data-toggle="tooltip"]').tooltip();
                            nicescrollResize('html');
                        }
                    } else {
                        notifyErrors(data.errors, false);
                    }

                    removeLadda(data.status, '.comment-form', 1000);
                    $(saveBtn).attr('disabled', false);
                },
                error : function (jqXHR, textStatus, errorThrown) {
                    ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'notify', false, 2500);
                    $(saveBtn).attr('disabled', false);

                    if (typeof globalVar.ladda !== 'undefined' && globalVar.ladda !== null) {
                        globalVar.ladda.stop();
                        globalVar.ladda.remove();
                    }
                }
            });
        } else {
            comment.focus();
        }
    });

    // Dropzone file remove event
    $(document).on('click', '.edit-dz-remove', function () {
        var container = $($(this).closest('.dropzone-container'));
        var preview   = $($(this).closest('.dz-preview'));
        var filename  = preview.find('.dz-filename').data('original');
        container.find("input[value='" + filename + "']").attr('name', 'removed_files[]');
        preview.remove();
    });

    // Appear timeline note edit form
    $(document).on('click', '.timeline-edit', function () {
        var thisTimelineInfo  = $($(this).closest('.timeline-info'));
        var content           = $(this).closest('.timeline-details-content');
        var container         = $(this).closest('.timeline-details');
        var timelineContainer = $($(this).closest('.timeline-info')).parent().parent();

        $.ajax({
            type     : 'GET',
            url      : $(this).data('url'),
            data     : { id: $(this).data('id') },
            dataType : 'JSON',
            success  : function (data) {
                if (data.status === true) {
                    if (typeof data.html !== 'undefined' && content.css('display') !== 'none') {
                        content.hide();
                        container.append(data.html);
                        textOverflowTitle('.dz-filename span');

                        $(timelineContainer.find('.timeline-info').not(thisTimelineInfo)).find('.cancel').trigger('click');
                        timelineContainer.find('.comment-form .cancel').trigger('click');

                        atWhoInit();
                        dropzoneInit();
                        $('[data-toggle="tooltip"]').tooltip();
                        nicescrollResize('html');
                    }
                } else {
                    $.notify({ message: 'Something went wrong.' }, globalVar.dangerNotify);
                }
            },
            error : function (jqXHR, textStatus, errorThrown) {
                ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'notify', false, 2500);
            }
        });
    });

    // Cancel timeline note edit form
    $(document).on('click', '.timeline-form .cancel', function (e) {
        e.preventDefault();
        var dropzoneId = $($(this).closest('.timeline-form')).find('.modalfree-dropzone').attr('id');
        globalVar.dropzone[dropzoneId].removeAllFiles(true);
        $($(this).closest('.timeline-details')).find('.timeline-details-content').show();
        $(this).closest('.timeline-form').remove();
    });

    // Click event to load more timeline items
    $(document).on('click', '.load-timeline', function (e) {
        e.preventDefault();
        var timelineInfo = $($(this).closest('.timeline-info'));

        if (!timelineInfo.hasClass('disable')) {
            timelineInfo.addClass('loading');
            timelineInfo.find('.load-icon').show();
            var timeline    = $($(this).closest('.timeline'));
            var url         = timeline.data('url');
            var relatedType = timeline.data('relatedtype');
            var relatedId   = timeline.data('relatedid');
            var latestId    = $(timelineInfo.prev('.timeline-info')).data('id');

            $.ajax({
                type     : 'GET',
                url      : url,
                data     : { type: relatedType, typeid: relatedId, latestid: latestId },
                dataType : 'JSON',
                success  : function (data) {
                    if (data.status === true) {
                        if (typeof data.html !== 'undefined') {
                            timelineInfo.remove();
                            $(data.html).hide().appendTo('.timeline').fadeIn(1150);
                            $('[data-toggle="tooltip"]').tooltip();
                            nicescrollResize('html');
                        }
                    } else {
                        notifyErrors(data.errors, true);
                        timelineInfo.removeClass('loading');
                        timelineInfo.find('.load-icon').hide();
                    }
                },
                error : function (jqXHR, textStatus, errorThrown) {
                    ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'notify', true, 1000);
                }
            });
        }
    });

    // Click event to pin|unpin of a timeline note
    $(document).on('click', '.pin-btn', function () {
        var pin = parseInt($(this).attr('data-pin'), 10);
        var timeline = null;

        if (typeof pin !== 'undefined' && (pin === 1 || pin === 0)) {
            if (pin === 1) {
                timeline = $($(this).closest('.timeline'));
            } else {
                timeline = $(this).closest('.timeline-pin').next('.timeline');
            }

            var postUrl = $(this).data('url');

            $.ajax({
                type     : 'POST',
                url      : postUrl,
                data     : { pin: pin },
                dataType : 'JSON',
                success  : function (data) {
                    if (data.status === true) {
                        // If response note is pin note then render note in top pin location
                        if (typeof data.pinHtml !== 'undefined' && data.pinHtml !== null) {
                            timeline.prev('.timeline-pin').html(data.pinHtml);
                            timeline.find(".timeline-info[data-id='" + data.pinLocation + "']").remove();

                            if (typeof data.timelineInfoCount !== 'undefined' && data.timelineInfoCount === 0) {
                                timeline.html('');
                            }
                        }

                        // If response note is unpinned note then render note in the proper location
                        if (typeof data.unpinHtml !== 'undefined' && data.unpinHtml !== null) {
                            if (typeof data.timelineInfoCount !== 'undefined' && data.timelineInfoCount <= 1) {
                                timeline.html('');
                            }

                            if (data.unpinLocation != null) {
                                $(timeline.prev('.timeline-pin')).find(".timeline-info[data-id='" + data.unpinLocation + "']").remove();
                            }

                            if (data.prevLocation === 0) {
                                timeline.find('.timeline-info').removeClass('top');
                                timeline.prepend(data.unpinHtml);
                            } else {
                                timeline.find(".timeline-info[data-id='" + data.prevLocation + "']").after(data.unpinHtml);
                            }
                        }

                        timeline.find('.timeline-info:first-child').addClass('top');
                        timeline.find('.timeline-info:not(:first-child)').removeClass('top');

                        $('[data-toggle="tooltip"]').tooltip();
                        nicescrollResize('html');
                    } else {
                        $.notify({ message: 'Something went wrong.' }, globalVar.dangerNotify);
                    }
                },
                error : function (jqXHR, textStatus, errorThrown) {
                    ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'notify', true, 1000);
                }
            });
        }
    });

    // Click event to update toggle open|closed status
    $(document).on('click', '.status-checkbox', function (e) {
        e.preventDefault();
        var thisStatusCheckbox = $(this);
        var tr = $(this).closest('tr');
        $(this).tooltip('hide');

        if (!$(this).hasClass('disabled')) {
            $.ajax({
                type     : 'POST',
                url      : $(this).data('url'),
                dataType : 'JSON',
                success  : function (data) {
                    if (data.status === true) {
                        if (data.checkbox !== null) {
                            thisStatusCheckbox.replaceWith(data.checkbox);
                            tr.find('.activity-status').replaceWith(data.activityStatus);
                            tr.next('tr.child').find('.activity-status').replaceWith(data.activityStatus);
                        }

                        if (typeof data.completion !== 'undefined' && data.completion !== null) {
                            tr.find('.completion-bar').replaceWith(data.completion);
                            tr.next('tr.child').find('.completion-bar').replaceWith(data.completion);
                        }

                        if (tr.find('.status-checkbox-link').hasClass('open')) {
                            tr.find('.status-checkbox-link').addClass('closed');
                            tr.find('.status-checkbox-link').removeClass('open');
                        } else if (tr.find('.status-checkbox-link').hasClass('closed')) {
                            tr.find('.status-checkbox-link').addClass('open');
                            tr.find('.status-checkbox-link').removeClass('closed');
                        }

                        if (typeof globalVar.jqueryDataTable !== 'undefined') {
                            if (typeof data.saveId !== 'undefined' && data.saveId !== null) {
                                focusSavedRow(globalVar.jqueryDataTable, data.saveId, true);
                            }
                        }

                        if (data.markAsClosed) {
                            singleNotify(ucword(data.item) + ' was closed successfully', 'success', globalVar.successNotify, false);
                        } else {
                            singleNotify(ucword(data.item) + ' was re-opened', 'warning', globalVar.warningNotify, false);
                        }

                        $("[data-toggle='tooltip']").tooltip();
                    } else {
                        $.notify({ message: 'Something went wrong.' }, globalVar.dangerNotify);
                    }
                },
                error : function (jqXHR, textStatus, errorThrown) {
                    ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'notify', false, 2500);
                }
            });
        }
    });

    // Follow a specified resource to get every changes notification
    $(document).on('click', '.follow-record', function () {
        var follower = $(this);
        var formUrl  = globalVar.baseAdminUrl + '/follower/' + $(this).data('type') + '/' + $(this).data('id');
        var formData = { type: $(this).data('type'), id: $(this).data('id'), follow: $(this).attr('data-follow') };

        $.ajax({
            type     : 'POST',
            url      : formUrl,
            data     : formData,
            dataType : 'JSON',
            success  : function (data) {
                if (data.status === true) {
                    if (data.follow === true) {
                        // Render HTML to unfollow
                        follower.attr('data-follow', 0);
                        follower.find('i').attr('class', 'mdi mdi-eye-off-outline');
                        follower.find('.status-text').text('Unfollow');
                        $.notify({ message: 'You are now following the ' + follower.data('type') }, globalVar.successNotify);
                    } else if (data.follow === false) {
                        // Render HTML to follow
                        follower.attr('data-follow', 1);
                        follower.find('i').attr('class', 'mdi mdi-eye');
                        follower.find('.status-text').text('Follow');
                        $.notify({ message: 'You are not following the ' + follower.data('type') }, globalVar.warningNotify);
                    }

                    if (data.count != null) {
                        if (data.count > 0) {
                            if (data.html != null) {
                                if ($('.follower-box').get(0)) {
                                    $('.follower-box').html(data.html);
                                } else {
                                    var followerHtml = "<div class='timeline-sidebox follower-container float-sm-auto-md-right'>";
                                    followerHtml += "<h4>Followers (<span class='follower-count'>" + data.count + '</span>)</h4>';
                                    followerHtml += "<span class='avatar-links follower-box'>" + data.html + '</span>';
                                    followerHtml += '</div>';
                                    $('.follower-container-box').html(followerHtml);
                                }
                            }

                            $('.follower-count').text(data.count);
                        } else {
                            $('.follower-container').remove();
                        }
                    }

                    $('[data-toggle="tooltip"]').tooltip();
                    nicescrollResize('html');
                } else {
                    if (!$(".alert.alert-danger[role='alert']").get(0)) {
                        $.each(data.errors, function (index, value) {
                            $.notify({ message: value }, globalVar.dangerNotify);
                        });
                    }
                }
            },
            error : function (jqXHR, textStatus, errorThrown) {
                ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'notify', false, 2500);
            }
        });
    });

    // Remove a table row
    $('.modal table').on('click.dt', '.close', function () {
        var tr     = $(this).closest('tr');
        var tbody  = tr.parent('tbody');
        var serial = tbody.attr('data-serial');
        tr.remove();

        if (typeof serial !== 'undefined') {
            tbody.find('tr').each(function (index, obj) {
                $(obj).find('td:first-child').html(index + 1);
            });
        }
    });

    $(document).on('mouseleave', 'tr.saved', function () {
        $(this).removeClass('saved');
    });

    // Dropdown action box appear on up|down position depends on the height
    $(document).on('click.dt', '#datatable .dropdown-toggle', function () {
        var dropdownHeight = $(this).next('.dropdown-menu').height() + 2;
        var tr             = $(this).closest('tr');
        var bottomHeight   = 0;
        var topHeight      = 0;

        tr.nextAll('tr').each(function (index, value) {
            bottomHeight += $(value).height();
        });

        tr.prevAll('tr').each(function (index, value) {
            topHeight += $(value).height();
        });

        if ((bottomHeight + 150) <= dropdownHeight) {
            nicescrollResize('html');

            if ((topHeight + 100) >= (dropdownHeight)) {
                $(this).parent('.dropdown').addClass('dropup');
            }
        }
    });

    // Mouseenter event on side nav compress list to get icon width
    $(document).on('mouseenter', 'nav.compress ul li', function () {
        var parentArray = $(this).parents().map(function () {
            return this.tagName;
        }).get();

        var navIndex = jQuery.inArray('NAV', parentArray);
        var navArray = parentArray.splice(0, navIndex);

        if (navArray.length === 1 && navArray[0] === 'UL') {
            var logoWidth = Math.floor($('.header-logo').width());
            $(this).find('i.fa').first().css('width', logoWidth + 'px');
        }
    });

    // Mouse leave event on side nav compress list to reset icon CSS
    $(document).on('mouseleave', 'nav.compress ul li', function () {
        var parentArray = $(this).parents().map(function () {
            return this.tagName;
        }).get();

        var navIndex = jQuery.inArray('NAV', parentArray);
        var navArray = parentArray.splice(0, navIndex);

        if (navArray.length === 1 && navArray[0] === 'UL') {
            $(this).find('.collapse').css('display', 'none');
            $(this).find('span.fa-angle-left').removeClass('down');
            $(this).find('.tree').removeClass('active');
            $(this).find('i.fa').first().removeAttr('style');
        }
    });

    // On change event effect on a related field value and enable|disabled status
    $(document).on('change', '*[data-option-related]', function () {
        var thisVal       = $(this).val();
        var option        = $(this).find("option[value='" + thisVal + "']");
        var form          = $(this).closest('form');
        var related       = $(this).data('option-related');
        var relatedField  = form.find("*[name='" + related + "']");
        var relatedHidden = relatedField.next("input[type='hidden']");

        if (typeof option.attr('relatedval') !== 'undefined') {
            var newVal = option.attr('relatedval');

            if (typeof relatedHidden !== 'undefined' && relatedHidden.length > 0 && relatedHidden.val() !== 0) {
                newVal = relatedHidden.val();
                relatedHidden.val(0);
            }

            relatedField.val(newVal).trigger('change');

            if (typeof option.attr('freeze') !== 'undefined' && option.attr('freeze') === 'true') {
                if (relatedField.is('select')) {
                    relatedField.attr('disabled', true);
                } else {
                    relatedField.attr('readonly', true);
                }
            } else {
                if (relatedField.is('select')) {
                    relatedField.attr('disabled', false);
                } else {
                    relatedField.attr('readonly', false);
                }
            }
        } else {
            if (relatedField.is('select')) {
                relatedField.attr('disabled', false);
            } else {
                relatedField.attr('readonly', false);
                relatedField.val('');
            }
        }
    });

    // Click event: Ajax request to appear to edit modal with a default value by getEditData
    $(document).on('click', '.edit', function () {
        var id   = $(this).attr('editid');
        var data = { id: id };

        if (typeof $(this).data('url') !== 'undefined') {
            var url       = $(this).data('url') + '/' + id + '/edit';
            var updateUrl = $(this).data('url') + '/' + id;

            // getEditData function defined in modals/edit.blade.php
            getEditData(id, data, url, updateUrl);
        }
    });

    // When the modal data table load a new page then the modal scroll position at the top
    $(document).on('click', '.modal .paginate_button', function () {
        if (!$(this).hasClass('current') &&
            !$(this).hasClass('disabled') &&
            typeof $(this).closest('.modal-body').data('paginate-top') !== 'undefined'
        ) {
            $(this).closest('.modal-body').animate({ scrollTop: 0 });
        } else {
            var modalBody = $(this).closest('.modal-body');
            var fromGroupHeight = $(this).closest('.form-group').height();

            setTimeout(function () {
                if (modalBody.find('tbody').height() < 380 || fromGroupHeight > 900) {
                    modalBody.animate({ scrollTop: 0 });
                }
            }, 1000);
        }
    });

    // Browse files to upload via dropzone
    $(document).on('click', '.dropzone-attach', function () {
        $($(this).closest('.form-group')).find('.modalfree-dropzone').trigger('click');
    });

    // Browse files to attach with messages via dropzone
    $(document).on('click', '.msg-attach', function () {
        $('.emoji').jemoji('close');
        $($(this).closest('#chat-message')).find('.modalfree-dropzone').trigger('click');
    });

    $(document).on('click', '.browse', function (e) {
        e.preventDefault();
        var uploadzone = $($(this).closest('.uploadzone'));
        uploadzone.find("input[type='file']").click();
    });

    // On change event: image extension and size validation, preview and crop image
    $(document).on('change', ".uploadzone input[type='file']", function () {
        var modal      = $($(this).closest('.modal'));
        var inputName  = $(this).attr('name');
        var uploadzone = $($(this).closest('.uploadzone'));
        var fromGroup  = $(uploadzone.closest('.form-group'));
        var cropper    = fromGroup.find('.cropper');

        if ($(this).val() !== '') {
            var file           = this.files[0];
            var validExtension = extensionValidation(file, ['png', 'jpg', 'jpeg', 'gif', 'webp']);
            var validSize      = filesizeValidation(file, 3000000);

            fromGroup.find(".validation-error[field='" + inputName + "']").text('');

            if (!validExtension) {
                fromGroup.find(".validation-error[field='" + inputName + "']").append('The ' + inputName + ' is invalid. ');
            }

            if (!validSize && validExtension) {
                fromGroup.find(".validation-error[field='" + inputName + "']").append('The ' + inputName + ' may not be greater than 3MB.');
            }

            if (validExtension && validSize) {
                fromGroup.find(".validation-error[field='" + inputName + "']").text('');
                previewImg(this, cropper);
                uploadzone.hide();
                cropperInit(cropper);
                fromGroup.find('.cropper-wrap').fadeIn(500);
                modal.find('.modal-footer').fadeIn(500);
            }
        }
    });

    // Nicescroll initialize on HTML
    $('html').niceScroll({
        cursorcolor        : '#c8c8c8',
        cursoropacitymin   : 0,
        cursoropacitymax   : 1,
        zindex             : 999,
        cursorwidth        : '5px',
        cursorborder       : '0 solid #c8c8c8',
        cursorborderradius : '3px',
        scrollspeed        : 60,
        mousescrollstep    : 40,
        hwacceleration     : true,
        grabcursorenabled  : true,
        autohidemode       : true,
        background         : 'rgba(238, 238, 238, 0)',
        smoothscroll       : true,
        enablekeyboard     : false,
        enablemousewheel   : true,
        sensitiverail      : true,
        hidecursordelay    : 500,
        spacebarenabled    : true,
        railpadding        : { top: 0, right: 0, left: 0, bottom: 0 }
    });

    // Nicescroll initialize on the nav
    $('nav').niceScroll({
        cursorcolor        : 'rgba(240, 240, 240, 0.5)',
        cursoropacitymin   : 0,
        cursoropacitymax   : 0,
        zindex             : 999,
        cursorwidth        : '3px',
        cursorborder       : '0 solid rgba(0, 0, 0, 0.25)',
        cursorborderradius : '2px',
        scrollspeed        : 60,
        mousescrollstep    : 40,
        hwacceleration     : true,
        grabcursorenabled  : true,
        autohidemode       : true,
        background         : 'rgba(0, 0, 0, 0)',
        smoothscroll       : true,
        enablekeyboard     : true,
        enablemousewheel   : true,
        sensitiverail      : true,
        hidecursordelay    : 500,
        spacebarenabled    : true
    });

    // Plugin initialize
    counterUpInit(null);
    perfectScrollbarInit();
    sortableInit();

    if ($('.modal').get(0)) {
        // PerfectScrollbar defined in plugins/perfectscrollbar
        var modalPs = new PerfectScrollbar('.modal');
    }

    // Date picker plugin initialize
    if ($('.datepicker').get(0)) {
        $('.datepicker').not('.only-view').datepicker({
            format : 'yyyy-mm-dd'
        });
    }

    // Date time picker plugin initialize
    if ($('.datetimepicker').get(0)) {
        $('.datetimepicker').datetimepicker({
            format         : 'Y-m-d h:i A',
            formatTime     : 'h:i A',
            validateOnBlur : false
        });
    }

    // Emoji plugin initialize
    if ($('.emoji').get(0)) {
        $('.emoji').jemoji({
            folder     : globalVar.baseUrl + '/plugins/jemoji/emojis/',
            navigation : false
        });

        globalVar.jemojiPs = new PerfectScrollbar('.jemoji-icons', {
            wheelSpeed         : 2,
            wheelPropagation   : true,
            minScrollbarLength : 30
        });
    }

    // Select2 box scrolling via perfectScroll plugin
    $(document).on('click', '.select2-container', function () {
        setTimeout(function () {
            destroySelect2PerfectScroll();
            initOpenSelect2PerfectScroll();
        }, 100);
    });

    // Select2 result search
    $(document).on('keyup', '.white-container.tags .select2-search__field', function (e) {
        var li = $(".select2-results__options li:contains('" + $(this).val() + "')");
        var charCode = e.which;

        if (charCode === 8) {
            $(this).val('');
            $('.select2-results__options li').remove();
        } else {
            if (li.get(0)) {
                var liObj = $($(".select2-results__options li:contains('" + $(this).val() + "')").get(0));
                $('.select2-results__options li').not(liObj).removeClass('select2-results__option--highlighted');
                $('.select2-results__options li').not(liObj).remove();
                li.addClass('select2-results__option--highlighted');
            }
        }
    });

    // Clean unnecessary modal temporary files
    $('.modal .cancel, .modal .close').on('click', function () {
        cleanDropzoneTempFiles($(this));
    });

    // If the file was not found
    $(document).on('click', ".download[data-valid='0'], .filelink[data-valid='0']", function (e) {
        e.preventDefault();
        $.notify({ message: 'The file was not found.' }, globalVar.dangerNotify);
    });

    // Initialize select2, chart, @who, calendar, dropzone plugins
    select2PluginInit();
    initChart();
    atWhoInit();
    calendarInit();
    dropzoneInit();

    // screenfull defined in plugins/screenfull
    $('#fullscreen').on('click', function () {
        if (screenfull.enabled) {
            screenfull.toggle();
        }
    });

    responsiveMediaQueryOnLoad();
});

/**
 * Height adjustment of HTML elements.
 *
 * @return {void}
 */
function heightAdjustment () {
    // Kanban board height adjustment
    if ($(window).height() > 700) {
        var fullHeight = $(window).height() - 190;
        $('.funnel-card-container').css('height', fullHeight + 'px');
        $('.full-height').css('height', fullHeight + 'px');
    } else {
        $('.funnel-card-container').css('height', '470px');
    }

    // Dashboard activity stream height adjustment
    if ($('.widget.stream').get(0)) {
        var percentageWidth = $('.widget.stream').parent().width() / $('.widget.stream').closest('.full').width() * 100;

        if (percentageWidth < 90) {
            setTimeout(function () {
                var adjustHeight = $('.widget.stream').closest('.full').find('.calendar').height() + 340;
                $('.widget.stream .timeline').css('height', adjustHeight + 'px');
            }, 500);
        }
    }

    // Bulk actions area height adjustment
    if ($('.bulk').css('display') === 'block') {
        var bulkHeight = $('.dataTables_wrapper').find('.table-filter').height() + 40;
        $('.bulk').css('height', bulkHeight + 'px');
    }

    // Middle content height adjustment
    if ($('.middle-content').get(0)) {
        $('.middle-content').css('marginTop', nonNegative($(window).height() - $('.middle-content').height()) / 3 + 'px');
    }
}

/**
 * Get datatabe per page item.
 *
 * @param {integer} defaultPerPage
 *
 * @return {integer}
 */
function getPerPageLength (defaultPerPage) {
    defaultPerPage   = parseInt($.trim(defaultPerPage), 10);
    var validLength  = [10, 25, 50, 75, 100];
    var windowHeight = $(window).height();

    if (defaultPerPage !== 0 && validLength.indexOf(defaultPerPage) !== -1) {
        return defaultPerPage;
    }

    if (windowHeight <= 900) {
        return 10;
    } else if (windowHeight > 900 && windowHeight <= 1900) {
        return 25;
    } else if (windowHeight > 1900 && windowHeight <= 3800) {
        return 50;
    } else if (windowHeight > 3800 && windowHeight <= 5700) {
        return 75;
    } else if (windowHeight > 5700) {
        return 100;
    } else {
        return 10;
    }
}

/**
 * Widget table ajax request to load data.
 *
 * @param {DOMElement} widgetTable
 *
 * @return {void}
 */
function ajaxWidgetTableData (widgetTable) {
    var scrollTop = widgetTable.scrollTop();
    var skipLoad  = widgetTable.attr('data-skipload');
    var loadUrl   = widgetTable.data('url');

    if (skipLoad !== 'false') {
        widgetTable.addClass('loading');

        $.ajax({
            type     : 'POST',
            url      : loadUrl,
            data     : { skip: skipLoad },
            dataType : 'JSON',
            success  : function (data) {
                widgetTable.removeClass('loading');

                if (data.status === true) {
                    // Render all items within table > tr
                    $(data.html).each(function (index, tr) {
                        if (!widgetTable.find('tr#' + $(tr).attr('id')).size()) {
                            $(tr).hide().appendTo('#' + widgetTable.find('.table tbody').attr('id')).fadeIn(550);
                        }
                    });

                    $('[data-toggle="tooltip"]').tooltip();

                    // Get to know any more items exists to load or nothing left to load
                    if (data.loadStatus) {
                        widgetTable.attr('data-skipload', data.nextSkip);
                    } else {
                        widgetTable.attr('data-skipload', 'false');
                    }
                } else {
                    if (!$(".alert.alert-danger[role='alert']").get(0)) {
                        $.notify({ message: 'Something went wrong.' }, globalVar.dangerNotify);
                    }

                    widgetTable.animate({ scrollTop: scrollTop });
                }
            },
            error : function (jqXHR, textStatus, errorThrown) {
                ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'notify', false, 2500);
            }
        });
    }
}

/**
 * Ajax auto refresh request to load dashboard content.
 *
 * @return {void}
 */
function ajaxAutoRefresh () {
    if ($('.auto-refresh').get(0)) {
        $('.auto-refresh').addClass('loading');

        $.ajax({
            type     : 'POST',
            url      : $('.auto-refresh').data('url'),
            dataType : 'JSON',
            success  : function (data) {
                $('.auto-refresh').removeClass('loading');
                $('.auto-refresh').attr('data-refresh', data.autoRefresh);
                $('.auto-refresh').attr('data-interval', data.interval);
                var $dataObj = $(data.html);

                if ($dataObj.length) {
                    $('.auto-refresh').html($dataObj);
                    // Initialize essential plugins
                    initChart();
                    calendarInit();
                    heightAdjustment();
                    perfectScrollbarInit();
                    counterUpInit('.auto-refresh');
                    $('[data-toggle="tooltip"]').tooltip();
                    nicescrollResize('html');
                }
            },
            error : function (jqXHR, textStatus, errorThrown) {
                ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
            }
        });
    }
}

/**
 * Kanban board left|right move effect.
 *
 * @param {DOMElement} container
 *
 * @return {void}
 */
function kanbanLeftRight (container) {
    var containerArrowLeft   = $(container.parent('.funnel-wrap').find('.funnel-container-arrow.left'));
    var containerArrowRight  = $(container.parent('.funnel-wrap').find('.funnel-container-arrow.right'));
    var containerWidth       = container.innerWidth();
    var containerLeftPos     = container.scrollLeft();
    var containerRightPos    = containerLeftPos + containerWidth;
    var stagesWidth          = container.children('.funnel-stage').size() * 300;
    var containerMaxRightPos = stagesWidth > containerWidth ? stagesWidth : containerWidth;

    if (containerLeftPos < 3) {
        containerArrowLeft.css('left', '-70px');
    } else {
        containerArrowLeft.css('left', '-35px');
    }

    if (containerRightPos < (containerMaxRightPos - 3)) {
        containerArrowRight.css('right', '-35px');
    } else {
        containerArrowRight.css('right', '-70px');
    }
}

/**
 * Kanban card drag moves effect.
 *
 * @param {Object}     ui
 * @param {DOMElement} funnelStage
 *
 * @return {void}
 */
function kanbanDragMove (ui, funnelStage) {
    var container           = $(ui.item.closest('.funnel-container'));
    var containerLeftPos    = container.scrollLeft();
    var containerRightPos   = containerLeftPos + container.innerWidth();
    var totalStage          = container.children('.funnel-stage').size();
    var prevStage           = funnelStage.prevAll('.funnel-stage').size();
    var funnelStageLeftPos  = prevStage * 300;
    var funnelStageRightPos = funnelStageLeftPos + 300;

    if (containerLeftPos > funnelStageLeftPos) {
        var goLeftVal = nonNegative(funnelStageLeftPos - 100);
        container.animate({ scrollLeft: goLeftVal }, 1000);
    } else if (containerRightPos < funnelStageRightPos) {
        var goRightVal = containerLeftPos + 300;

        if (totalStage === (prevStage + 1)) {
            goRightVal = nonNegative(funnelStageRightPos - container.innerWidth());
            container.animate({ scrollLeft: goRightVal }, 1000);
        } else {
            container.animate({ scrollLeft: goRightVal }, 1000);
        }
    }
}

/**
 * Ajax request to update the kanban card element and post update effect.
 *
 * @param {Object} ui
 *
 * @return {void}
 */
function kanbanUpdate (ui) {
    var card       = $(ui.item.context);
    var cardId     = card.find('input').val();
    var prevLi     = card.prev('li');
    var prevLiSize = card.prev('li').size();
    var pickedId   = prevLiSize ? prevLi.find('input').val() : 0;
    var container  = $(ui.item.closest('.funnel-container'));
    var source     = container.data('source');
    var field      = container.data('stage');
    var orderType  = container.data('order');
    var stage      = card.find('input').attr('data-stage');
    var parent     = typeof container.data('parent') !== 'undefined' ? container.data('parent') : null;
    var parentId   = typeof container.data('parent-id') !== 'undefined' ? parseInt(container.data('parent-id'), 10) : null;

    $.ajax({
        type : 'GET',
        url  : globalVar.baseAdminUrl + '/kanban-reorder',
        data : {
            source    : source,
            id        : cardId,
            picked    : pickedId,
            field     : field,
            stage     : stage,
            ordertype : orderType,
            parent    : parent,
            parentid  : parentId
        },
        dataType : 'JSON',
        success  : function (data) {
            if (data.status === true) {
                // Render updated kanban stage total items no. in stage header
                kanbanCountResponse(data);

                $.each(data.realtime, function (index, value) {
                    $("*[data-realtime='" + index + "']").html(value);
                });
            } else {
                notifyErrors(data.errors, true);
            }
        },
        error : function (jqXHR, textStatus, errorThrown) {
            ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
        }
    });
}

/**
 * Kanban card update response effect on kanban board.
 *
 * @param {Object} data
 *
 * @return {void}
 */
function kanbanUpdateResponse (data) {
    $.each(data.kanban, function (stage, cards) {
        $.each(cards, function (cardId, card) {
            var cardExists = $(".funnel-stage[id='" + stage + "']").find('#' + cardId);

            // If the kanban card changed stage, then the card will be place top in the new stage list
            if (cardExists.length === 0) {
                $('.funnel-stage #' + cardId).remove();
                $(".funnel-stage[id='" + stage + "'] .kanban-list").prepend(card);
            } else {
                cardExists.html(card);
            }
        });
    });

    // Render updated kanban stage total items no. in stage header
    kanbanCountResponse(data);
    $('[data-toggle="tooltip"]').tooltip();
}

/**
 * Kanban stage total items no. update according to current stage items.
 *
 * @param {Object} data
 *
 * @return {void}
 */
function kanbanCountResponse (data) {
    var kanbanCount = data.kanbanCount;

    if (typeof $('.funnel-container').attr('data-parent') !== 'undefined' &&
        typeof data.parentKanbanCount !== 'undefined' && data.parentKanbanCount != null
    ) {
        kanbanCount = data.parentKanbanCount[$('.funnel-container').attr('data-parent')];
    }

    $.each(kanbanCount, function (index, value) {
        var dataLoad = parseInt(value, 10) > 0 ? 'true' : 'false';
        $(".funnel-stage[id='" + index + "']").find('.funnel-stage-header .title .count').html(value);
        $(".funnel-stage[id='" + index + "']").attr('data-load', dataLoad);
    });
}

/**
 * "Filter View" change effect on kanban board.
 *
 * @param {Object}  data
 * @param {boolean} reverseLoad
 *
 * @return {void}
 */
function kanbanFilterViewChange (data, reverseLoad) {
    if (typeof data.kanbanCount !== 'undefined' && Object.keys(data.kanbanCount).length > 0) {
        kanbanCountResponse(data);
        $('.funnel-card-container').animate({ scrollTop: 0 });
        $('.funnel-container .ui-sortable-handle').remove();
        $('.funnel-container .kanban-list li.disable').remove();
        var funnelCardContainer = reverseLoad === false ? $('.funnel-card-container') : $($('.funnel-card-container').get().reverse());

        funnelCardContainer.each(function (index, ui) {
            var cardContainer = $(this);
            var delayTime     = index < 3 ? 1000 : 1500;

            setTimeout(function () {
                if (cardContainer.find('.kanban-list').sortable('instance') !== 'undefined') {
                    ajaxKanbanCard(cardContainer, 15, true);
                }
            }, ((index + 1) * delayTime));
        });
    }
}

/**
 * Ajax request to load kanban stage cards.
 *
 * @param {DOMElement} cardContainer
 * @param {number}     takeLimit
 * @param {bool|null}  fromStart
 *
 * @return {void}
 */
function ajaxKanbanCard (cardContainer, takeLimit, fromStart) {
    var scrollTop     = cardContainer.scrollTop();
    var funnelStage   = cardContainer.closest('.funnel-stage');
    var loadStatus    = funnelStage.attr('data-load');
    var loadUrl       = funnelStage.data('url');
    var reqData       = kanbanCardReqData(cardContainer);
    reqData.takeLimit = takeLimit;
    reqData.fromStart = fromStart;

    if (loadStatus === 'true') {
        cardContainer.closest('.funnel-stage').addClass('loading');
        funnelStage.find('.kanban-list').css('height', (funnelStage.find('.kanban-list .li-container').height() + 50) + 'px');

        $.ajax({
            type     : 'POST',
            url      : loadUrl,
            data     : reqData,
            dataType : 'JSON',
            success  : function (data) {
                funnelStage.removeClass('loading');

                if (data.status === true) {
                    // Render all loaded items
                    $(data.html).each(function (index, card) {
                        if (!funnelStage.find('li#' + $(card).attr('id')).size()) {
                            $(card).hide().appendTo('#' + funnelStage.find('.kanban-list .li-container').attr('id')).fadeIn(550);
                        }
                    });

                    $('[data-toggle="tooltip"]').tooltip();

                    // Update sortable plugins after loaded new kanban cards
                    funnelStage.find('.kanban-list').each(function (index, ui) {
                        if ($(ui).hasClass('ui-sortable')) {
                            $(ui).sortable('refresh');
                        }
                    });

                    // If no cards left to load for next times
                    if (!data.loadStatus) {
                        funnelStage.attr('data-load', 'false');
                    }
                } else {
                    if (!$(".alert.alert-danger[role='alert']").get(0)) {
                        $.each(data.errors, function (index, value) {
                            $.notify({ message: value }, globalVar.dangerNotify);
                        });

                        if (data.errors == null) {
                            $.notify({ message: 'Something went wrong.' }, globalVar.dangerNotify);
                        }
                    }

                    cardContainer.animate({ scrollTop: scrollTop });
                }

                funnelStage.find('.kanban-list').css('height', funnelStage.find('.kanban-list .li-container').height() + 'px');
            },
            error : function (jqXHR, textStatus, errorThrown) {
                // ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'notify', false, 2500);
            }
        });
    } else {
        funnelStage.find('.kanban-list').css('height', funnelStage.find('.kanban-list .li-container').height() + 'px');
    }
}

/**
 * Get kanban stage all card elements value.
 *
 * @param {DOMElement} cardContainer
 *
 * @return {Object}
 */
function kanbanCardReqData (cardContainer) {
    var reqData = {};
    var type = cardContainer.data('card-type');
    reqData.stageId = cardContainer.closest('.funnel-stage').data('stage');
    reqData.ids = cardContainer.find("[data-init-stage='" + reqData.stageId + "'] input[name='positions[]']").map(function () {
        return $(this).val();
    }).get();

    return reqData;
}

/**
 * CSS style change to adjust with responsive elements.
 *
 * @return {void}
 */
function responsiveMediaQuery () {
    if ($('.nav-link.expand').css('display') === 'block') {
        if ($('nav').hasClass('compress')) {
            if ($('logo').hasClass('compress') === false) {
                $('.header-logo').addClass('compress');
            }
        }

        $('.nicescroll-rails').eq(0).css('width', 5 + 'px');
        $('.nicescroll-cursors').eq(0).css('width', 5 + 'px');
    }

    if ($('.nav-link.expand').css('display') === 'none') {
        $('.nicescroll-rails').css('width', 0 + 'px');
        $('.nicescroll-cursors').css('width', 0 + 'px');
    }
}

/**
 * CSS style change when load page to adjust with responsive elements.
 *
 * @return {void}
 */
function responsiveMediaQueryOnLoad () {
    if ($('.nav-link.expand').css('display') === 'block') {
        $('.nicescroll-rails').eq(0).css('width', 5 + 'px');
        $('.nicescroll-cursors').eq(0).css('width', 5 + 'px');
    }

    if ($('.nav-link.expand').css('display') === 'none') {
        $('.header-logo').removeClass('compress');
        $('nav').removeClass('compress');
        $('.header-nav').removeClass('expand');
        $('main').removeClass('expand');
        $('.header-logo').removeAttr('style');
        $('.nicescroll-rails').css('width', 0 + 'px');
        $('.nicescroll-cursors').css('width', 0 + 'px');
    }
}

/**
 * Set delay time for hiding modal.
 *
 * @param {string} modalId
 * @param {number} delayVal
 *
 * @return {void}
 */
function delayModalHide (modalId, delayVal) {
    setTimeout(function () {
        $(modalId).modal('hide');
    }, parseInt(delayVal * 1000, 10));
}

/**
 * Remove all unnecessary temporary files from dropzone in a modal.
 *
 * @param {DOMElement} modalClose
 *
 * @return {void}
 */
function cleanDropzoneTempFiles (modalClose) {
    var modal    = $(modalClose.closest('.modal'));
    var dropzone = modal.find('.dropzone');

    if (dropzone.length === 1) {
        // globalVar defined in partials/footer.blade.php
        globalVar.dropzone[dropzone.attr('data-identifier')].removeAllFiles(true);
    }
}

/**
 * Initialize perfect scroll plugin in select2 box.
 *
 * @return {void}
 */
function initSelect2PerfectScroll () {
    if ($('.select2-results__options').get(0) &&
        typeof $('.select2-results__options .ps__rail-x').get(0) === 'undefined'
    ) {
        // globalVar defined in partials/footer.blade.php.
        // PerfectScrollbar defined in plugins/perfectscrollbar.
        globalVar.psSelect2 = new PerfectScrollbar($('.select2-results__options').get(0), {
            wheelSpeed         : 2,
            wheelPropagation   : true,
            minScrollbarLength : 50
        });
    }
}

/**
 * Initialize perfect scroll plugin in opened select2 box.
 *
 * @return {void}
 */
function initOpenSelect2PerfectScroll () {
    if ($('.select2-results__options').get(0) &&
        typeof $('.select2-results__options .ps__rail-x').get(0) === 'undefined'
    ) {
        var ul         = $($('.select2-results__options').get(0));
        var liSelected = $(ul.find("li[aria-selected='true']").get(0));
        var prevLiSize = liSelected.prevAll('li').size();
        var top        = (prevLiSize - 1) * 30.55;

        // PerfectScrollbar defined in plugins/perfectscrollbar
        globalVar.psSelect2 = new PerfectScrollbar($('.select2-results__options').get(0), {
            wheelSpeed         : 2,
            wheelPropagation   : true,
            minScrollbarLength : 50
        });

        if (prevLiSize > 5 && ul.scrollTop() === 0) {
            ul.animate({ scrollTop: top });
        }
    }
}

/**
 * Destroy select2 perfect scroll.
 *
 * @return {void}
 */
function destroySelect2PerfectScroll () {
    if (typeof globalVar.psSelect2 !== 'undefined' && globalVar.psSelect2 !== null) {
        globalVar.psSelect2.destroy();
        globalVar.psSelect2 = null;
    }
}

/**
 * Initialize all necessary plugin.
 *
 * @return {void}
 */
function pluginInit () {
    select2PluginInit();
    dropzoneInit();

    if ($('.datepicker').get(0)) {
        $('.datepicker').not('.only-view').datepicker({
            format : 'yyyy-mm-dd'
        });

        $(document).on('click', '.datepicker', function () {
            $(this).datepicker('update', $(this).val());
        });
    }

    if ($('.datetimepicker').get(0)) {
        $('.datetimepicker').datetimepicker({
            format         : 'Y-m-d h:i A',
            formatTime     : 'h:i A',
            validateOnBlur : false
        });
    }

    $('[data-toggle="tooltip"]').tooltip();
}

/**
 * Initialize "counter up" plugin.
 *
 * @param {string|null} containerTag
 *
 * @return {void}
 */
function counterUpInit (containerTag) {
    var searchCounter = containerTag === null ? '.counter' : containerTag + ' ' + '.counter';

    // Initialize "counter up" only tablet, laptop, and desktop
    if ($(searchCounter).get(0) && $(window).width() >= 768) {
        $(searchCounter).each(function (index, ui) {
            $(ui).counterUp();
        });
    }
}

/**
 * Initialize perfect scrollbar plugin.
 *
 * @return {void}
 */
function perfectScrollbarInit () {
    if (typeof globalVar.perfectscroll === 'undefined') {
        globalVar.perfectscroll = [];
    }

    globalVar.perfectscroll.ps         = [];
    globalVar.perfectscroll.psBox      = [];
    globalVar.perfectscroll.psXBox     = [];
    globalVar.perfectscroll.psDropdown = [];

    // PerfectScrollbar defined in plugins/perfectscrollbar
    if ($('.perfectscroll').get(0)) {
        $('.perfectscroll').each(function (index) {
            globalVar.perfectscroll.ps[index] = new PerfectScrollbar($('.perfectscroll').get(index));
        });
    }

    if ($('.scroll-dropdown').get(0)) {
        $('.scroll-dropdown').each(function (index) {
            globalVar.perfectscroll.psDropdown[index] = new PerfectScrollbar($('.scroll-dropdown').get(index));
        });
    }

    if ($('.scroll-box-x').get(0)) {
        $('.scroll-box-x').each(function (index) {
            globalVar.perfectscroll.psXBox[index] = new PerfectScrollbar($('.scroll-box-x').get(index));
        });
    }

    if ($('.scroll-box').get(0)) {
        $('.scroll-box').each(function (index) {
            globalVar.perfectscroll.psBox[index] = new PerfectScrollbar($('.scroll-box').get(index), { minScrollbarLength: 50 });
        });

        $('.scroll-box').animate({ scrollTop: 0 });
    }

    // Initialize perfectScroll in widget table and bind ajax request to load new data event
    $('.widget-table-box').each(function (index, ui) {
        this.addEventListener('ps-y-reach-end', function () {
            if ($(this).attr('data-skipload') !== 'false' && !$(this).hasClass('loading')) {
                ajaxWidgetTableData($(this));
            }
        });

        this.addEventListener('ps-y-reach-start', function () {
            $(this).removeClass('loading');
        });

        this.addEventListener('ps-scroll-up', function () {
            $(this).removeClass('loading');
        });
    });

    $('.widget.stream .timeline').each(function (index, ui) {
        this.addEventListener('ps-y-reach-end', function () {
            var loadTimeline = $($(this).find('.load-timeline'));
            var timelineInfo = $(loadTimeline.closest('.timeline-info'));

            if (!timelineInfo.hasClass('disable') && !timelineInfo.hasClass('loading')) {
                loadTimeline.trigger('click');
            }
        });
    });
}

/**
 * Resize nicescroll element according to current height and width
 *
 * @param {DOMElement} ui
 *
 * @return {void}
 */
function nicescrollResize (ui) {
    if ($(ui).css('overflowY') === 'hidden') {
        $(ui).getNiceScroll().resize();
    }
}

/**
 * Initialize a sortable plugin in the kanban board.
 *
 * @return {void}
 */
function sortableInit () {
    if ($('.kanban-list').get(0)) {
        $('.funnel-card-container').animate({ scrollTop: 0 });
        $('.funnel-container').animate({ scrollLeft: 0 });
        $('.breadcrumb select[data-kanban-select]').attr('disabled', true);

        var initialStage = null;
        var overStage = null;
        var uiArray = [];

        $('.kanban-list').sortable({
            start : function (event, ui) {
                initialStage = ui.item.closest('.funnel-stage').attr('id');
                ajaxKanbanCard(ui.item.closest('.funnel-card-container'), 10, null);
            },
            change : function (event, ui) {
                var funnelContainer      = ui.item.closest('.funnel-container');
                var highlightPlaceholder = funnelContainer.find('.ui-state-highlight');
                var funnelCardContainer  = $(highlightPlaceholder.closest('.funnel-card-container'));
                var funnelHeight         = Math.floor(funnelCardContainer.height());
                var liContainerHeight    = Math.floor(funnelCardContainer.find('.li-container').height());
                var animateTime          = liContainerHeight * 4;

                funnelContainer.find('.funnel-card-container').stop(true);

                if (highlightPlaceholder.offset().top < 275) {
                    funnelCardContainer.animate({ scrollTop: 0 }, animateTime);
                }

                if (highlightPlaceholder.offset().top > (funnelHeight - 150)) {
                    funnelCardContainer.animate({
                        scrollTop : nonNegative(liContainerHeight - funnelHeight)
                    }, animateTime);
                }
            },
            over : function (event, ui) {
                uiArray = [];
                overStage = $($(this).closest('.funnel-stage')).attr('id');

                if (initialStage !== overStage) {
                    kanbanDragMove(ui, $($(this).closest('.funnel-stage')));
                }
            },
            receive : function (event, ui) {
                var funnelStage = $($(this).closest('.funnel-stage'));
                var funnelStageVal = funnelStage.data('stage');
                $(ui.item.context).find('input').attr('data-stage', funnelStageVal);

                if ($(ui.item.context).find('[data-stage-field]').get(0)) {
                    $(ui.item.context).find('[data-stage-field]').html(funnelStage.data('display'));
                }

                ajaxKanbanCard(ui.item.closest('.funnel-card-container'), 10, null);
            },
            update : function (event, ui) {
                uiArray.push(ui);
                var uiArrayCount = uiArray.length;
                var currentStage = ui.item.closest('.funnel-stage').attr('id');
                ui.item.closest('.funnel-container').find('.funnel-card-container').stop(true);

                if (initialStage === currentStage) {
                    kanbanUpdate(ui);
                } else if (uiArrayCount > 1) {
                    kanbanUpdate(uiArray.last());
                }
            },
            connectWith : '.kanban-list',
            appendTo    : '.funnel-container',
            helper      : 'clone',
            placeholder : 'ui-state-highlight',
            revert      : true,
            items       : 'li:not(.disable)'
        }).disableSelection();
    }

    // Bind event to every kanban stage to load items|cards by ajax request
    $('.funnel-card-container').each(function (index, ui) {
        this.addEventListener('ps-y-reach-end', function () {
            ajaxKanbanCard($(this), 10, null);
        });

        this.addEventListener('ps-y-reach-start', function () {
            $(this).closest('.funnel-stage').removeClass('loading');
        });

        this.addEventListener('ps-scroll-up', function () {
            $(this).closest('.funnel-stage').removeClass('loading');
        });

        var cardContainer = $(this);
        var delayTime     = index < 3 ? 175 : 700;

        setTimeout(function () {
            if (cardContainer.find('.kanban-list').sortable('instance') !== 'undefined') {
                ajaxKanbanCard(cardContainer, 10, null);
            }

            if ($('.funnel-card-container').size() === (index + 1)) {
                $('.breadcrumb select[data-kanban-select]').attr('disabled', false);
            }
        }, ((index + 1) * delayTime));
    });
}

/**
 * Initialize atwho.js  plugin.
 *
 * @return {void}
 */
function atWhoInit () {
    if ($('.atwho-inputor').get(0)) {
        $('.atwho-inputor').each(function (index) {
            var thisAtWho = $($('.atwho-inputor').get(index));
            var atWhoData = thisAtWho.attr('at-who').split(',');

            // After typing "@" users will get a dropdown list to choose the mentioned user
            thisAtWho.atwho({
                at   : '@',
                data : atWhoData
            });
        });
    }
}

/**
 * Initialize calendar plugin.
 *
 * @return {void}
 */
function calendarInit () {
    if ($('.calendar').get(0)) {
        $('.none').hide();

        $('.calendar').each(function (index, ui) {
            var dataUrl    = $(ui).attr('data-url');
            var createItem = typeof $(ui).data('viewonly') === 'undefined';

            $(ui).fullCalendar({
                header : {
                    left   : 'prev,next today',
                    center : 'title',
                    right  : 'month,agendaWeek,agendaDay'
                },
                selectable   : true,
                selectHelper : true,
                editable     : true,
                eventLimit   : true,
                views        : {
                    timeGrid : { eventLimit: 3 },
                    week     : { eventLimit: 10 },
                    day      : { eventLimit: 10 }
                },
                events : {
                    url  : dataUrl,
                    type : 'POST',
                    data : { start_date: null, end_date: null }
                },
                select : function (start, end) {
                    if (createItem) {
                        // addNewEvent function defined in modals/create.blade.php
                        addNewEvent();

                        // moment defined in plugins/moment
                        var startDate     = moment(start).format('YYYY-MM-DD');
                        var startDateTime = moment(start).hours(10).format('YYYY-MM-DD hh:mm A');
                        var endDate       = moment(end).add('-1', 'days').format('YYYY-MM-DD');
                        var endDateTime   = moment(end).add('-1', 'days').hours(11).format('YYYY-MM-DD hh:mm A');

                        // Auto-update start and end date's time from the calendar date range
                        if ($("input[name='start_date']").hasClass('datetimepicker')) {
                            $("input[name='start_date']").val(startDateTime);
                            $("input[name='end_date']").val(endDateTime);
                            $("input[name='due_date']").val(endDateTime);
                        }

                        // Auto-update start and end date from the calendar date range
                        if ($("input[name='start_date']").hasClass('datepicker')) {
                            $("input[name='start_date']").val(startDate);

                            if (startDate !== endDate) {
                                $("input[name='end_date']").val(endDate);
                                $("input[name='due_date']").val(endDate);
                            }
                        }

                        if ($('input.calendar-start-date').get(0)) {
                            $('input.calendar-start-date').val(startDate);
                        }

                        if ($('input.calendar-end-date').get(0)) {
                            $('input.calendar-end-date').val(endDate);
                        }
                    }
                },
                eventDrop : function (event, delta, revertFunc) {
                    var startDate = event.start.format();
                    var endDate   = startDate;

                    if (event.end != null) {
                        endDate = moment(event.end).add('-1', 'days').format('YYYY-MM-DD');
                    }

                    $.ajax({
                        type : 'POST',
                        url  : event.position_url,
                        data : { id: event.id, start: startDate, end: endDate }
                    });
                },
                eventClick : function (event, jsEvent, view) {
                    if (event.auth_can_edit) {
                        var id        = event.id;
                        var data      = { id: id, html: true };
                        var url       = event.base_url + '/' + id + '/edit';
                        var updateUrl = event.base_url + '/' + id;

                        // getCommonEdit function defined in modals/common-edit.blade.php
                        getCommonEdit(id, data, event.item, url, updateUrl, event.modal_size, null, '#common-edit', '#common-edit-content');
                    } else {
                        window.location.href = event.show_route;
                    }
                },
                eventAfterRender : function (event, element, view) {
                    var requireWidth = event.title.length * 5;

                    if ($(element).width() > 0 && $(element).width() < requireWidth) {
                        $(element).attr('data-original-title', event.title);
                        $(element).attr('data-html', 'true');
                        $(element).tooltip({ container: 'body' });
                    }

                    $(element).attr('data-url', event.show_route);
                    $(element).attr('data-modal', event.auth_can_edit);
                }
            });
        });
    }
}

/**
 * Initialize the dropzone plugin to upload files.
 *
 * @return {void}
 */
function dropzoneInit () {
    if ($('.dropzone').get(0)) {
        $('.dropzone').each(function (index) {
            var thisDropzone = $($('.dropzone').get(index));

            if (!thisDropzone.hasClass('dz-clickable')) {
                var dropzoneId        = '#' + thisDropzone.attr('id');
                var dropzoneContainer = $(thisDropzone.closest('.dropzone-container'));
                var previewContainer  = '#' + thisDropzone.attr('data-preview');
                var url               = thisDropzone.data('url');
                var removeUrl         = thisDropzone.data('removeurl');
                var linked            = thisDropzone.attr('data-linked');
                var maxFiles          = typeof thisDropzone.attr('max-files') !== 'undefined' ? parseInt(thisDropzone.attr('max-files'), 10) : 10;
                var dzError           = dropzoneContainer.find(".validation-error[field='dropzone-error']");
                var identifier        = 'dropzone-' + index + Math.floor((Math.random() * 10000000) + 1);
                thisDropzone.attr('data-identifier', identifier);

                $(dropzoneId).dropzone({
                    url                : url,
                    dictDefaultMessage : 'Drop max ' + maxFiles + ' files here or click to upload.',
                    addRemoveLinks     : true,
                    timeout            : 300000,
                    maxFiles           : maxFiles,
                    previewsContainer  : previewContainer,
                    init               : function () {
                        // globalVar defined in partials/footer.blade.php
                        globalVar.dropzone[identifier] = this;

                        // Added new file and get a proper icon
                        this.on('addedfile', function (file) {
                            var icon = getFileIconHtml(file.name);
                            $($(file.previewElement).find('.dz-details')).prepend(icon);
                        });

                        // Ajax request to remove the file
                        this.on('removedfile', function (file) {
                            var fileName = $($(file.previewElement).find('.dz-filename')).attr('data-original');
                            dropzoneContainer.find("input[name='uploaded_files[]'][value='" + fileName + "']").remove();

                            $.ajax({
                                type : 'POST',
                                data : { linked: linked, uploaded_files: fileName },
                                url  : removeUrl
                            });
                        });

                        // Show error message if total files cross the limit of max files
                        this.on('maxfilesreached', function (files) {
                            if (files.length === (maxFiles + 1)) {
                                var errorMsg = 'The uploaded files may not have more than ' + maxFiles + ' items.';

                                if (dzError.css('display') === 'none') {
                                    dzError.text(errorMsg);
                                    dzError.css('display', 'block');

                                    setTimeout(function () {
                                        dzError.fadeOut(1500);
                                    }, 1500);
                                }
                            }
                        });

                        this.on('maxfilesexceeded', function (file) {
                            this.removeFile(file);
                        });

                        // Show error message and remove the file
                        this.on('error', function (file, errormessage, xhr) {
                            if (typeof xhr !== 'undefined') {
                                var thisDz = this;
                                $(file.previewElement).addClass('dz-error');
                                $($(file.previewElement).find('.dz-error-message span')).html('Upload failed');

                                $(file.previewElement).fadeOut(1750, function () {
                                    thisDz.removeFile(file);
                                });
                            }
                        });
                    },
                    sending : function (file, xhr, formData) {
                        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                        formData.append('linked', linked);
                    },
                    success : function (data, response) {
                        textOverflowTitle('.dz-filename span');
                        $($(data.previewElement).find('.dz-filename')).attr('data-original', response.fileName);
                        dropzoneContainer.append($('<input>', {
                            type : 'hidden',
                            name : 'uploaded_files[]',
                            val  : response.fileName
                        }));
                    }
                });
            }
        });
    }

    if ($('.modalfree-dropzone').get(0)) {
        $('.modalfree-dropzone').each(function (index) {
            var thisDropzone = $($('.modalfree-dropzone').get(index));

            if (!thisDropzone.hasClass('dz-clickable')) {
                var dropzoneId         = 'modalfree-dropzone-' + index + Math.floor((Math.random() * 10000000) + 1);
                var dropzoneContainer  = $(thisDropzone.closest('.dropzone-container'));
                var fromGroup          = $(dropzoneContainer.closest('.form-group'));
                var previewContainer   = $(dropzoneContainer.find('.dz-preview-container'));
                var previewContainerId = 'modalfree-dropzone-preview-' + index + Math.floor((Math.random() * 10000000) + 1);
                var url                = thisDropzone.data('url');
                var removeUrl          = thisDropzone.data('removeurl');
                var linked             = thisDropzone.data('linked');
                var maxFiles           = typeof thisDropzone.attr('max-files') !== 'undefined' ? parseInt(thisDropzone.attr('max-files'), 10) : 10;

                thisDropzone.attr('id', dropzoneId);
                previewContainer.attr('id', previewContainerId);

                thisDropzone.dropzone({
                    url                : url,
                    dictDefaultMessage : 'Drop max ' + maxFiles + ' files here or click to upload.',
                    addRemoveLinks     : true,
                    timeout            : 300000,
                    maxFiles           : maxFiles,
                    previewsContainer  : '#' + previewContainerId,
                    init               : function () {
                        // globalVar defined in partials/footer.blade.php
                        globalVar.dropzone[dropzoneId] = this;

                        this.on('addedfile', function (file) {
                            var addStatus = true;

                            if (dropzoneContainer.hasClass('update-dz')) {
                                var existedUploaded = dropzoneContainer.find("input[name='uploaded_files[]']").size();

                                if (existedUploaded >= maxFiles) {
                                    addStatus = false;
                                    this.removeFile(file);
                                    var errorMsg = 'The uploaded files may not have more than ' + maxFiles + ' items.';
                                    if (!$(".alert.alert-danger[role='alert']").get(0)) {
                                        $.notify({ message: errorMsg }, globalVar.dangerNotify);
                                    }
                                }
                            }

                            if (addStatus) {
                                fromGroup.find('.btn').not('.cancel').attr('disabled', true);
                                var icon = getFileIconHtml(file.name);
                                $($(file.previewElement).find('.dz-details')).prepend(icon);
                            }
                        });

                        // Ajax request to remove the file
                        this.on('removedfile', function (file) {
                            var fileName = $($(file.previewElement).find('.dz-filename')).attr('data-original');
                            dropzoneContainer.find("input[name='uploaded_files[]'][value='" + fileName + "']").remove();

                            $.ajax({
                                type : 'POST',
                                data : { linked: linked, uploaded_files: fileName },
                                url  : removeUrl
                            });
                        });

                        // Show error message if total files cross the limit of max files
                        this.on('maxfilesreached', function (files) {
                            if (files.length === (maxFiles + 1)) {
                                var errorMsg = 'The uploaded files may not have more than ' + maxFiles + ' items.';

                                if (!$(".alert.alert-danger[role='alert']").get(0)) {
                                    $.notify({ message: errorMsg }, globalVar.dangerNotify);
                                }
                            }
                        });

                        this.on('maxfilesexceeded', function (file) {
                            this.removeFile(file);
                        });

                        // Show error message and remove the file
                        this.on('error', function (file, errormessage, xhr) {
                            if (typeof xhr !== 'undefined') {
                                var thisDz = this;

                                $(file.previewElement).addClass('dz-error');
                                $($(file.previewElement).find('.dz-error-message span')).html('Upload failed');
                                $(file.previewElement).fadeOut(1750, function () {
                                    thisDz.removeFile(file);
                                });
                            }
                        });

                        this.on('success', function (file) {
                            if (dropzoneContainer.hasClass('update-dz')) {
                                var existedUploaded = dropzoneContainer.find("input[name='uploaded_files[]']").size();

                                if (existedUploaded > maxFiles) {
                                    this.removeFile(file);
                                    var errorMsg = 'You can not upload more than ' + maxFiles + ' files.';
                                    if (!$(".alert.alert-danger[role='alert']").get(0)) {
                                        $.notify({ message: errorMsg }, globalVar.dangerNotify);
                                    }
                                }
                            }
                        });

                        this.on('complete', function (file) {
                            if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                                fromGroup.find('.btn').not('.cancel').attr('disabled', false);
                            }
                        });
                    },
                    sending : function (file, xhr, formData) {
                        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                        formData.append('linked', linked);
                    },
                    success : function (data, response) {
                        nicescrollResize('html');
                        textOverflowTitle('.dz-filename span');
                        $($(data.previewElement).find('.dz-filename')).attr('data-original', response.fileName);
                        dropzoneContainer.append($('<input>', {
                            type : 'hidden',
                            name : 'uploaded_files[]',
                            val  : response.fileName
                        }));
                    }
                });
            }
        });
    }
}

/**
 * Initialize image cropper plugin.
 *
 * @param {DOMElement} cropper
 *
 * @return {void}
 */
function cropperInit (cropper) {
    var cropWrap = $(cropper.closest('.cropper-wrap'));

    setTimeout(function () {
        cropper.cropper({
            viewMode         : 1,
            aspectRatio      : 1 / 1,
            background       : false,
            movable          : false,
            zoomable         : false,
            zoomOnTouch      : false,
            zoomOnWheel      : false,
            minCropBoxWidth  : 150,
            minCropBoxHeight : 150,
            guides           : false,
            center           : false,
            rotatable        : false,
            autoCropArea     : 0.7,
            ready            : function (event) {
                var containerWidth  = cropWrap.find('.cropper-container').width();
                var containerHeight = cropWrap.find('.cropper-container').height();
                var canvasWidth     = cropWrap.find('.cropper-canvas').width();
                var canvasHeight    = cropWrap.find('.cropper-canvas').height();
                var left = (containerWidth - canvasWidth) / 2;
                var top  = (containerHeight - canvasHeight) / 2;

                cropWrap.find('.cropper-modal').css('width', Math.round(canvasWidth) + 'px');
                cropWrap.find('.cropper-modal').css('height', Math.round(canvasHeight) + 'px');
                cropWrap.find('.cropper-modal').css('left', Math.floor(left) + 'px');
                cropWrap.find('.cropper-modal').css('top', Math.floor(top) + 'px');
            },
            crop : function (event) {
                cropWrap.find("input[name='x']").val(parseInt(event.detail.x, 10));
                cropWrap.find("input[name='y']").val(parseInt(event.detail.y, 10));
                cropWrap.find("input[name='width']").val(parseInt(event.detail.width, 10));
                cropWrap.find("input[name='height']").val(parseInt(event.detail.height, 10));
            }
        });
    }, 500);
}

/**
 * File extension validator.
 *
 * @param {Object} file
 * @param {Array}  extensions
 *
 * @return {bool}
 */
function extensionValidation (file, extensions) {
    var filename       = file.name;
    var filetypeInfo   = file.type.split('/');
    var filetype       = filetypeInfo[filetypeInfo.length - 1];
    var extension      = filename.substr((filename.lastIndexOf('.') + 1)).toLowerCase();
    var validExtension = extensions.indexOf(extension) !== -1;
    var validType      = extensions.indexOf(filetype) !== -1;

    return (validExtension && validType);
}

/**
 * File size validator.
 *
 * @param {Object} file
 * @param {number} size
 *
 * @return {bool}
 */
function filesizeValidation (file, size) {
    return (file.size <= size);
}

/**
 * Get the file icon according to the file extension.
 *
 * @param {string} filename
 *
 * @return {string}
 */
function getFileIconHtml (filename) {
    var extension = filename.substr((filename.lastIndexOf('.') + 1)).toLowerCase();

    switch (extension) {
        case 'webp':
        case 'jpeg':
        case 'jpg':
        case 'png':
        case 'gif':
            return "<span class='icon image fa fa-file-image-o'></span>";
        case 'zip':
        case 'rar':
        case 'iso':
        case 'tar':
        case 'tgz':
        case 'apk':
        case 'dmg':
        case '7z':
            return "<span class='icon zip fa fa-file-zip-o'></span>";
        case 'docx':
        case 'doc':
            return "<span class='icon word fa fa-file-word-o'></span>";
        case 'xlsx':
        case 'xls':
        case 'csv':
        case 'ods':
            return "<span class='icon excel fa fa-file-excel-o'></span>";
        case 'pptx':
        case 'pptm':
        case 'ppt':
            return "<span class='icon powerpoint fa fa-file-powerpoint-o'></span>";
        case 'pdf':
            return "<span class='icon pdf fa fa-file-pdf-o'></span>";
        case 'wav':
        case 'wma':
        case 'mpc':
        case 'msv':
            return "<span class='icon audio fa fa-file-audio-o'></span>";
        case 'mp3':
        case 'm4a':
        case 'm4b':
        case 'm4p':
            return "<span class='icon audio fa fa-music'></span>";
        case 'mov':
        case 'mp4':
        case 'avi':
        case 'flv':
        case 'wmv':
        case 'swf':
        case 'mkv':
        case 'mpg':
            return "<span class='icon video fa fa-file-video-o'></span>";
        case 'txt':
            return "<span class='icon text fa fa-file-text-o'></span>";
        case 'html':
        case 'php':
            return "<span class='icon code fa fa-file-code-o'></span>";
        default :
            return "<span class='icon file fa fa-file-o'></span>";
    }
}

/**
 * Set a tooltip title if the text is overflow.
 *
 * @param {string} className
 *
 * @return {void}
 */
function textOverflowTitle (className) {
    $(className + ':not([data-checked="true"])').each(function (index, ui) {
        $(ui).attr('data-checked', 'true');

        if ($(ui).width() > $($(ui).parent()).width()) {
            $(ui).attr('title', $(ui).text());
            $(ui).attr('data-toggle', 'tooltip');
            $(ui).attr('data-placement', 'top');
            $(ui).tooltip();
            $(ui).attr('title', '');
        }
    });
}

/**
 * Reset radio typed checkbox.
 *
 * @return {void}
 */
function resetCheckboxRadio () {
    $('input:checkbox, input:radio').each(function (index, ui) {
        var checked = $(ui).attr('checked');

        if (checked) {
            $(ui).prop('checked', true);
        } else {
            $(ui).prop('checked', false);
        }
    });
}

/**
 * Initialize select2 plugin.
 *
 * @return {void}
 */
function select2PluginInit () {
    if ($('.select-type-single').get(0)) {
        $('.select-type-single').select2().on('select2:open', function () {
            initSelect2PerfectScroll();
        }).on('select2:close', function () {
            destroySelect2PerfectScroll();
        });
    }

    if ($('.select-type-single-b').get(0)) {
        $('.select-type-single-b').select2({
            minimumResultsForSearch : -1
        }).on('select2:open', function () {
            initSelect2PerfectScroll();
        }).on('select2:close', function () {
            destroySelect2PerfectScroll();
        });
    }

    if ($('.select-type-multiple').get(0)) {
        $('.select-type-multiple').select2({
            allowClear : true
        }).on('select2:open', function () {
            initSelect2PerfectScroll();
        }).on('select2:close', function () {
            destroySelect2PerfectScroll();
        });
    }

    if ($('.breadcrumb-select').get(0)) {
        $('.breadcrumb-select').select2({
            containerCssClass : 'breadcrumb-select-container',
            dropdownCssClass  : 'breadcrumb-select-dropdown',
            selectOnClose     : true
        }).on('select2:open', function () {
            initSelect2PerfectScroll();
        }).on('select2:close', function () {
            destroySelect2PerfectScroll();
        });
    }

    if ($('.white-select-type-single').get(0)) {
        $('.white-select-type-single').select2({
            containerCssClass : 'white-container',
            dropdownCssClass  : 'white-dropdown'
        }).on('select2:open', function () {
            initSelect2PerfectScroll();
        }).on('select2:close', function () {
            destroySelect2PerfectScroll();
        });
    }

    if ($('.white-select-single-clear').get(0)) {
        $('.white-select-single-clear').select2({
            containerCssClass : 'white-container',
            dropdownCssClass  : 'white-dropdown',
            allowClear        : true,
            placeholder       : function () {
                $(this).data('placeholder');
            }
        }).on('select2:open', function () {
            initSelect2PerfectScroll();
        }).on('select2:close', function () {
            destroySelect2PerfectScroll();
        });
    }

    if ($('.white-select-type-single-b').get(0)) {
        $('.white-select-type-single-b').select2({
            minimumResultsForSearch : -1,
            containerCssClass       : 'white-container',
            dropdownCssClass        : 'white-dropdown'
        }).on('select2:open', function () {
            initSelect2PerfectScroll();
        }).on('select2:close', function () {
            destroySelect2PerfectScroll();
        });
    }

    if ($('.white-select-type-multiple').get(0)) {
        $('.white-select-type-multiple').select2({
            containerCssClass : 'white-container',
            dropdownCssClass  : 'white-dropdown',
            allowClear        : true,
            placeholder       : function () {
                $(this).data('placeholder');
            }
        }).on('select2:open', function () {
            initSelect2PerfectScroll();
        }).on('select2:close', function () {
            destroySelect2PerfectScroll();
        });
    }

    if ($('.white-select-type-multiple-tags').get(0)) {
        $('.white-select-type-multiple-tags').select2({
            containerCssClass : 'white-container tags',
            dropdownCssClass  : 'white-dropdown tags',
            tags              : true,
            placeholder       : function () {
                $(this).data('placeholder');
            },
            language : {
                noResults : function (params) {
                    return 'Type to search';
                }
            }
        }).on('select2:open', function () {
            initSelect2PerfectScroll();
        }).on('select2:close', function () {
            destroySelect2PerfectScroll();
        });
    }

    if ($('.select2-selection--multiple').get(0)) {
        globalVar.psSelect2Multiple = [];

        $('.select2-selection--multiple').each(function (index, ui) {
            if ($(ui).find('.ps__rail-x').length === 0) {
                globalVar.psSelect2Multiple[index] = new PerfectScrollbar($(ui).get(0), {
                    wheelSpeed         : 2,
                    wheelPropagation   : true,
                    minScrollbarLength : 20
                });
            }
        });
    }
}

/**
 * Set limit of min and max value.
 *
 * @param {number}      value
 * @param {number|null} min
 * @param {number|null} max
 *
 * @return {number}
 */
function limitMinMax (value, min, max) {
    if (min !== null && value < min) {
        return min;
    }

    if (max !== null && value > max) {
        return max;
    }

    return value;
}

/**
 * Get the max element of an array.
 *
 * @param {Array} array
 *
 * @return {*}
 */
function arrayMax (array) {
    return Math.max.apply(Math, array);
}

/**
 * Get a min element of an array.
 *
 * @param {Array} array
 *
 * @return {*}
 */
function arrayMin (array) {
    return Math.min.apply(Math, array);
}

/**
 * Get the last element of an array.
 *
 * @return {*}
 */
Array.prototype.last = function () {
    return this[this.length - 1];
};

/**
 * Accept and get a non-negative number.
 *
 * @param {number} value
 *
 * @return {number}
 */
function nonNegative (value) {
    return Math.max(0, value);
}

/**
 * Get upper case first letter of a word.
 *
 * @param {string} string
 *
 * @return {string}
 */
function ucword (string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

/**
 * Reset all inputs of the comment form.
 *
 * @param {DOMElement} form
 * @param {boolean}    emptyfiles
 *
 * @return {void}
 */
function resetCommentForm (form, emptyfiles) {
    var dropzoneId = form.find('.modalfree-dropzone').attr('id');

    if (emptyfiles) {
        globalVar.dropzone[dropzoneId].files = [];
    } else {
        globalVar.dropzone[dropzoneId].removeAllFiles(true);
    }

    form.find('textarea').css('height', '34px');
    form.find('textarea').val('');
    form.find('.form-group.bottom').slideUp('fast');
    form.find('.dz-preview-container').html('');
    form.find("input[name='uploaded_files[]']").remove();
}

/**
 * Preview cropper image.
 *
 * @param {Object}     browse
 * @param {DOMElement} preview
 *
 * @return {void}
 */
function previewImg (browse, preview) {
    if (browse.files && browse.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            preview.attr('src', e.target.result);
        };

        reader.readAsDataURL(browse.files[0]);
    }
}

/**
 * Get error notification by using the notify plugin.
 *
 * @param {Object}  errors
 * @param {boolean} reload
 *
 * @return {void}
 */
function notifyErrors (errors, reload) {
    if (errors.length) {
        $.each(errors, function (index, value) {
            // globalVar defined in partials/footer.blade.php
            $.notify({ message: value }, globalVar.dangerNotify);
        });
    } else {
        $.notify({ message: 'Something went wrong.' }, globalVar.dangerNotify);
    }

    if (reload) {
        setTimeout(location.reload.bind(location), 1000);
    }
}

/**
 * Ajax request error handler.
 *
 * @param {Object}      jqXHR
 * @param {string}      textStatus
 * @param {string}      errorThrown
 * @param {string|null} alertSkeleton
 * @param {boolean}     reload
 * @param {number}      reloadDelay
 *
 * @return {void}
 */
function ajaxErrorHandler (jqXHR, textStatus, errorThrown, alertSkeleton, reload, reloadDelay) {
    var errorMsg     = null;
    var alertType    = null;
    var alertOptions = {};

    if (jqXHR.status === 419) {
        errorMsg = jqXHR.responseJSON.message;
    } else {
        errorMsg = jqXHR.status ?
                   typeof jqXHR.responseText !== 'undefined' && jqXHR.responseText.indexOf('Error Message:') !== -1 ?
                   jqXHR.status + ' ' + jqXHR.responseText : jqXHR.status + ' ' + errorThrown : 'Internal Server Error';
    }

    if (alertSkeleton === 'confirm') {
        alertType = 'red';
        alertOptions = { title: 'Error', icon: 'fa fa-warning' };
    } else if (alertSkeleton === 'notify') {
        alertType = globalVar.dangerNotify;
    }

    alertInit(errorMsg, alertSkeleton, alertType, alertOptions);

    if (reload === true) {
        setTimeout(function () {
            location.reload();
        }, reloadDelay);
    }
}

/**
 * Initialize alert.
 *
 * @param {string}             msg
 * @param {string|null}        skeleton
 * @param {string|Object|null} type
 * @param {Object}             options
 *
 * @return {void}
 */
function alertInit (msg, skeleton, type, options) {
    if (skeleton === 'confirm') {
        $.alert({
            title              : options.title,
            icon               : options.icon,
            type               : type,
            content            : msg,
            animation          : options.animation ? options.animation : 'top',
            closeAnimation     : options.closeAnimation ? options.closeAnimation : 'opacity',
            animateFromElement : false
        });
    } else if (skeleton === 'notify') {
        $.notify({ message: msg }, type);
    } else {
        alert(msg);
    }
}

/**
 * Show single notify to avoid repeatedly showing the same message.
 *
 * @param {string}  message
 * @param {string}  type
 * @param {Object}  typeConfig
 * @param {boolean} forceToShow
 *
 * @return {void}
 */
function singleNotify (message, type, typeConfig, forceToShow) {
    if (forceToShow === true || !$('.alert-' + type + "[data-notify='container']").get(0)) {
        $.notify({ message: message }, typeConfig);
    }
}

/**
 * Ajax form submits validation.
 *
 * @param {DOMElement} form
 * @param {string}     formUrl
 * @param {Object}     formData
 *
 * @return {void}
 */
function ajaxValidation (form, formUrl, formData) {
    $.ajax({
        type     : 'POST',
        url      : formUrl,
        data     : formData,
        dataType : 'JSON',
        success  : function (data) {
            var keepDisabled  = false;
            var delayDisabled = 500;
            $('span.validation-error').html('');

            switch (data.status) {
                case true:
                    delayDisabled = delayDisabled * 100;
                    keepDisabled  = typeof data.btnDisabled !== 'undefined' && data.btnDisabled === true;
                    form.submit();
                    break;
                case false:
                    var positions = [];
                    $.each(data.errors, function (index, value) {
                        $("span[error-field='" + index + "']").html(value);
                        positions.push(parseInt($("span[error-field='" + index + "']").offset().top, 10));
                    });
                    var scrollTopVal = nonNegative(arrayMin(positions) - 200);
                    $('html, body').animate({ scrollTop: scrollTopVal }, 'fast');
                    break;
                default:
                    location.reload();
            }

            if (keepDisabled === false) {
                setTimeout(function () {
                    removeLadda(data.status, form, 1500);
                    form.find('.btn').attr('disabled', false);
                }, delayDisabled);
            }
        },
        error : function (jqXHR, textStatus, errorThrown) {
            ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
        }
    });
}

/**
 * Modal form store data.
 *
 * @param {string}     modalId
 * @param {DOMElement} form
 * @param {boolean}    listOrder
 * @param {boolean}    saveAndNew
 * @param {boolean}    tableDraw
 *
 * @return {void}
 */
function modalDataStore (modalId, form, listOrder, saveAndNew, tableDraw) {
    // globalVar defined in partials/footer.blade.php
    var table    = globalVar.jqueryDataTable;
    var formUrl  = form.prop('action');
    var enctype  = (typeof form.attr('enctype') !== 'undefined');
    var formData = enctype ? new FormData($(modalId + ' form').get(0)) : form.serialize();

    var ajaxArg = {
        type     : 'POST',
        url      : formUrl,
        data     : formData,
        dataType : 'JSON',
        success  : function (data) {
            if (data.status === true) {
                $(modalId + ' span.validation-error').html('');

                if (saveAndNew === true) {
                    // reset to default values
                    form.trigger('reset');
                    form.find('.select2-hidden-accessible').trigger('change');
                    setTimeout(function () { $(modalId + ' .none').slideUp(); }, 1000);
                } else {
                    delayModalHide(modalId, 1);
                }

                // Render HTML accordingly after storing data
                if (typeof data.innerHtml !== 'undefined') {
                    $(data.innerHtml).each(function (index, value) {
                        $(value[0]).html(value[1]);
                    });
                }

                // globalVar defined in partials/footer.blade.php
                if (typeof data.tabTable !== 'undefined' && typeof globalVar.dataTable[data.tabTable] !== 'undefined') {
                    table = globalVar.dataTable[data.tabTable];
                }

                // Reload data table after storing data
                if (typeof table !== 'undefined' && tableDraw === true) {
                    if (listOrder) {
                        table.columns.adjust().page('first').draw('page');
                    } else {
                        table.columns.adjust().page('last').draw('page');
                    }

                    if (typeof data.saveId !== 'undefined' && data.saveId !== null) {
                        focusSavedRow(table, data.saveId, false);
                    }
                }

                if (typeof data.modalImage !== 'undefined' && data.modalImage !== null) {
                    $("*[data-avt='" + data.modalImageType + "'] img").attr('src', data.modalImage);
                }

                // Calendar data load accordingly after storing data
                if ($('.calendar').get(0) && typeof data.renderEvent !== 'undefined' && data.renderEvent !== null) {
                    var event = $.parseJSON(data.renderEvent);
                    $('.calendar').fullCalendar('renderEvent', event);
                }

                // Gantt chart load accordingly after storing data
                if ($('.gantt').get(0) && typeof data.gantt !== 'undefined' && data.gantt === true) {
                    initGanttChart();
                }

                if ($('.auto-refresh').get(0)) {
                    ajaxAutoRefresh();
                }

                if ($('#chat-room').get(0) && typeof data.announce !== 'undefined' && data.announce === true) {
                    setTimeout(function () {
                        var activeChatroom = $('#chat-room').find('.navlist-item.active');

                        if (activeChatroom.length && $(data.rooms).length) {
                            var activeId = parseInt(activeChatroom.attr('chatroomid'), 10);

                            if (typeof data.rooms[0] !== 'undefined' && parseInt(data.rooms[0], 10) === activeId) {
                                activeChatroom.removeClass('active');
                                activeChatroom.click();
                            }
                        }
                    }, limitMinMax($(data.rooms).length * 50, 450, 1500));
                }

                // Render HTML accordingly after storing data
                if (typeof data.realtime !== 'undefined') {
                    $.each(data.realtime, function (index, value) {
                        $("span[realtime='" + index + "']").html(value);
                        $("*[data-realtime='" + index + "']").html(value);
                        $("input[realtime='" + index + "']").val(value).trigger('change');
                    });
                }

                // Kanban board load accordingly after storing data
                if ($('.funnel-container').get(0)) {
                    if (typeof data.kanbanAddStatus === 'undefined' || data.kanbanAddStatus === true) {
                        $.each(data.kanban, function (stage, cards) {
                            $.each(cards, function (position, card) {
                                $(".funnel-stage[id='" + stage + "'] .kanban-list").prepend(card);
                            });
                        });
                    }

                    // Render kanban stage total items no. in stage header
                    kanbanCountResponse(data);
                }

                $('[data-toggle="tooltip"]').tooltip();
                nicescrollResize('html');
            } else {
                $(modalId + ' span.validation-error').html('');

                $.each(data.errors, function (index, value) {
                    $(modalId + " span[field='" + index + "']").html(value);
                });

                if (typeof data.errorsClose !== 'undefined' && data.errorsClose === true) {
                    delayModalHide(modalId, 3);
                }
            }

            removeLadda(data.status, modalId, 1500);
            $(modalId + ' .save').attr('disabled', false);
            $(modalId + ' .save-new').attr('disabled', false);
        },
        error : function (jqXHR, textStatus, errorThrown) {
            ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
        }
    };

    if (enctype) {
        ajaxArg.processData = false;
        ajaxArg.contentType = false;
    }

    $.ajax(ajaxArg);
}

/**
 * After update|store focus saved row.
 *
 * @param {DOMElement}      table
 * @param {boolean|integer} row
 * @param {boolean}         quick
 *
 * @return {void}
 */
function focusSavedRow (table, row, quick) {
    var tbody = $(table.table().body());
    var tr = 'tr:first-child';
    var focusDelay = quick === true ? 500 : 2500;
    var focusRemoved = quick === true ? 3000 : 5500;

    if (row === true) {
        tr = 'tr:first-child';
    } else if (row === false) {
        tr = 'tr:last-child';
    }

    setTimeout(function () {
        if (typeof row !== 'boolean') {
            tr = $($(tbody.find("a[editid='" + row + "']")).closest('tr'));
        }

        tbody.find(tr).addClass('saved');
    }, focusDelay);

    setTimeout(function () {
        tbody.find(tr).removeClass('saved');
    }, focusRemoved);
}

/**
 * Ajax form submits smooth save.
 *
 * @param {string} formUrl
 * @param {Object} formData
 *
 * @return {void}
 */
function smoothSave (formUrl, formData) {
    $.ajax({
        type        : 'POST',
        url         : formUrl,
        data        : formData,
        dataType    : 'JSON',
        processData : false,
        contentType : false,
        success     : function (data) {
            var btnDelayEnabled = 550;

            if (data.status === true) {
                $('span.validation-error').html('');

                if (typeof data.realtime !== 'undefined') {
                    $.each(data.realtime, function (index, info) {
                        if (info.tag === 'img' && $("img[realtime='" + index + "']").get(0)) {
                            var validSrc = info.value.replace(globalVar.baseUrl + '/', '');

                            if (validSrc) {
                                $("img[realtime='" + index + "']").prop('src', info.value);
                                $("input[name='" + index + "']").val('');
                                $("img[realtime='" + index + "']").show();
                            }
                        } else {
                            $("*[realtime='" + index + "']").html(info.value);
                        }
                    });
                }

                if (typeof data.removeCss !== 'undefined') {
                    $(data.removeCss).each(function (index, value) {
                        $(value[0]).removeClass(value[1]);
                    });
                }

                if (typeof data.redirect !== 'undefined' && data.redirect !== null) {
                    btnDelayEnabled = 10000;
                    window.location.replace(data.redirect);
                } else {
                    $.notify({ message: 'Update was successful' }, globalVar.successNotify);
                }
            } else {
                $('span.validation-error').html('');
                var positions = [];

                $.each(data.errors, function (index, value) {
                    $("span[field='" + index + "']").html(value);
                    positions.push(parseInt($("span[field='" + index + "']").offset().top, 10));
                });

                if (data.errors == null) {
                    $.notify({ message: 'Something went wrong.' }, globalVar.dangerNotify);
                } else {
                    if ((typeof data.scroll !== 'undefined' && data.scroll === true) || typeof data.scroll === 'undefined') {
                        var scrollTopVal = nonNegative(arrayMin(positions) - 200);
                        $('html, body').animate({ scrollTop: scrollTopVal }, 'fast');
                    }
                }
            }

            setTimeout(function () {
                removeLadda(data.status, '.smooth-save', 1500);
                $('.smooth-save .save').attr('disabled', false);
                $('.smooth-save .submit').attr('disabled', false);
            }, btnDelayEnabled);
        },
        error : function (jqXHR, textStatus, errorThrown) {
            ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
        }
    });
}

/**
 * Get select options dropdown list by ajax request.
 *
 * @param {string}      requestUrl
 * @param {Object}      data
 * @param {string}      selectIdentifier
 * @param {Object|null} topItem
 * @param {Object|null} bottomItem
 * @param {boolean}     bottomDefault
 * @param {string}      prefix
 * @param {string}      postfix
 * @param {boolean}     defaultType
 *
 * @return {void}
 */
function ajaxDropdownList (
    requestUrl,
    data,
    selectIdentifier,
    topItem,
    bottomItem,
    bottomDefault,
    prefix,
    postfix,
    defaultType
) {
    $.ajax({
        type     : 'GET',
        url      : requestUrl,
        data     : data,
        dataType : 'JSON',
        success  : function (data) {
            if (data.status === true) {
                var select = $(selectIdentifier).empty();
                var defaultVal = null;

                if (topItem !== null) {
                    $('<option/>', topItem).appendTo(select);
                }

                // Render responded dropdown list
                $.each(data.items, function (order, item) {
                    $('<option/>', {
                        value : item.id,
                        text  : prefix + item.name + postfix
                    }).appendTo(select);

                    if (defaultType === false) {
                        defaultVal = item.id;
                    }
                });

                if (data.items.length > 0 && defaultVal !== null && bottomItem !== null) {
                    $('<option/>', bottomItem).appendTo(select);

                    if (bottomDefault === true) {
                        defaultVal = bottomItem.value;
                    }
                }

                // globalVar defined in partials/footer.blade.php
                globalVar.defaultDropdown.push({
                    identifier : selectIdentifier,
                    default    : defaultVal
                });

                select.val(defaultVal).trigger('change');
            }
        },
        error : function (jqXHR, textStatus, errorThrown) {
            ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
        }
    });
}

/**
 * Get parent append child select options list by ajax request.
 *
 * @param {DOMElement} selectObj
 * @param {DOMElement} form
 * @param {string}     parent
 * @param {string}     child
 * @param {string}     field
 * @param {number}     id
 *
 * @return {void}
 */
function appendDropdownLoad (selectObj, form, parent, child, field, id) {
    var dataUrl      = globalVar.baseAdminUrl + '/dropdown-append-list/' + parent + '/' + child.replace('[]', '');
    var append       = $(form.find("select[data-append='" + child + "']"));
    var appendVal    = append.val();
    var appendlist   = append.empty();
    var appendDiv    = $(append.closest(append.data('container')));
    var invalidInput = $(appendDiv.find("input[data-invalid='true']"));
    var hiddenInput  = $(appendDiv.find("[data-default='true']"));

    if (typeof append.attr('default-none') === 'undefined' ||
       (typeof append.attr('default-none') !== 'undefined' && append.attr('default-none') === 'true')
    ) {
        $('<option/>', { value: '', text: '-None-' }).appendTo(appendlist);
    }

    if (typeof append.attr('data-keepval') === 'undefined' ||
       (typeof append.attr('data-keepval') !== 'undefined' && append.attr('data-keepval') === 'false')
    ) {
        append.val('');
        appendDiv.find('.select2-hidden-accessible').trigger('change');
    }

    if (id === '') {
        if (typeof append.attr('data-enabled') === 'undefined') {
            append.attr('disabled', true);
            appendDiv.tooltip('enable');
        }

        if (!hiddenInput.is('input')) {
            append.html(hiddenInput.html());
            append.val(appendVal);
            appendDiv.find('.select2-hidden-accessible').trigger('change');
        }
    } else {
        append.attr('disabled', false);
        appendDiv.tooltip('disable');

        $.ajax({
            type     : 'GET',
            url      : dataUrl,
            data     : { field: field, id: id },
            dataType : 'JSON',
            success  : function (data) {
                if (data.status === true) {
                    $(appendlist).each(function (index, uiDropdown) {
                        $(uiDropdown).find('option').not(':first').remove();
                    });

                    // Render responded dropdown list
                    $.each(data.selectOptions, function (id, name) {
                        if (Number.isInteger(parseInt(id, 10))) {
                            if (id !== invalidInput.val()) {
                                $('<option/>', { value: id, text: name }).appendTo(appendlist);
                            }
                        } else {
                            $('<optgroup/>', { label: id }).appendTo(appendlist);
                            var optgroupList = $(append.find("optgroup[label='" + id + "']")).empty();

                            $.each(name, function (key, display) {
                                if (key !== invalidInput.val()) {
                                    $('<option/>', { value: key, text: display }).appendTo(optgroupList);
                                }
                            });
                        }
                    });

                    if (hiddenInput.val() !== null && hiddenInput.val() !== '') {
                        $(hiddenInput).each(function (index, uiHdInput) {
                            $($(uiHdInput).closest($(uiHdInput).data('container'))).find("select[data-append='" + child + "']").val($(uiHdInput).attr('value'));
                            $($(uiHdInput).closest($(uiHdInput).data('container'))).find('.select2-hidden-accessible').trigger('change');
                            $(uiHdInput).val('');
                        });
                    } else {
                        if (typeof append.attr('data-keepval') !== 'undefined' &&
                            append.attr('data-keepval') === 'true'
                        ) {
                            append.val(appendVal);
                            appendDiv.find('.select2-hidden-accessible').trigger('change');
                        }
                    }

                    appendDiv.find('span.validation-error').html('');
                } else {
                    appendDiv.find('span.validation-error').html(data.error);
                }
            },
            error : function (jqXHR, textStatus, errorThrown) {
                ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
            }
        });
    }
}

/**
 * Disabled current select option.
 *
 * @param {string}        selectIdentifier
 * @param {string|number} currentItem
 * @param {boolean}       select2
 * @param {string}        select2Element
 * @param {Object}        select2Arg
 *
 * @return {void}
 */
function disabledCurrentItem (selectIdentifier, currentItem, select2, select2Element, select2Arg) {
    $(selectIdentifier + ' option').prop('disabled', false);
    $(selectIdentifier + " option[value='" + currentItem + "']").prop('disabled', true);

    if (select2 === true) {
        $(select2Element).select2('destroy').select2(select2Arg);
    }
}

/**
 * Initialize chart.
 *
 * @return {void}
 */
function initChart () {
    if ($('.chart-js-pie').get(0)) {
        $('.chart-js-pie').each(function (index, ui) {
            var pieDataArray       = [];
            var pieLabelArray      = [];
            var pieBackgroundArray = [];
            var pieDataStr         = $(ui).data('pie').split(',');
            var pieLabelStr        = $(ui).data('label').split(',');
            var pieBackgroundStr   = $(ui).data('background').split('),');

            // Set formatted data array
            if (pieDataStr.length) {
                $(pieDataStr).each(function (key, value) {
                    if (value !== '' && value !== null) {
                        pieDataArray.push(value);
                    }
                });
            }

            // Set formatted label array
            if (pieLabelStr.length) {
                $(pieLabelStr).each(function (key, label) {
                    if (label !== '' && label !== null) {
                        pieLabelArray.push(label);
                    }
                });
            }

            // Set formatted background array
            if (pieBackgroundStr.length) {
                $(pieBackgroundStr).each(function (key, rgba) {
                    if (rgba !== '' && rgba !== null) {
                        var rgbaFormat = rgba.slice(-1) !== ')' ? rgba + ')' : rgba;
                        pieBackgroundArray.push(rgbaFormat);
                    }
                });
            }

            if (pieDataArray.length) {
                initChartJsPie('#' + $(ui).attr('id'), pieLabelArray, pieDataArray, pieBackgroundArray);
            }
        });
    }

    if ($('.chart-js-stacked').get(0)) {
        $('.chart-js-stacked').each(function (index, ui) {
            var labelArray      = $(ui).data('label').split(',');
            var groupLabel      = $(ui).data('group').split(',');
            var backgroundArray = $(ui).data('color').split('|');
            var dataArray       = $(ui).data('value').split('|');
            var datasets        = [];

            $(groupLabel).each(function (key, label) {
                var set = {
                    label           : label,
                    backgroundColor : backgroundArray[key],
                    stack           : 'reportColumn',
                    borderWidth     : 0,
                    data            : dataArray[key].split(',')
                };

                datasets.push(set);
            });

            if (labelArray.length) {
                initChartJsStacked('#' + $(ui).attr('id'), labelArray, datasets);
            }
        });
    }

    if ($('.chart-js-line').get(0)) {
        $('.chart-js-line').each(function (index, ui) {
            var lineDataArray  = [];
            var lineLabelArray = [];
            var lineDataStr    = $(ui).data('line').split(',');
            var lineLabelStr   = $(ui).data('label').split(',');

            // Set formatted data array
            if (lineDataStr.length) {
                $(lineDataStr).each(function (key, value) {
                    if (value !== '' && value !== null) {
                        lineDataArray.push(value);
                    }
                });
            }

            // Set formatted label array
            if (lineLabelStr.length) {
                $(lineLabelStr).each(function (key, label) {
                    if (label !== '' && label !== null) {
                        lineLabelArray.push(label);
                    }
                });
            }

            if (lineDataArray.length) {
                initChartJsLine('#' + $(ui).attr('id'), lineLabelArray, lineDataArray);
            }
        });
    }

    if ($('.gantt').get(0)) {
        initGanttChart();
    }
}

/**
 * Initialize Gantt chart.
 *
 * @return {void}
 */
function initGanttChart () {
    $('.gantt').each(function (index, ui) {
        var scale        = typeof $(ui).data('scale') !== 'undefined' ? $(ui).data('scale') : 'days';
        var minScale     = typeof $(ui).data('min-scale') !== 'undefined' ? $(ui).data('min-scale') : 'hours';
        var maxScale     = typeof $(ui).data('max-scale') !== 'undefined' ? $(ui).data('max-scale') : 'months';
        var itemsPerPage = typeof $(ui).attr('data-per-page') !== 'undefined' ? $(ui).attr('data-per-page') : 25;

        $(ui).gantt({
            source        : $(ui).attr('data-url'),
            itemsPerPage  : parseInt(itemsPerPage, 10),
            waitText      : '',
            navigate      : 'scroll',
            scale         : scale,
            minScale      : minScale,
            maxScale      : maxScale,
            scrollToToday : true,
            useCookie     : false,
            onItemClick   : function (eventData) {
                if (eventData.can_edit) {
                    var data = { id: eventData.id, html: true };

                    // getCommonEdit function defined in modals/common-edit.blade.php
                    getCommonEdit(eventData.id, data, eventData.type, eventData.edit_url, eventData.update_url, eventData.modal_size, null, '#common-edit', '#common-edit-content');
                } else {
                    window.location.href = eventData.show_url;
                }
            },
            onAddClick : function (dt, rowId) {
                openCommonCreateModal($(ui), null, null, $(ui).data('create-url'), $(ui).data('create-form'));

                setTimeout(function () {
                    if ($("#common-add input[name='start_date']").hasClass('datepicker')) {
                        $("#common-add input[name='start_date']").val(moment(dt).format('YYYY-MM-DD'));
                    }
                }, 500);
            },
            onRender : function () {
                $(ui).find('.rightPanel').addClass('scroll-box-x');
                $(ui).find('.rightPanel').addClass('only-thumb');

                if (typeof $(ui).attr('data-title') !== 'undefined') {
                    var progressBar = '';

                    if (typeof $(ui).data('progress') !== 'undefined') {
                        progressBar = "<div class='progress curve-narrow'><div class='progress-bar color-success' role='progressbar' " +
                                      "style='width: " + $(ui).data('progress') + "%;' aria-valuenow='" + $(ui).data('progress') + "' " +
                                      "aria-valuemin='0' aria-valuemax='100'></div></div>";
                    }

                    $(ui).find('.row.spacer').html('<h3>' + $(ui).data('title') + '</h3>' + progressBar);
                }

                $('[data-toggle="tooltip"]').tooltip();
                nicescrollResize('html');
            }
        });
    });
}

/**
 * Initialize pie or doughnut chart.
 *
 * @param {string} canvasId
 * @param {Array}  pieLabelArray
 * @param {Array}  pieDataArray
 * @param {Array}  pieBackgroundArray
 *
 * @return {void}
 */
function initChartJsPie (canvasId, pieLabelArray, pieDataArray, pieBackgroundArray) {
    var pieData = {
        datasets : [{
            data            : pieDataArray,
            backgroundColor : pieBackgroundArray,
            borderWidth     : 1
        }],
        labels : pieLabelArray
    };

    var legendShow = typeof $(canvasId).attr('data-legend-hide') === 'undefined';
    var legendPosition = typeof $(canvasId).attr('data-legend-position') !== 'undefined' ? $(canvasId).attr('data-legend-position') : 'right';
    var options = {
        responsive : true,
        legend     : {
            display  : legendShow,
            position : legendPosition,
            labels   : {
                fontColor  : 'rgba(51, 51, 51, 0.85)',
                boxWidth   : 11,
                fontSize   : 11,
                fontFamily : "'Open Sans', 'Verdana'",
                padding    : 9
            }
        },
        tooltips : {
            callbacks : {
                label : function (tooltipItem, data) {
                    var chart      = this._chartInstance;
                    var total      = chart.getDatasetMeta(0).total;
                    var thisVal    = parseInt(data.datasets[0].data[tooltipItem.index], 10);
                    var percentage = ((thisVal * 100) / total).toFixed(2);
                    var label      = data.labels[tooltipItem.index] + ': ' + thisVal + ' (' + percentage + '%)';

                    return label;
                }
            }
        }
    };

    if (typeof $(canvasId).get(0).$chartjs !== 'undefined') {
        globalVar.pieChart[canvasId].destroy();
    }

    var areAllZero = pieDataArray.every(function (val) {
        return val === 0;
    });

    if (!areAllZero) {
        var pieChartType = typeof $(canvasId).attr('data-doughnut') !== 'undefined' ? 'doughnut' : 'pie';
        // Chart defined in plugins/chartjs
        var thisPieChart = new Chart($(canvasId).get(0), {
            type    : pieChartType,
            data    : pieData,
            options : options
        });
        thisPieChart.update();
        globalVar.pieChart[canvasId] = thisPieChart;
    }
}

/**
 * Initialize bar chart.
 *
 * @param {string} canvasId
 * @param {Array}  labelArray
 * @param {Array}  datasets
 *
 * @return {void}
 */
function initChartJsStacked (canvasId, labelArray, datasets) {
    var barChartData = {
        labels   : labelArray,
        datasets : datasets
    };

    var options = {
        title      : { display: false },
        tooltips   : { mode: 'index', intersect: false },
        responsive : true,
        legend     : {
            display  : true,
            position : 'top',
            align    : 'center',
            labels   : {
                fontColor  : 'rgba(51, 51, 51, 0.85)',
                boxWidth   : 11,
                fontSize   : 11,
                fontFamily : "'Open Sans', 'Verdana'",
                padding    : 15
            }
        },
        scales : {
            xAxes : [{
                display       : true,
                barPercentage : 0.2,
                scaleLabel    : { display: false },
                gridLines     : {
                    display       : true,
                    color         : 'rgba(51, 51, 51, 0)',
                    zeroLineColor : 'rgba(51, 51, 51, 0.1)'
                },
                ticks : {
                    fontColor  : 'rgba(51, 51, 51, 0.5)',
                    fontFamily : "'Open Sans', 'Verdana'",
                    fontSize   : 11,
                    fontStyle  : 500
                }
            }],
            yAxes : [{
                display    : true,
                scaleLabel : { display: false },
                gridLines  : {
                    display       : true,
                    color         : 'rgba(51, 51, 51, 0.045)',
                    zeroLineColor : 'rgba(51, 51, 51, 0.05)'
                },
                ticks : {
                    fontColor   : 'rgba(51, 51, 51, 0.5)',
                    fontFamily  : "'Open Sans', 'Verdana'",
                    fontSize    : 11,
                    fontStyle   : 500,
                    beginAtZero : true
                }
            }]
        }
    };

    if (typeof $(canvasId).get(0).$chartjs !== 'undefined') {
        globalVar.barChart[canvasId].destroy();
    }

    // Chart defined in plugins/chartjs
    var thisBarChart = new Chart($(canvasId).get(0), {
        type    : 'bar',
        data    : barChartData,
        options : options
    });
    thisBarChart.update();
    globalVar.barChart[canvasId] = thisBarChart;
}

/**
 * Initialize line chart.
 *
 * @param {string} canvasId
 * @param {Array}  lineLabelArray
 * @param {Array}  lineDataArray
 *
 * @return {void}
 */
function initChartJsLine (canvasId, lineLabelArray, lineDataArray) {
    var lineData = {
        datasets : [{
            data            : lineDataArray,
            label           : $(canvasId).data('label-name'),
            backgroundColor : 'rgba(185, 220, 250, 0.5)',
            borderColor     : 'rgba(100, 190, 255, 1)',
            borderWidth     : 1
        }],
        labels : lineLabelArray
    };

    var options = {
        responsive          : true,
        maintainAspectRatio : true,
        spanGaps            : false,
        showLines           : true,
        elements            : {
            line : {
                tension : 0.000001
            }
        },
        plugins : {
            filler : {
                propagate : false
            }
        },
        title  : { display: false },
        legend : { display: false },
        scales : {
            xAxes : [{
                display    : true,
                scaleLabel : { display: false },
                gridLines  : {
                    display       : true,
                    color         : 'rgba(51, 51, 51, 0)',
                    zeroLineColor : 'rgba(51, 51, 51, 0.1)'
                },
                ticks : {
                    fontColor  : 'rgba(51, 51, 51, 0.5)',
                    fontFamily : "'Open Sans', 'Verdana'",
                    fontSize   : 11,
                    fontStyle  : 500
                }
            }],
            yAxes : [{
                display    : true,
                scaleLabel : { display: false },
                gridLines  : {
                    display       : true,
                    color         : 'rgba(51, 51, 51, 0.045)',
                    zeroLineColor : 'rgba(51, 51, 51, 0.05)'
                },
                ticks : {
                    fontColor    : 'rgba(51, 51, 51, 0.5)',
                    fontFamily   : "'Open Sans', 'Verdana'",
                    fontSize     : 11,
                    fontStyle    : 500,
                    beginAtZero  : true,
                    stepSize     : parseInt($(canvasId).data('step'), 10),
                    suggestedMin : parseInt($(canvasId).data('min'), 10),
                    suggestedMax : parseInt($(canvasId).data('max'), 10)
                }
            }]
        }
    };

    if (typeof $(canvasId).get(0).$chartjs !== 'undefined') {
        globalVar.lineChart[canvasId].destroy();
    }

    // Chart defined in plugins/chartjs
    var thisLineChart = new Chart($(canvasId).get(0), {
        type    : 'line',
        data    : lineData,
        options : options
    });
    thisLineChart.update();
    globalVar.lineChart[canvasId] = thisLineChart;
}

/**
 * JSON string to an array.
 *
 * @param {string} jsonString
 *
 * @return {Array}
 */
function attrJsonStringToArray (jsonString) {
    var outcomeArray    = [];
    var jsonStringArray = jsonString.split('}');

    $(jsonStringArray).each(function (index, value) {
        if (value.indexOf('{') >= 0) {
            value = value.replace(',{', '{');
            value = value + '}';
            value = JSON.parse(value);
            outcomeArray.push(value);
        }
    });

    return outcomeArray;
}

/**
 * Stop and remove Ladda from an HTML element.
 *
 * @param {boolean}    status
 * @param {DOMElement} container
 * @param {number}     delay
 *
 * @return {void}
 */
function removeLadda (status, container, delay) {
    if (typeof globalVar.ladda !== 'undefined' && globalVar.ladda !== null) {
        var statusClass = status ? 'success' : 'error';
        container = typeof container === 'string' ? $(container) : container;
        container.find('.ladda-button[data-loading] .ladda-label').addClass(statusClass);
        globalVar.ladda.stop();
        globalVar.ladda.remove();

        setTimeout(function () {
            container.find('.ladda-label').removeClass(statusClass);
        }, delay);
    }
}

/**
 * Initialize the tab data table.
 *
 * @param {string|null} itemName
 * @param {string|null} itemNameCarrier
 *
 * @return {void}
 */
function tabDatatableInit (itemName, itemNameCarrier) {
    var item = itemName !== null ? itemName : $('#' + itemNameCarrier).attr('item').toLowerCase();

    if ($('.table.display').get(0)) {
        $('.table.display').each(function (index, ui) {
            var table         = null;
            var tableId       = '#' + $(ui).attr('id');
            var dataUrl       = $(ui).attr('dataurl');
            var tableColumns  = $(ui).attr('datacolumn');
            var columnButtons = $(ui).attr('databtn');
            var perPage       = typeof $(ui).attr('perpage') !== 'undefined' ? $(ui).attr('perpage') : getPerPageLength(0);
            var pagination    = typeof $(ui).attr('pagination') === 'undefined';
            var processing    = typeof $(ui).attr('processing') === 'undefined';
            var bulk          = typeof $(ui).attr('bulk') === 'undefined';
            var rowReorder    = typeof $(ui).attr('data-reorder') !== 'undefined';

            if (rowReorder) {
                table = rowReorderDatatableInit(tableId, dataUrl, tableColumns);
            } else {
                table = datatableInit(item, tableId, dataUrl, tableColumns, columnButtons, perPage, pagination, processing, bulk);
            }

            globalVar.dataTable[tableId] = table;

            if (index === 0 && tableId === '#datatable') {
                globalVar.jqueryDataTable = table;
            }
        });
    }
}

/**
 * Initialize the data table.
 *
 * @param {string}  item
 * @param {string}  tableId
 * @param {string}  dataUrl
 * @param {string}  tableColumns
 * @param {string}  columnButtons
 * @param {number}  perPage
 * @param {boolean} pagination
 * @param {boolean} processing
 * @param {boolean} bulk
 *
 * @return {Object}
 */
function datatableInit (item, tableId, dataUrl, tableColumns, columnButtons, perPage, pagination, processing, bulk) {
    tableColumns = attrJsonStringToArray(tableColumns);
    columnButtons = columnButtons !== '' ? attrJsonStringToArray(columnButtons) : [];
    var containerClass = typeof $(tableId).attr('data-containerclass') !== 'undefined' ? $(tableId).attr('data-containerclass') : '';
    var paginationHtml = pagination ? 'ip' : '';
    var bulkHtml = bulk ? "<'bulk'>" : '';
    var upperHtml = pagination ? bulkHtml + "lBf<'custom-filter'>" : '';
    upperHtml = columnButtons !== '' ? upperHtml : "lf<'custom-filter'>";

    var buttons = [
        {
            extend  : 'collection',
            text    : "<i class='fa fa-eye-slash'></i>",
            buttons : columnButtons,
            fade    : true
        },
        {
            text   : "<i class='fa fa-refresh'></i>",
            action : function (e, dt, node, config) {
                dt.ajax.reload();
            }
        }
    ];

    if (typeof $(tableId).attr('data-export') === 'undefined' ||
       (typeof $(tableId).attr('data-export') !== 'undefined' && $(tableId).attr('data-export') === 'true')
    ) {
        buttons.unshift({
            extend  : 'collection',
            text    : 'EXPORT',
            buttons : ['excel', 'csv', 'pdf', 'print']
        });
    }

    var table = $(tableId).on('init.dt', function () {
        $('[data-toggle="tooltip"]').tooltip();
        nicescrollResize('html');
        $(tableId + ' .pretty').find('input').prop('checked', false);
        $('.select-type-single').select2();
        $('.select-type-single-b').select2({ minimumResultsForSearch: -1 });
    }).on('error.dt', function (e, settings, techNote, message) {
        return dataTableError(message, tableId);
    }).DataTable({
        dom        : '<' + upperHtml + "r<'table-responsive " + containerClass + "'t>" + paginationHtml + '>',
        buttons    : buttons,
        paging     : pagination,
        pageLength : perPage,
        lengthMenu : [10, 25, 50, 75, 100],
        language   : {
            paginate          : { previous: "<i class='fa fa-angle-double-left'></i>", next: "<i class='fa fa-angle-double-right'></i>" },
            info              : '_START_ - _END_ / _TOTAL_',
            lengthMenu        : '_MENU_',
            search            : '_INPUT_',
            infoFiltered      : '',
            sProcessing       : '',
            searchPlaceholder : 'Search'
        },
        order      : [],
        processing : processing,
        serverSide : true,
        ajax       : {
            url  : globalVar.baseAdminUrl + '/' + dataUrl,
            type : 'POST',
            data : function (d) {
                $(tableColumns).each(function (index, value) {
                    var datatableWrapper = $(tableId).closest('.dataTables_wrapper');
                    d.globalSearch = datatableWrapper.length && datatableWrapper.find('.dataTables_filter').get(0) ? datatableWrapper.find(".dataTables_filter input[type='search']").val() : '';
                    var filterInput  = value.data;
                    var filterInputId = '#' + item + '-' + filterInput;

                    if (filterInput !== 'checkbox' && filterInput !== 'action') {
                        d[filterInput] = $(filterInputId).get(0) ? $(filterInputId).val() : '';
                    }
                });
            }
        },
        columns        : tableColumns,
        fnDrawCallback : function (oSettings) {
            $('[data-toggle="tooltip"]').tooltip();
            nicescrollResize('html');
            perfectScrollbarInit();
            $(tableId + ' .pretty').find('input').prop('checked', false);
            $('div.bulk').hide();
        }
    });

    var customFilter = $(tableId).closest('.dataTables_wrapper').parent().find('.table-filter');

    // Render custom filter HTML
    if (customFilter.length) {
        $(tableId).closest('.dataTables_wrapper').find('.custom-filter').html('');
        customFilter.find('select').each(function (index, ui) {
            $(tableId).closest('.dataTables_wrapper').find('.custom-filter').append($(ui));
        });
        select2PluginInit();
        $(tableId).closest('.dataTables_wrapper').find('.custom-filter select').change(function () {
            table.draw();
        });
    }

    return table;
}

/**
 * Initialize row reorderable data table.
 *
 * @param {string} tableId
 * @param {string} dataUrl
 * @param {string} tableColumns
 *
 * @return {Object}
 */
function rowReorderDatatableInit (tableId, dataUrl, tableColumns) {
    var source = $(tableId).attr('source');
    var rowReorder = $(tableId + ' th .dragdrop').get(0) ? { update: false } : false;
    var sourceCondition = typeof $(tableId).attr('data-source-condition') !== 'undefined' ? $(tableId).attr('data-source-condition') : null;

    dataUrl = globalVar.baseAdminUrl + '/' + dataUrl;
    tableColumns = attrJsonStringToArray(tableColumns);
    $('.pretty').find('input').prop('checked', false);

    var table = $(tableId).on('init.dt', function () {
        $('[data-toggle="tooltip"]').tooltip();
        nicescrollResize('html');
    }).on('error.dt', function (e, settings, techNote, message) {
        return dataTableError(message, tableId);
    }).DataTable({
        dom            : "<'full paging-false'r<'table-responsive zero-distance't>>",
        paging         : false,
        order          : [],
        processing     : true,
        oLanguage      : { sProcessing: '' },
        serverSide     : true,
        ajax           : { url: dataUrl, type: 'POST' },
        columns        : tableColumns,
        rowReorder     : rowReorder,
        fnDrawCallback : function (oSettings) {
            $('[data-toggle="tooltip"]').tooltip();
            nicescrollResize('html');
            perfectScrollbarInit();
        }
    });

    table.on('row-reorder', function (e, diff, edit) {
        var positions = [];

        $("input[name='positions[]']").each(function (index) {
            var positionInput = $("input[name='positions[]']").get(index);
            positions.push(positionInput.value);
        });

        $.ajax({
            type : 'POST',
            url  : globalVar.baseAdminUrl + '/dropdown-reorder',
            data : { source: source, positions: positions, condition: sourceCondition }
        });
    });

    return table;
}

/**
 * Show data table error.
 *
 * @param {string} message
 * @param {string} tableId
 *
 * @return {bool}
 */
function dataTableError (message, tableId) {
    var tableIdName = tableId.replace('#', '');
    var endPoint    = message.indexOf('. For');
    var startPoint  = message.indexOf('id=' + tableId.replace('#', '') + ' - ');

    if (startPoint !== -1 && endPoint !== -1) {
        message = message.substr(0, endPoint);
        message = message.substr((startPoint + tableId.length + 5), message.length);
    }

    $.notify({ message: message }, globalVar.dangerNotify);

    return true;
}

/**
 * Datatable row selects by checked column
 *
 * @param {string} tBody
 * @param {string} selectAllId
 * @param {string} inputSingleRow
 * @param {string} itemSingular
 * @param {string} itemPlural
 *
 * @return {void}
 */
function bulkChecked (tBody, selectAllId, inputSingleRow, itemSingular, itemPlural) {
    $(tBody).on('click.dt', inputSingleRow, function (event) {
        event.stopPropagation();
        var checked       = $(this).prop('checked');
        var tbody         = $(this).closest('tbody');
        var bulk          = $(this).closest('.dataTables_wrapper').find('.bulk');
        var rowsCount     = 0;
        var selectionText = ' ' + itemSingular + ' Selected';
        var totalRows     = tbody.find(inputSingleRow).size();
        var bulkHeight    = $(this).closest('.dataTables_wrapper').find('.table-filter').height() + 40;

        bulk.css('height', bulkHeight + 'px');

        if (checked === true) {
            rowsCount = tbody.find(inputSingleRow + ':checked').size();

            if (rowsCount > 1) {
                selectionText = ' ' + itemPlural + ' Selected';
            }

            selectionText = rowsCount + selectionText;
            $('div.bulk .selection').html(selectionText);

            if (rowsCount === totalRows) {
                $(selectAllId).find('input').prop('checked', true);
            }

            bulk.fadeIn();
        } else {
            rowsCount = tbody.find(inputSingleRow + ':checked').size();
            $(selectAllId).find('input').prop('checked', false);

            if (rowsCount === 0) {
                bulk.hide();
            }

            if (rowsCount > 1) {
                selectionText = ' ' + itemPlural + ' Selected';
            }

            selectionText = rowsCount + selectionText;
            $('div.bulk .selection').html(selectionText);
        }
    });

    $(selectAllId).on('click', function () {
        $('[data-toggle="tooltip"]').tooltip('hide');
        var checked       = $(this).find('input').prop('checked');
        var table         = $(this).closest('table');
        var bulk          = $(this).closest('.dataTables_wrapper').find('.bulk');
        var rowsCount     = 0;
        var selectionText = ' ' + itemSingular + ' Selected';
        var bulkHeight    = $(this).closest('.dataTables_wrapper').find('.table-filter').height() + 40;

        bulk.css('height', bulkHeight + 'px');

        if (checked === true) {
            table.find(inputSingleRow).prop('checked', true);
            rowsCount = table.find(inputSingleRow + ':checked').size();

            if (rowsCount > 1) {
                selectionText = ' ' + itemPlural + ' Selected';
            }

            selectionText = rowsCount + selectionText;
            $('div.bulk .selection').html(selectionText);
            bulk.fadeIn();
        } else {
            table.find(inputSingleRow).prop('checked', false);
            bulk.hide();
            rowsCount = table.find(inputSingleRow + ':checked').size();

            if (rowsCount > 1) {
                selectionText = ' ' + itemPlural + ' Selected';
            }

            selectionText = rowsCount + selectionText;
            $('div.bulk .selection').html(selectionText);
        }
    });

    $(selectAllId).mouseleave(function () {
        $('[data-toggle="tooltip"]').tooltip('hide');
    });
}

/**
 * Reset toggle permission
 *
 * @param {DOMElement} toggleBox
 * @param {string}     resetType
 * @param {boolean}    onlyDefault
 *
 * @return {void}
 */
function resetTogglePermission (toggleBox, resetType, onlyDefault) {
    if (resetType === 'open') {
        var getInputTag = onlyDefault ? "input[data-default='true']" : 'input';
        toggleBox.find('.parent-permission').not('.reset-false').find('input').prop('checked', true);
        toggleBox.find('.child-permission').not('.reset-false').css('opacity', 1);
        toggleBox.find('.child-permission').not('.reset-false').find(getInputTag).prop('checked', true);
        toggleBox.find('.child-permission').not('.reset-false').find(getInputTag).attr('disabled', false);
    } else {
        toggleBox.find('.parent-permission').not('.reset-false').find('input').prop('checked', false);
        toggleBox.find('.child-permission').not('.reset-false').css('opacity', 0.5);
        toggleBox.find('.child-permission').not('.reset-false').find('input').prop('checked', false);
        toggleBox.find('.child-permission').not('.reset-false').find('input').attr('disabled', true);
    }
}

/**
 * Open common modal to create new data.
 *
 * @param {DOMElement}  thisBtn
 * @param {string}      modalId
 * @param {string|null} contentTag
 * @param {string|null} postUrl
 * @param {string}      formContent
 *
 * @return {void}
 */
function openCommonCreateModal (thisBtn, modalId, contentTag, postUrl, formContent) {
    modalId     = modalId === null ? '#common-add' : modalId;
    contentTag  = contentTag === null ? '#common-add-content' : contentTag;
    var action  = postUrl === null ? thisBtn.attr('data-action') : postUrl;
    var content = formContent === null ? thisBtn.attr('data-content') : formContent;

    // Set all default data
    var defaultData = typeof thisBtn.attr('data-default') !== 'undefined' ? thisBtn.attr('data-default') : null;
    var showData    = typeof thisBtn.attr('data-show') !== 'undefined' ? thisBtn.attr('data-show') : null;
    var hideData    = typeof thisBtn.attr('data-hide') !== 'undefined' ? thisBtn.attr('data-hide') : null;
    var freezeData  = typeof thisBtn.attr('data-freeze') !== 'undefined' ? thisBtn.attr('data-freeze') : null;

    var formData = {
        formAction  : action,
        viewContent : content,
        viewType    : 'create',
        default     : defaultData,
        showField   : showData,
        hideField   : hideData,
        freezeField : freezeData
    };

    var modalDataTable  = typeof thisBtn.attr('modal-datatable') !== 'undefined';
    var dataTableUrl    = typeof thisBtn.attr('datatable-url') !== 'undefined' ? thisBtn.attr('datatable-url') : null;
    var dataTableAddUrl = typeof thisBtn.attr('datatable-addurl') !== 'undefined' ? thisBtn.attr('datatable-addurl') : null;
    var dataTableCol    = typeof thisBtn.attr('datatable-col') !== 'undefined' ? thisBtn.attr('datatable-col') : null;
    var modalSize       = typeof thisBtn.attr('data-modalsize') !== 'undefined' ? thisBtn.attr('data-modalsize') : null;
    var resetPermission = typeof thisBtn.attr('data-permission') !== 'undefined';
    var title           = typeof thisBtn.attr('modal-title') === 'undefined' ? 'Add New ' + thisBtn.attr('data-item') : thisBtn.attr('modal-title');
    title              += typeof thisBtn.attr('modal-sub-title') === 'undefined' ? '' : " <span class='shadow bracket'>" + thisBtn.attr('modal-sub-title') + '</span>';

    $(modalId + ' .save').attr('disabled', false);
    $(modalId + ' .save-new').attr('disabled', false);
    $(modalId + ' .save').show();
    $(modalId + ' .save-new').show();

    if (typeof thisBtn.attr('save-new') !== 'undefined') {
        if (thisBtn.attr('save-new') === 'false-all') {
            $(modalId + ' .save-new').hide();
            $(modalId + ' .save').hide();
        } else if (thisBtn.attr('save-new') === 'false') {
            $(modalId + ' .save-new').hide();
        }
    }

    if (typeof globalVar.ladda !== 'undefined' && globalVar.ladda !== null) {
        globalVar.ladda.remove();
    }

    $(modalId + ' .save').html('Save');
    $(modalId + ' .cancel').html('Cancel');
    $(modalId + ' .modal-footer').show();
    $(modalId).removeClass('medium');
    $(modalId).removeClass('tiny');
    $(modalId).removeClass('sub');

    if (typeof thisBtn.attr('save-txt') !== 'undefined') {
        $(modalId + ' .save').html(thisBtn.attr('save-txt'));
    }

    if (typeof thisBtn.attr('cancel-txt') !== 'undefined') {
        $(modalId + ' .cancel').html(thisBtn.attr('cancel-txt'));
    }

    if (typeof thisBtn.attr('modal-footer') !== 'undefined') {
        $(modalId + ' .modal-footer').hide();
    }

    // Set modal size before appear
    if (modalSize === null) {
        if (!$(modalId).hasClass('large')) {
            $(modalId).addClass('large');
        }
    } else {
        $(modalId).removeClass('large');
        $(modalId).addClass(modalSize);
    }

    // If the modal form has input files to upload
    if (typeof thisBtn.attr('modal-files') !== 'undefined') {
        $(modalId + ' form').attr('enctype', 'multipart/form-data');
    }

    $(modalId + ' form').trigger('reset');
    $(modalId + ' form').find('.select2-hidden-accessible').trigger('change');
    $(modalId + ' span.validation-error').html('');
    $(modalId + ' ' + contentTag).hide();
    $(modalId + ' .modal-loader').show();
    $(modalId + ' .modal-title').html(title);

    $(modalId).modal({
        show     : true,
        backdrop : false,
        keyboard : false
    });

    $.ajax({
        type    : 'GET',
        url     : globalVar.baseAdminUrl + '/view-content',
        data    : formData,
        success : function (data) {
            var $dataObj = $(data.html);

            if (data.status === true && $dataObj.length) {
                $(modalId + ' form').attr('action', action);
                $(modalId + ' ' + contentTag).html($dataObj);

                // If modal has toggle permission box
                if ($(modalId + ' .toggle-permission').get(0)) {
                    $(modalId + ' .child-permission').css('opacity', 1);
                    $(modalId + ' .child-permission').find('input').attr('disabled', false);
                    $(modalId + ' .child-permission').find("input[data-default='true']").prop('checked', true);
                }

                if ($(modalId + ' .dropzone').get(0)) {
                    $(modalId + ' .dropzone').attr('data-linked', data.info.linked_type);
                }

                if (dataTableAddUrl != null) {
                    $('#modal-datatable').attr('data-addurl', dataTableAddUrl);
                }

                // PerfectScrollbar defined in plugins/perfectscrollbar
                var ps   = new PerfectScrollbar(modalId + ' .modal-body');
                var hide = '';
                var show = '';

                // initialize all necessary plugin
                pluginInit();

                if (resetPermission) {
                    resetTogglePermission($(modalId + " .form-group[data-set='permission']"), 'open', true);
                }

                // Set all default value, show fields, hidden fields
                $.each(data.info, function (index, value) {
                    if ($(modalId + " *[name='" + index + "']").get(0)) {
                        if ($(modalId + " *[name='" + index + "']").is(':checkbox')) {
                            if ($(modalId + " *[name='" + index + "']").val() === value) {
                                $(modalId + " *[name='" + index + "']").prop('checked', true);
                            } else {
                                $(modalId + " *[name='" + index + "']").prop('checked', false);
                            }
                        } else {
                            $(modalId + " *[name='" + index + "']").not(':radio').val(value).trigger('change');
                        }

                        if ($(modalId + " *[name='" + index + "']").is(':radio')) {
                            $(modalId + " *[name='" + index + "']").each(function (index, obj) {
                                if ($(obj).val() === value) {
                                    $(obj).prop('checked', true);
                                }
                            });
                        }
                    }

                    if (index === 'show') {
                        $.each(value, function (key, val) {
                            show += modalId + " *[name='" + val + "'],";
                        });
                        show = show.slice(0, -1);
                    }

                    if (index === 'hide') {
                        $.each(value, function (key, val) {
                            hide += modalId + " label[for='" + val + "'],";
                        });
                        hide = hide.slice(0, -1);
                    }
                });

                $(modalId + ' .modal-body').animate({ scrollTop: 1 });
                $(show).closest('.none').show();
                $(show).closest('.form-group').show();
                $(hide).closest('.form-group').hide();
                $(modalId + ' ' + contentTag).slideDown();
                $(modalId + ' .modal-body').animate({ scrollTop: 0 });
                $(modalId + ' .modal-loader').fadeOut(1000);
                $(modalId + ' input[data-focus="true"]').focus();

                if (modalDataTable) {
                    var item          = $('#modal-datatable').attr('data-item');
                    var dataUrl       = dataTableUrl != null ? dataTableUrl : $('#modal-datatable').attr('data-url');
                    var tableColumns  = dataTableCol != null ? dataTableCol : $('#modal-datatable').attr('data-column');
                    var columnButtons = $('#modal-datatable').attr('data-btn');
                    var modalTable    = datatableInit(item, '#modal-datatable', dataUrl, tableColumns, columnButtons, 10, true, true, true);
                    globalVar.jqueryModalDataTable = modalTable;

                    var selectAllId  = '#' + $('#modal-datatable').find('.select-all').attr('id');
                    var itemSingular = $('#modal-datatable').attr('data-item');
                    var itemPlural   = itemSingular + 's';
                    bulkChecked('#modal-datatable tbody', selectAllId, 'input.single-row:not(:disabled)', itemSingular, itemPlural);
                }
            }
        },
        error : function (jqXHR, textStatus, errorThrown) {
            ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
        }
    });
}

/**
 * Set cursor position.
 *
 * @param {number} position
 *
 * @return {Object}
 */
$.fn.setCursorPosition = function (position) {
    this.each(function (index, input) {
        if (input.setSelectionRange) {
            input.setSelectionRange(position, position);
        } else if (input.createTextRange) {
            var range = input.createTextRange();
            range.collapse(true);
            range.moveEnd('character', position);
            range.moveStart('character', position);
            range.select();
        }
    });

    return this;
};
