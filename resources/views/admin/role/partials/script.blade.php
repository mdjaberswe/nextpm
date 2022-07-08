<script>
    $(document).ready(function () {
        // Add CSS class "last" on the last item of the permission summary
        $('.permission-summary').each(function (index, ui) {
            $(ui).find('.para-soft:first span').removeClass('last');
            $(ui).find('.para-soft:first span:visible:last').addClass('last');
        });

        // Ajax request to show all role users of the specified role
        $('#datatable tbody').on('click.dt', '.role-users', function () {
            var id = $(this).attr('rowid');
            var roleName = $(this).closest('tr').find('.role-name').html();

            if (typeof roleName !== 'undefined') {
                $('#role-users .modal-title').html('Users in ' + roleName + ' Role');
            }

            $('#role-users .modal-body').animate({ scrollTop: 0 });
            $('#role-users .modal-loader').show();
            $('#role-users .modal-body').hide();
            $('#role-users').modal();

            $.ajax({
                type    : 'POST',
                url     : globalVar.baseAdminUrl + '/role-users/' + id,
                data    : { id: id },
                success : function (data) {
                    if (data.status === true) {
                        // If the response is true then render HTML users list on modal
                        $('#role-users .modal-body').html(data.users);
                        $('#role-users .modal-body').slideDown(200);
                        $('#role-users .modal-loader').fadeOut(700);
                    } else {
                        $('#role-users .modal-loader').fadeOut(500);
                        $('#role-users .modal-body').hide().html("<p class='center-lg'>Something went wrong.</p>").fadeIn(500);
                        delayModalHide('#role-users', 2);
                    }
                },
                error : function (jqXHR, textStatus, errorThrown) {
                    // ajaxErrorHandler defined in js/app.js
                    ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'notify', false, 2500);
                }
            });
        });

        // Module permission toggle enabled|disabled event
        $('.switch').not('.all').on('click', function () {
            var checked = $(this).find('input').prop('checked');

            // If module permission is checked
            if (checked === true) {
                $(this).parent().parent().find('.permission-summary').addClass('block');
                $(this).parent().parent().find('.permission-summary .para-soft:first span').not('[disabled]').show();
                $(this).parent().parent().find('.permission-details').find('input:not([disabled])').prop('checked', true);
                $(this).parent().parent().find('.permission-summary .para-soft:first span').removeClass('last');
                $(this).parent().parent().find('.permission-summary .para-soft:first span:visible:last').addClass('last');

                var currentChecked   = $(this).closest('.permission-group').find('input:not([disabled]):checked').not('.all').size() + 1;
                var totalSwitchInput = $(this).closest('.permission-group').find('input:not([disabled])').not('.all').size();

                // If all modules permissions are checked then top all enabled|disabled toggle switch is checked
                if (currentChecked >= totalSwitchInput) {
                    $(this).closest('.permission-group').find('.all').find('input').prop('checked', true);
                }
            } else {
                $(this).parent().parent().find('.permission-summary').removeClass('block');
                $(this).parent().parent().find('.permission-details').slideUp(100);
                $(this).parent().parent().find('.permission-details').find('input').prop('checked', false);
                $(this).closest('.permission-group').find('.all').find('input').prop('checked', false);
            }
        });

        // All modules permission toggle enabled|disabled by one click
        $('.all').on('click', function () {
            var checked = $(this).find('input').prop('checked');
            var allPermissionsContainer = $(this).closest('.permission-group');

            if (checked === true) {
                // Enable all modules and their permissions
                allPermissionsContainer.find('.switch input').prop('checked', true);
                allPermissionsContainer.find('.permission-summary').addClass('block');
                allPermissionsContainer.find('.permission-summary .para-soft span').not('[disabled]').show();
                allPermissionsContainer.find('.permission-details').find('input:not([disabled])').prop('checked', true);

                $(allPermissionsContainer.find('.permission-summary')).each(function (index, ui) {
                    $(ui).find('.para-soft:first span').removeClass('last');
                    $(ui).find('.para-soft:first span:visible:last').addClass('last');
                });
            } else {
                // Disable all modules and their permissions
                allPermissionsContainer.find('.switch input').prop('checked', false);
                allPermissionsContainer.find('.permission-summary').removeClass('block');
                allPermissionsContainer.find('.permission-details').slideUp(100);
                allPermissionsContainer.find('.permission-details').find('input').prop('checked', false);
            }
        });

        // Permission summary details show on a dropdown box with all permissions checkbox
        $('main .permission-summary').on('click', function (e) {
            var thisDivPosition    = parseInt($(this).offset().top, 10) - parseInt($(this).closest('.page-content').offset().top, 10);
            var containerDivHeight = $(this).closest('.page-content').height();
            var lowerGap           = containerDivHeight - thisDivPosition;
            var comingDivHeight    = $(this).find('.permission-details').height() + 40;

            if (comingDivHeight > lowerGap) {
                $(this).find('.permission-details').css('top', 'auto');
                $(this).find('.permission-details').css('bottom', '100%');
            }

            e.stopPropagation();
            $('.permission-details').not($(this).children('.permission-details')).slideUp(100);
            $(this).find('.permission-details').slideToggle(100);
        });

        $('.permission-summary .permission-details').on('click', function (e) {
            e.stopPropagation();
        });

        // Slide up all opened permission details box when clicking outside
        $('main').on('click', function () {
            $('.permission-details').slideUp(100);
        });

        // Permission change event
        $('.para-checkbox input').on('change', function () {
            var permissionVal     = $(this).val();
            var checked           = $(this).prop('checked');
            var text              = $(this).parent().find('span').html();
            var parent            = $(this).attr('parent');
            var divTypeF          = $(this).parent().parent();
            var divTypeE          = divTypeF.parent();
            var divTypeD          = divTypeE.parent();
            var divTypeB          = divTypeD.parent();
            var permissionSummary = divTypeD.find("span[name='" + parent + "']");

            changePermissionEvent(permissionVal, permissionSummary, checked, text, divTypeF, divTypeE, divTypeD, divTypeB);
        });
    });

    /**
     * Permission change effect
     *
     * @param {string}     permissionVal
     * @param {string}     permissionSummary
     * @param {boolean}    checked
     * @param {string}     text
     * @param {DOMElement} divTypeF
     * @param {DOMElement} divTypeE
     * @param {DOMElement} divTypeD
     * @param {DOMElement} divTypeB
     *
     * @return {void}
     */
    function changePermissionEvent (permissionVal, permissionSummary, checked, text, divTypeF, divTypeE, divTypeD, divTypeB) {
        if (checked === true) {
            permissionSummary.show();

            // Ensure enable minimal "View" permission
            if (text !== 'View' && divTypeF.find('span').html() === 'View') {
                divTypeF.find('input:first').prop('checked', true);
            }
        } else {
            // If unchecked "View" permission then disable all of the rest module permissions
            if (text === 'View') {
                divTypeF.find('input').prop('checked', false);
                permissionSummary.hide();
            } else {
                if (permissionVal === permissionSummary.attr('status') ||
                    divTypeF.find('input:checked').length === 0
                ) {
                    permissionSummary.hide();
                }
            }

            if ((text === 'View' && permissionVal === permissionSummary.attr('status')) ||
                divTypeD.find('.para-soft:first').children(':visible').length === 0
            ) {
                divTypeE.removeClass('block');
                divTypeD.removeClass('block');
                divTypeB.find('.switch input').prop('checked', false);
            }
        }

        // Reset last permission summary items
        $(divTypeD).each(function (index, ui) {
            $(ui).find('.para-soft:first span').removeClass('last');
            $(ui).find('.para-soft:first span:visible:last').addClass('last');
        });
    }
</script>
