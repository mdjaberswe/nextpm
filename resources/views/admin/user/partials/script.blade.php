<script>
    $(document).ready(function () {
        // User status toggles active|inactive in Datatable
        $('#datatable tbody').on('click.dt', '.switch.user-status', function (event) {
            var thisSwitch = $(this);
            var input = $(this).find('input');
            changeStatus(thisSwitch, input);
        });

        // User status toggle active|inactive in the show page
        $('.switch.user-status').on('click', function () {
            var thisSwitch = $(this);
            var input = $(this).find('input');
            changeStatus(thisSwitch, input);
        });
    });

    /**
     * Ajax request to change status.
     *
     * @param {DOMElement} thisSwitch
     * @param {DOMElement} input
     *
     * @return {void}
     */
    function changeStatus (thisSwitch, input) {
        if (!thisSwitch.hasClass('disabled')) {
            var id      = input.val();
            var checked = input.prop('checked') ? 1 : 0;

            $.ajax({
                type     : 'POST',
                url      : globalVar.baseAdminUrl + '/user-status/' + id,
                data     : { id: id, checked: checked },
                dataType : 'JSON',
                success  : function (data) {
                    if (data.status === true) {
                        var updateStatus = 'Inactive';

                        if (data.checked) {
                            updateStatus = 'Active';
                        }

                        thisSwitch.attr('data-original-title', updateStatus);
                        thisSwitch.parent().find('.tooltip-inner').html(updateStatus);
                    } else {
                        input.prop('checked', !checked);
                    }
                },
                error : function (jqXHR, textStatus, errorThrown) {
                    input.prop('checked', !checked);
                    // ajaxErrorHandler defined in js/app.js
                    ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'notify', false, 2500);
                }
            });
        }
    }
</script>
