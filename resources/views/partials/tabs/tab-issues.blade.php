<div class="full">
    <h4 class="tab-title">{{ $prefix or null }} Issues</h4>

    <div class="right-top">
        @if (isset($multiple_view) && $multiple_view == true)
            <div class="btn-group light">
                <a class="btn thin btn-regular active" data-toggle="tooltip" data-placement="bottom" title="Tabular"><i class="fa fa-list"></i></a>
                <a class="btn thin btn-regular tab-link" tabkey="{{ $tabkey }}" parent-tabkey="{{ $parent_tabkey }}" data-toggle="tooltip" data-placement="bottom" title="Kanban"><i class="fa fa-align-left rot-90"></i></a>
            </div>
        @endif

        @if ((! isset($can_create) && permit('issue.create')) || (isset($can_create) && $can_create == true))
            <button type="button" class="btn btn-regular add-multiple" data-item="issue"
                data-action="{{ route('admin.issue.store') }}"
                data-content="issue.partials.form"
                @if (isset($data_default))
                	data-default="{{ $data_default }}"
                @else
                	data-default="{{ 'related_type:' . $module_name . '|related_id:' . $module_id }}"
                @endif
                save-new="false">
                <i class="fa fa-plus-circle"></i> Add Issue
            </button>
        @endif
    </div>

    <div class="table-filter none">
        {!! DataTable::filterHtml($issues_table['filter_input'], $module_name) !!}
    </div>

	<table id="datatable" class="table display responsive" cellspacing="0" width="100%" dataurl="{{ 'connected-issue/' . $module_name . '/' . $module_id }}" datacolumn='{{ DataTable::jsonColumn($issues_table['columns'], [], $default_hide_columns) }}' databtn='{{ DataTable::showhideColumn($issues_table) }}' data-export="{{ permit('export.issue') ? 'true' : 'false' }}" perpage="10">
		<thead>
			<tr>
				<th data-priority="1" data-class-name="all column-dropdown max-w330">issue</th>
				<th data-priority="3" data-class-name="min-max-w80">{!! fill_up_space('due date') !!}</th>
				<th data-priority="4" data-class-name="sync-val min-w80">status</th>
				<th data-priority="5">severity</th>
                <th data-priority="7">{!! fill_up_space('related to') !!}</th>
				<th data-priority="6" data-class-name="all">owner</th>
				<th data-priority="2" data-orderable="false" data-class-name="align-r all" class="action-column"></th>
			</tr>
		</thead>
	</table>
</div> <!-- end full -->
