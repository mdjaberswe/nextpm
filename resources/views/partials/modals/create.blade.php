<div class="modal fade {{ $page['modal_size'] or 'large' }}" id="add-new-form">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@if (isset($page['modal_icon'])) <i class="{{ $page['modal_icon'] }}"></i> @endif Add New {{ $page['item'] }}</h4>
            </div> <!-- end modal-header -->

            @if (! isset($yield) || (isset($yield) && $yield == true))
                @yield('modalcreate')
            @else
                {{ Form::open(['route' => $page['route'] . '.store', 'method' => 'post', 'class' => 'modal-form']) }}
                    @include($page['view'] . '.partials.form', ['form' => 'create'])
                {{ Form::close() }}
            @endif

            <div class="modal-footer space btn-container">
                <button type="button" class="cancel btn btn-default" data-dismiss="modal">Cancel</button>

                @if (! isset($page['save_and_new']) || (isset($page['save_and_new']) && $page['save_and_new'] == true))
                    <button type="button" class="save-new btn btn-default ladda-button" data-style="expand-right" data-spinner-color="#666">
                        <span class="ladda-label">Save and New</span>
                    </button>
                @endif

                <button type="button" class="save btn btn-info ladda-button" data-style="expand-right">
                    <span class="ladda-label">Save</span>
                </button>
            </div> <!-- end modal-footer -->
        </div>
    </div> <!-- end modal-dialog -->
</div> <!-- end add-new-form -->

@push('scripts')
    <script>
        $(document).ready(function () {
            // Click event to reset and open create modal
            $('#add-new-btn').on('click', function () {
                $('#add-new-form form').trigger('reset');
                $('#add-new-form form').find('.select2-hidden-accessible').trigger('change');
                $('#add-new-form form').find('option').prop('disabled', false);
                $('#add-new-form form').find('.white-select-type-single').select2('destroy').select2({ containerCssClass: 'white-container', dropdownCssClass: 'white-dropdown' });
                $('#add-new-form form').find('.white-select-type-single-b').select2('destroy').select2({ minimumResultsForSearch: -1, containerCssClass: 'white-container', dropdownCssClass: 'white-dropdown' });
                $('#add-new-form .none').slideUp();
                $('#add-new-form span.validation-error').html('');
                $('#add-new-form .modal-body').animate({ scrollTop: 1 });
                $('#add-new-form .save').attr('disabled', false);
                $('#add-new-form .save-new').attr('disabled', false);

                $('#add-new-form .datepicker').each(function (index, value) {
                    $(this).datepicker('update', $(this).val());
                });

                // Reset to field default values
                if (typeof $(this).attr('data-default') !== 'undefined') {
                    var fieldSet = $(this).attr('data-default').split('|');

                    $(fieldSet).each(function (index, singleField) {
                        var fieldData = singleField.split(':');
                        $("#add-new-form *[name='" + fieldData[0] + "']").val(fieldData[1]).trigger('change');
                    });
                }

                if (typeof globalVar.ladda !== 'undefined' && globalVar.ladda !== null) {
                    globalVar.ladda.remove();
                }

                $('#add-new-form').modal({
                    show     : true,
                    backdrop : false,
                    keyboard : false
                });

                $('#add-new-form .modal-body').animate({ scrollTop: 0 });
            });

            // Ajax request for saving data and respond accordingly by modalDataStore
            $('#add-new-form .save').on('click', function () {
                var form      = $(this).parent().parent().find('form');
                var listOrder = true;

                @if (isset($table['list_order']))
                    listOrder = false;
                @endif

                globalVar.ladda = Ladda.create(this);
                globalVar.ladda.start();
                $(this).attr('disabled', true);
                $(this).closest('.modal-footer').find('.save-new').attr('disabled', true);
                // modalDataStore defined in js/app.js
                modalDataStore('#add-new-form', form, listOrder, false, true);
            });

            // Ajax request for saving data and reopen create modal
            $('#add-new-form .save-new').on('click', function () {
                var form      = $(this).parent().parent().find('form');
                var listOrder = true;

                @if (isset($table['list_order']))
                    listOrder = false;
                @endif

                globalVar.ladda = Ladda.create(this);
                globalVar.ladda.start();
                $(this).attr('disabled', true);
                $(this).closest('.modal-footer').find('.save').attr('disabled', true);
                // modalDataStore defined in js/app.js
                modalDataStore('#add-new-form', form, listOrder, true, true);
            });

            // Click event to open common create modal
            $(document).on('click', '.add-new-common', function () {
                openCommonCreateModal($(this), '#add-new-form', '#add-new-content', null, null);
            });
        });

        /**
         * Appear to add a new modal
         */
        function addNewEvent () {
            $('#add-new-form form').trigger('reset');
            $('#add-new-form form').find('.select2-hidden-accessible').trigger('change');
            $('#add-new-form form').find('option').prop('disabled', false);
            $('#add-new-form form').find('.white-select-type-single').select2('destroy').select2({ containerCssClass: 'white-container', dropdownCssClass: 'white-dropdown' });
            $('#add-new-form form').find('.white-select-type-single-b').select2('destroy').select2({ minimumResultsForSearch: -1, containerCssClass: 'white-container', dropdownCssClass: 'white-dropdown' });
            $('#add-new-form .none').slideUp();
            $('#add-new-form span.validation-error').html('');

            if (typeof globalVar.ladda !== 'undefined' && globalVar.ladda !== null) {
                globalVar.ladda.remove();
            }

            $('#add-new-form').modal({
                show     : true,
                backdrop : false,
                keyboard : false
            });

            $('#add-new-form .modal-body').animate({ scrollTop: 0 });
        }
    </script>
@endpush
