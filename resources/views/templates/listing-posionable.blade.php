@extends('layouts.master')

@section('content')

    <div class="row">
        @if (isset($page['subnav']))
            @include('partials.subnav.' . $page['subnav'])
        @endif

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-10 panel">
            <h4 class="tab-title bold near">@if (isset($page['list_title'])) {{ $page['list_title'] }} @else {{ $page['item'] }} List @endif</h4>

            <div class="right-top">
                @if (! isset($page['modal_create']) || isset($page['modal_create']) && $page['modal_create'] == true)
                    @if (isset($page['permission']) && permit($page['permission'] . '.create'))
                        <button type="button" id="add-new-btn" class="btn btn-info">
                            <i class="{{ $page['add_icon'] or 'fa fa-plus-circle' }}"></i>
                            Add {{ $page['item'] }}
                        </button>
                    @endif
                @endif
            </div>

            <table id="datatable" class="table display responsive reorderable" source="{{ $page['field'] }}" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        @if (! isset($table['drag_drop']) || (isset($table['drag_drop']) && $table['drag_drop'] == true))
                            <th data-priority="1" data-orderable="false" data-class-name="center all min-w45">
                                <span class="full center dragdrop" data-toggle="tooltip" data-placement="bottom" title="Drag&nbsp;&amp;&nbsp;Drop">
                                    <i class="fa fa-arrows"></i>
                                </span>
                            </th>
                        @endif

                        @foreach ($table['thead'] as $key => $thead)
                            @if (is_array($thead))
                                <th data-priority="{{ ($key + 3) }}" data-orderable="false" data-class-name="{{ $thead['data_class'] or null }}" style="{{ $thead['style'] or null }}">{{ fill_up_space($thead[0]) }}</th>
                            @else
                                <th data-priority="{{ ($key + 3) }}" data-orderable="false">{{ fill_up_space($thead) }}</th>
                            @endif
                        @endforeach

                        @if (! isset($table['action']) || (isset($table['action']) && $table['action'] == true))
                            <th data-priority="2" data-orderable="false" data-column-type="action" data-class-name="align-r all" class="{{ $table['action_class'] or 'action-column' }}" style="{{ $table['action_style'] or null }}">actions</th>
                        @endif
                    </tr>
                </thead>
            </table>
        </div> <!-- end panel -->
    </div> <!-- end row -->

@endsection

@section('modals')
    @include('partials.modals.initialize', ['yield' => false])
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            var source     = $('#datatable').attr('source');
            var rowReorder = $('#datatable th .dragdrop').get(0) ? { update: false } : false;
            $('.pretty').find('input').prop('checked', false);

            // Initialize Datatable
            var table = $('#datatable').on('init.dt', function () {
                $('[data-toggle="tooltip"]').tooltip();
                nicescrollResize('html');
            }).on('error.dt', function (e, settings, techNote, message) {
                // dataTableError defined in js/app.js
                return dataTableError(message, '#datatable');
            }).DataTable({
                dom            : "<'full paging-false'r<'table-responsive m0-imp't>>",
                paging         : false,
                order          : [],
                processing     : true,
                oLanguage      : { sProcessing: '' },
                serverSide     : true,
                ajax           : { url: '{{ route($page['plain_route'] . '.data') }}', type: 'POST' },
                columns        : [ {!! $table['json_columns'] !!} ],
                rowReorder     : rowReorder,
                fnDrawCallback : function (oSettings) {
                    $('[data-toggle="tooltip"]').tooltip();
                    nicescrollResize('html');
                    perfectScrollbarInit();

                    var requestUrl = '{{ route('admin.dropdown.list') }}';
                    var data       = { source: source, orderby: 'position' };
                    var topItem    = { value: 0, text: 'AT TOP' };
                    var bottomItem = { value: -1, text: 'AT BOTTOM' };
                    // ajaxDropdownList defined in js/app.js
                    ajaxDropdownList(requestUrl, data, '.position', topItem, bottomItem, true, 'AFTER : ', '', false);
                }
            });

            globalVar.jqueryDataTable = table;

            // Ajax request to update data table item's position by drag-drop up and down
            table.on('row-reorder', function (e, diff, edit) {
                var positions = [];

                $("input[name='positions[]']").each(function (index) {
                    var positionInput = $("input[name='positions[]']").get(index);
                    positions.push(positionInput.value);
                });

                $.ajax({
                    type : 'POST',
                    url  : globalVar.baseAdminUrl + '/dropdown-reorder',
                    data : { source: source, positions: positions }
                });
            });

            // Datatable item's actions dropdown box toggle open|close event
            $('#datatable tbody').on('click.dt', '.dropdown-toggle', function (event) {
                var dropdownHeight = $(this).next('.dropdown-menu').height() + 2;
                var tr             = $(this).closest('tr');
                var bottomHeight   = 0;

                tr.nextAll('tr').each(function (index, value) {
                    bottomHeight += $(value).height();
                });

                if (bottomHeight <= dropdownHeight) {
                    $(this).parent('.dropdown').addClass('dropup');
                }
            });

            // Click event: Open edit modal with default data
            $('#datatable tbody').on('click.dt', '.edit', function (event) {
                var id         = $(this).attr('editid');
                var data       = { id: id };
                var url        = '{{ route($page['route'] . '.index') }}' + '/' + id + '/edit';
                var updateUrl  = '{{ route($page['route'] . '.index') }}' + '/' + id;
                var select2Arg = { containerCssClass: 'white-container', dropdownCssClass: 'white-dropdown' };

                // disabledCurrentItem defined in js/app.js
                // getEditData defined in modals/edit.blade.php
                disabledCurrentItem('.position', id, true, '.white-select-type-single', select2Arg);
                getEditData(id, data, url, updateUrl);
            });

            // Click event: Confirm alert before deleting an item
            $('#datatable tbody').on('click.dt', '.delete', function (event) {
                event.preventDefault();

                if ($(this).hasClass('disabled')) {
                    $.notify({ message: 'This {{ strtolower($page['item']) }} is used in other modules.' }, globalVar.dangerNotify);

                    return false;
                }

                var formUrl  = $(this).parent('form').prop('action');
                var formData = $(this).parent('form').serialize();
                var itemName = '{{ $page['item'] }}';
                var message  = 'This {{ strtolower($page['item']) }} will be removed along with all associated data.<br>Are you sure you want to delete this {{ strtolower($page['item']) }}?';
                // confirmDelete defined in modals/delete.blade.php
                confirmDelete(formUrl, formData, table, itemName, message, null);
            });
        });
    </script>

    @if (isset($page['script']) && $page['script'] == true)
        @include($page['view'] . '.partials.script')
    @endif
@endpush
