<div class="modal fade large" id="access-form">
    <div class="modal-dialog">
        <div class="modal-loader">
            <div class="spinner"></div>
        </div>

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Private <span class="capitalize"></span> <span class="shadow bracket"></span></h4>
            </div> <!-- end modal-header -->

            {{ Form::open(['route' => null, 'method' => 'post', 'class' => 'modal-form']) }}
                <div class="modal-body min-h150 perfectscroll">
                    <div class="form-group always-show">
                        <div class="col-xs-9">
                            {{ Form::select('staffs[]', $admin_users_list, null, ['class' => 'form-control white-select-type-multiple', 'multiple' => 'multiple', 'data-placeholder' => 'Allow some users only']) }}
                            {{ Form::select('ids', $admin_users_list, null, ['class' => 'none', 'data-stafflist' => 'true']) }}
                            <span field="staffs" class="validation-error"></span>
                        </div>

                        <div class="inline-block btn-container">
                            <button type="button" class="allow-user btn thin-both btn-warning">Add</button>
                        </div>
                    </div> <!-- end form-group -->

                    <div class="form-group">
                        <div class="col-xs-12 table-responsive min-h150">
                            <table class="table modal-table middle center">
                                <thead>
                                    <tr>
                                        <th class="w30">#</th>
                                        <th class="min-w270">ALLOWED USER</th>
                                        <th class="w70">READ</th>
                                        <th class="w70">WRITE</th>
                                        <th class="w70">DELETE</th>
                                        <th class="w30"></th>
                                    </tr>
                                </thead>

                                <tbody data-serial="true">
                                    {{-- Display allowed users data here --}}
                                </tbody>
                            </table>
                            <span field="serial" class="validation-error"></span>
                            <span field="id" class="validation-error"></span>
                            <span field="type" class="validation-error"></span>
                            <span field="allowed_staffs" class="validation-error"></span>
                        </div>
                    </div>
                </div> <!-- end modal-body -->

                {{ Form::hidden('id', null) }}
                {{ Form::hidden('type', null) }}
            {{ Form::close() }}

            <div class="modal-footer space btn-container">
                <button type="button" class="cancel btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="save btn btn-info ladda-button" data-style="expand-right">
                    <span class="ladda-label">Save</span>
                </button>
            </div> <!-- end modal-footer -->
        </div>
    </div> <!-- end modal-dialog -->
</div> <!-- end access-form -->

@push('scripts')
    <script>
        $(document).ready(function () {
            // Ajax request to add allowed users in modal data table form
            $('.allow-user').on('click', function () {
                var formGroup    = $(this).closest('.form-group');
                var modalBody    = formGroup.parent('.modal-body');
                var perfectIndex = $('.perfectscroll').index(modalBody);
                var tbody        = modalBody.find('table').find('tbody');
                var trCount      = tbody.children('tr').length;
                var staffs       = formGroup.find("*[name='staffs[]']").val();
                var addStaffs    = [];
                var allowBtn     = $(this);
                allowBtn.attr('disabled', true);

                $.each(staffs, function (index, val) {
                    var staffExist = tbody.find("tr[data-staff='" + val + "']");

                    if (staffExist.length === 0) {
                        addStaffs.push(val);
                    }
                });

                // Send ajax request if allowed users found
                if (addStaffs.length > 0) {
                    $.ajax({
                        type     : 'POST',
                        url      : globalVar.baseAdminUrl + '/allowed-user-data',
                        data     : { staffs: addStaffs, serial: trCount },
                        dataType : 'JSON',
                        success  : function (data) {
                            $('#access-form span.validation-error').html('');

                            if (data.status === true) {
                                if (data.html !== '') {
                                    $(data.html).each(function (index, tr) {
                                        $(tr).hide().appendTo(tbody).fadeIn(1150);
                                    });
                                    $('[data-toggle="tooltip"]').tooltip();
                                    formGroup.find("*[name='staffs[]']").val('');
                                    formGroup.find('.select2-hidden-accessible').trigger('change');

                                    if (typeof globalVar.perfectscroll.ps[perfectIndex] !== 'undefined') {
                                        globalVar.perfectscroll.ps[perfectIndex].update();
                                    }
                                }
                            } else if (data.errors !== null) {
                                $.each(data.errors, function (index, value) {
                                    $("#access-form span[field='" + index + "']").html(value);
                                });
                            } else {
                                alert('Something went wrong! Please try again.');
                            }

                            allowBtn.attr('disabled', false);
                        },
                        error : function (jqXHR, textStatus, errorThrown) {
                            // ajaxErrorHandler defined in js/app.js
                            ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
                            allowBtn.attr('disabled', false);
                        }
                    });
                } else {
                    formGroup.find("*[name='staffs[]']").val('');
                    formGroup.find('.select2-hidden-accessible').trigger('change');
                    allowBtn.attr('disabled', false);
                }
            });

            $('#access-form .save').on('click', function () {
                globalVar.ladda = Ladda.create(this);
                globalVar.ladda.start();
                $(this).attr('disabled', true);

                var form     = $(this).parent().parent().find('form');
                var formUrl  = form.prop('action');
                var formData = form.serialize();

                // Ajax request to save allowed users with given permissions related to the specified resource module
                $.ajax({
                    type     : 'POST',
                    url      : formUrl,
                    data     : formData,
                    dataType : 'JSON',
                    success  : function (data) {
                        if (data.status === true) {
                            $('#access-form span.validation-error').html('');

                            // Render access HTML with allowed users in the show page overview tab
                            if (data.html) {
                                $('#access').html(data.html);
                            }

                            // Update who is last updated of the specified resource
                            if (data.updatedBy != null) {
                                $("*[data-realtime='updated_by']").html(data.updatedBy);
                            }

                            if (typeof data.innerHtml !== 'undefined') {
                                $(data.innerHtml).each(function (index, value) {
                                    $(value[0]).html(value[1]);
                                });
                            }

                            $('[data-toggle="tooltip"]').tooltip();
                            nicescrollResize('html');
                            delayModalHide('#access-form', 1);
                        } else {
                            $('#access-form span.validation-error').html('');

                            $.each(data.errors, function (index, value) {
                                $("#access-form span[field='" + index + "']").html(value);
                            });
                        }

                        if (typeof globalVar.ladda !== 'undefined' && globalVar.ladda !== null) {
                            var statusClass = data.status ? 'success' : 'error';
                            $('#access-form .ladda-button[data-loading]').find('.ladda-label').addClass(statusClass);
                            globalVar.ladda.stop();
                            globalVar.ladda.remove();

                            setTimeout(function () {
                                $('#access-form .ladda-label').removeClass(statusClass);
                            }, 1500);
                        }

                        $('#access-form .save').attr('disabled', false);
                    },
                    error : function (jqXHR, textStatus, errorThrown) {
                        // ajaxErrorHandler defined in js/app.js
                        ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
                    }
                });
            });
        });
    </script>
@endpush
