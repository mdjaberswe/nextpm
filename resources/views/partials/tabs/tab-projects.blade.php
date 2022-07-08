<div class="full overflow-table">
    <h4 class="tab-title">{{ $prefix or null }} Projects</h4>

     <div class="right-top">
        @if (isset($multiple_view) && $multiple_view == true)
            <div class="btn-group light">
                <a class="btn thin btn-regular active" data-toggle="tooltip" data-placement="bottom" title="Tabular"><i class="fa fa-list"></i></a>
                <a class="btn thin btn-regular tab-link" tabkey="{{ $tabkey or null }}" parent-tabkey="{{ $parent_tabkey or null }}" data-toggle="tooltip" data-placement="bottom" title="Kanban"><i class="fa fa-align-left rot-90"></i></a>
            </div>
        @endif

        @permission('project.create')
            <button type="button" class="btn btn-regular add-multiple" data-item="project"
             data-action="{{ route('admin.project.store') }}"
             data-content="project.partials.form"
             @if (isset($data_default))
                 data-default="{{ $data_default }}"
             @else
                 data-default="{{ 'related_type:' . $module_name . '|related_id:' . $module_id }}"
             @endif
             save-new="false"
             data-modalsize="medium">
                <i class="fa fa-plus-circle"></i> Add Project
            </button>
        @endpermission
     </div>

    <table id="datatable" class="table display responsive" cellspacing="0" width="100%" dataurl="{{ 'connected-project/' . $module_name . '/' . $module_id }}" datacolumn='{{ $projects_table['json_columns'] }}' databtn='{{ DataTable::showhideColumn($projects_table) }}' data-export="{{ permit('export.project') ? 'true' : 'false' }}" data-containerclass="overflow-top scroll-box-x only-thumb" perpage="10">
        <thead>
            <tr>
                <th data-priority="1" data-class-name="all column-dropdown min-w170-max-w180">{!! fill_up_space('project name') !!}</th>
                <th data-priority="3" data-class-name="center narrow max-w65">progress</th>
                <th data-priority="7" data-class-name="center max-w80">tasks</th>
                <th data-priority="8" data-class-name="center max-w80">milestones</th>
                <th data-priority="9" data-class-name="center max-w80">issues</th>
                <th data-priority="5" data-class-name="min-max-w80">{!! fill_up_space('start date') !!}</th>
                <th data-priority="6" data-class-name="min-max-w80">{!! fill_up_space('end date') !!}</th>
                <th data-priority="4" data-class-name="min-w120">members</th>
                <th data-priority="2" data-orderable="false" data-class-name="align-r all" class="action-column"></th>
            </tr>
        </thead>
    </table>
</div> <!-- end full -->
