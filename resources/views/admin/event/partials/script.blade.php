<script>
    $(document).ready(function () {
        // Calendar filter view change to user's default calendar view
        $("select[name='calendar_filter']").val($("select[name='calendar_filter']").attr('data-default'));
        $("select[name='calendar_filter']").trigger('change');
        $("select[name='calendar_filter']").closest('.breadcrumb').find('.select2-hidden-accessible').trigger('change');

        // Calendar view change event and ajax request to load calendar data according to the filter parameter
        $(document).on('change', "select[name='calendar_filter']", function () {
            var thisSelect = $(this);

            $.ajax({
                type     : 'GET',
                data     : { calendar_filter: $(this).val() },
                url      : globalVar.baseAdminUrl + '/event-calendar-filter',
                dataType : 'JSON',
                success  : function (data) {
                    if (data.status === true) {
                        // Reload calendar data if view changed response status is true
                        thisSelect.closest('.row').find('.calendar').fullCalendar('refetchEvents');
                    }
                },
                error : function (jqXHR, textStatus, errorThrown) {
                    // ajaxErrorHandler defined in js/app.js
                    ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
                }
            });
        });

        // Ajax request to add event attendees
        $(document).on('click', '.add-attendee', function () {
            var formGroup = $(this).closest('.form-group');
            var dataTable = formGroup.parent('.modal-body').find('table');
            var attendees = formGroup.find("*[name='attendees[]']").val();
            var addBtn    = $(this);

            globalVar.ladda = Ladda.create(this);
            globalVar.ladda.start();
            addBtn.attr('disabled', true);

            $.ajax({
                type     : 'POST',
                url      : globalVar.baseAdminUrl + '/' + dataTable.attr('data-addurl'),
                data     : { attendees: attendees },
                dataType : 'JSON',
                success  : function (data) {
                    if (data.status === true) {
                        $('#common-add span.validation-error').html('');
                        formGroup.find("*[name='attendees[]']").val('');
                        formGroup.find('.select2-hidden-accessible').trigger('change');

                        if (typeof globalVar.jqueryModalDataTable !== 'undefined') {
                            globalVar.jqueryModalDataTable.ajax.reload(null, false);
                        }

                        if (typeof data.innerHtml !== 'undefined') {
                            $(data.innerHtml).each(function (index, value) {
                                $(value[0]).html(value[1]);
                            });
                        }

                        $('[data-toggle="tooltip"]').tooltip();
                    } else {
                        $('#common-add span.validation-error').html('');

                        $.each(data.errors, function (index, value) {
                            $("#common-add span[field='" + index + "']").html(value);
                        });
                    }

                    if (typeof globalVar.ladda !== 'undefined' && globalVar.ladda !== null) {
                        var statusClass = data.status ? 'success' : 'error';
                        $('#common-add .ladda-button[data-loading]').find('.ladda-label').addClass(statusClass);
                        globalVar.ladda.stop();
                        globalVar.ladda.remove();

                        setTimeout(function () {
                            $('#common-add .ladda-label').removeClass(statusClass);
                        }, 1500);
                    }

                    addBtn.attr('disabled', false);
                },
                error : function (jqXHR, textStatus, errorThrown) {
                    // ajaxErrorHandler defined in js/app.js
                    ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
                }
            });
        });
    });
</script>
