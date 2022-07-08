<div class="full">
    <h4 class="tab-title">{{ $prefix or null }} Milestones</h4>

    <div class="right-top">
        @if ((! isset($can_create) && permit('milestone.create')) || (isset($can_create) && $can_create == true))
            <button type="button" class="btn btn-regular add-multiple"
                data-item="milestone"
                data-action="{{ route('admin.milestone.store') }}"
                data-content="milestone.partials.form"
                @if (isset($data_default))
                    data-default="{{ $data_default }}"
                @else
                    data-default="{{ $module_name . '_id:' . $module_id }}"
                @endif
                save-new="false"
                data-modalsize="medium">
                <i class="fa fa-plus-circle"></i> Add Milestone
            </button>
        @endif
    </div>

    <table id="datatable" class="table display responsive" cellspacing="0" width="100%" dataurl="{{ 'connected-milestone/' . $module_name . '/' . $module_id }}" datacolumn='{{ DataTable::jsonColumn($milestones_table['columns'], [], $default_hide_columns) }}' databtn='{{ DataTable::showhideColumn($milestones_table) }}' perpage="10">
        <thead>
            <tr>
                <th data-priority="1" data-class-name="all column-dropdown max-w200">milestone</th>
                <th data-priority="3" data-class-name="center max-w65">progress</th>
                <th data-priority="8" data-class-name="center min-max-w80">tasks</th>
                <th data-priority="9" data-class-name="center min-max-w80">issues</th>
                <th data-priority="4" data-class-name="min-max-w80">{!! fill_up_space('start date') !!}</th>
                <th data-priority="5" data-class-name="min-max-w80">{!! fill_up_space('end date') !!}</th>
                <th data-priority="6" data-class-name="min-w150">project</th>
                <th data-priority="7" data-class-name="all">owner</th>
                <th data-priority="2" data-orderable="false" data-class-name="align-r all" class="action-column"></th>
            </tr>
        </thead>
    </table>
</div> <!-- end full -->
