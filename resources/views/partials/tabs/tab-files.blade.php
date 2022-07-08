<div class="full">
    <h4 class="tab-title">Files</h4>

    <div class="right-top">
        @if ((! isset($can_create) && permit('attachment.create')) || (isset($can_create) && $can_create == true))
            <div class="dropdown clean inline-block">
                <a class="btn md btn-regular first dropdown-toggle mb5" animation="fadeIn|fadeOut" data-toggle="dropdown" aria-expanded="false">
                	<i class="mdi mdi-file-plus"></i> Add File
                </a>

                <ul class="dropdown-menu up-caret">
                    <li><a class="add-multiple" data-item="file" data-action="{{ route('admin.file.store') }}" data-content="partials.modals.upload-file" data-default="{{ 'linked_type:' . $module_name . '|linked_id:' . $module_id }}" save-new="false" data-modalsize="medium" modal-title="Add Files"><i class="fa fa-upload"></i> From Computer</a></li>
                    <li><a class="add-multiple" data-item="link" data-action="{{ route('admin.link.store') }}" data-content="partials.modals.add-link" data-default="{{ 'linked_type:' . $module_name . '|linked_id:' . $module_id }}" save-new="false" data-modalsize="" modal-title="Add Link"><i class="fa fa-link"></i> Add Link</a></li>
                </ul>
            </div>
        @endif
    </div>

    <table id="datatable" class="table display responsive" cellspacing="0" width="100%" dataurl="{{ 'file-data/' . $module_name . '/' . $module_id }}" datacolumn='{{ $files_table['json_columns'] }}' databtn='{{ DataTable::showhideColumn($files_table) }}' perpage="10">
        <thead>
            <tr>
                <th data-priority="1" data-class-name="all column-dropdown">name</th>
                <th data-priority="3">{!! fill_up_space('uploaded by') !!}</th>
                <th data-priority="5" data-class-name="min-w90-max-w100">{!! fill_up_space('date modified') !!}</th>
                <th data-priority="4">size</th>
                <th data-priority="2" data-orderable="false" data-class-name="align-r all" class="action-column"></th>
            </tr>
        </thead>
    </table>
</div> <!-- end full -->
