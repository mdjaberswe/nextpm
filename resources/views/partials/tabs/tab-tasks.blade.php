<div class="full">
    <h4 class="tab-title">{{ $prefix or null }} Tasks</h4>

    <div class="right-top">
        @if (isset($multiple_view) && $multiple_view == true)
            <div class="btn-group light">
                <a class="btn thin btn-regular active" data-toggle="tooltip" data-placement="bottom" title="Tabular"><i class="fa fa-list"></i></a>
                <a class="btn thin btn-regular tab-link" tabkey="{{ $tabkey or null }}" parent-tabkey="{{ $parent_tabkey or null }}" data-toggle="tooltip" data-placement="bottom" title="Kanban"><i class="fa fa-align-left rot-90"></i></a>
            </div>
        @endif

        @if ((! isset($can_create) && permit('task.create')) || (isset($can_create) && $can_create == true))
            <button type="button" class="btn btn-regular add-multiple"
                data-item="task"
                data-action="{{ route('admin.task.store') }}"
                data-content="task.partials.form"
                @if (isset($data_default))
                    data-default="{{ $data_default }}"
                @else
                    data-default="{{ 'related_type:' . $module_name . '|related_id:' . $module_id }}"
                @endif
                save-new="false">
                <i class="fa fa-plus-circle"></i> Add Task
            </button>
        @endif
    </div>

    <div class="table-filter none">
        {!! DataTable::filterHtml($tasks_table['filter_input'], $module_name) !!}
    </div>

    <table id="datatable" class="table display responsive" cellspacing="0" width="100%" dataurl="{{ 'connected-task/' . $module_name . '/' . $module_id }}" datacolumn='{{ DataTable::jsonColumn($tasks_table['columns'], [], $default_hide_columns) }}' databtn='{{ DataTable::showhideColumn($tasks_table) }}' data-export="{{ permit('export.task') ? 'true' : 'false' }}" perpage="10">
        <thead>
            <tr>
                <th data-priority="1" data-class-name="all column-dropdown max-w330">{!! fill_up_space('task name') !!}</th>
                <th data-priority="3" data-class-name="min-max-w80">{!! fill_up_space('due date') !!}</th>
                <th data-priority="4" data-class-name="sync-val min-w80">status</th>
                <th data-priority="5" data-class-name="sync-val max-w80">progress</th>
                <th data-priority="6">priority</th>
                <th data-priority="8">{!! fill_up_space('related to') !!}</th>
                <th data-priority="7" data-class-name="all">owner</th>
                <th data-priority="2" data-orderable="false" data-class-name="align-r all" class="action-column"></th>
            </tr>
        </thead>
    </table>
</div> <!-- end full -->
