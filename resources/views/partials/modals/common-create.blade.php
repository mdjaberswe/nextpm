<div class="modal fade large" id="common-add">
    <div class="modal-dialog">
        <div class="modal-loader">
            <div class="spinner"></div>
        </div>

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title capitalize">Add New</h4>
            </div> <!-- end modal-header -->

            {{ Form::open(['route' => null, 'method' => 'post', 'class' => 'modal-form']) }}
                <div id="common-add-content"></div>
            {{ Form::close() }}

            <div class="modal-footer space btn-container">
                <button type="button" class="cancel btn btn-default" data-dismiss="modal">Cancel</button>

                <button type="button" class="save-new btn btn-default ladda-button" data-style="expand-right" data-spinner-color="#666">
                    <span class="ladda-label">Save and New</span>
                </button>

                <button type="button" class="save btn btn-info ladda-button" data-style="expand-right">
                    <span class="ladda-label">Save</span>
                </button>
            </div> <!-- end modal-footer -->
        </div>
    </div> <!-- end modal-dialog -->
</div> <!-- end add-multiple-form -->

@push('scripts')
    <script>
        $(document).ready(function () {
            // Click event to open common create modal
            $(document).on('click', '.add-multiple', function () {
                openCommonCreateModal($(this), null, null, null, null);
            });

            // Ajax request for saving data and respond accordingly by modalDataStore() function
            $('#common-add .save').on('click', function () {
                var form          = $(this).parent().parent().find('form');
                var listOrder     = true;
                var tableDraw     = true;
                var thisListOrder = $('#common-add .modal-body').attr('data-listorder');
                var thisTableDraw = $('#common-add .modal-body').attr('data-tabledraw');

                if (typeof thisListOrder !== 'undefined' && thisListOrder === 'false') {
                    listOrder = false;
                }

                if (typeof thisTableDraw !== 'undefined' && thisTableDraw === 'false') {
                    tableDraw = false;
                }

                globalVar.ladda = Ladda.create(this);
                globalVar.ladda.start();
                $(this).attr('disabled', true);
                $(this).closest('.modal-footer').find('.save-new').attr('disabled', true);

                // modalDataStore defined in js/app.js
                modalDataStore('#common-add', form, listOrder, false, tableDraw);
            });

            $('#common-add .save-new').on('click', function () {
                var form          = $(this).parent().parent().find('form');
                var listOrder     = true;
                var thisListOrder = $('#common-add .modal-body').attr('data-listorder');

                if (typeof thisListOrder !== 'undefined' && thisListOrder === 'false') {
                    listOrder = false;
                }

                globalVar.ladda = Ladda.create(this);
                globalVar.ladda.start();
                $(this).attr('disabled', true);
                $(this).closest('.modal-footer').find('.save').attr('disabled', true);

                // modalDataStore defined in js/app.js
                modalDataStore('#common-add', form, listOrder, true, true);
            });
        });
    </script>
@endpush
