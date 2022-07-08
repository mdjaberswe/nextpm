<div class="full">
	<h4 class="tab-title near">{{ $prefix or null }} Milestones</h4>

    <div class="right-top">
    	@if (isset($multiple_view) && $multiple_view == true)
            <div class="btn-group light">
                <a class="btn thin btn-regular active" data-toggle="tooltip" data-placement="bottom" title="Tabular"><i class="fa fa-list"></i></a>
                <a class="btn thin btn-regular tab-link" tabkey="{{ $tabkey or null }}" parent-tabkey="{{ $parent_tabkey or null }}" data-toggle="tooltip" data-placement="bottom" title="Kanban"><i class="fa fa-align-left rot-90"></i></a>
            </div>
        @endif

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

	<table id="datatable" class="table display responsive" cellspacing="0" width="100%" data-reorder="true" source="milestones" data-source-condition="{{ $module_name . '_id:' . $module_id }}" dataurl="{{ 'sequence-milestone/' .  $module_name . '/' . $module_id }}" datacolumn='{{ isset($table_format) ? $table_format['json_columns'] : $milestones_sequence_table['json_columns'] }}'>
		<thead>
			<tr>
                @if (! isset($drag_drop) || (isset($drag_drop) && $drag_drop == true))
    				<th data-priority="2" data-orderable="false" data-class-name="center all min-w45">
    					<span class="full center dragdrop" data-toggle="tooltip" data-placement="bottom" title="Drag&nbsp;&amp;&nbsp;Drop">
    						<i class="fa fa-arrows"></i>
    					</span>
    				</th>
                @endif

				<th data-priority="1" data-orderable="false" data-class-name="all max-w250">milestone</th>
				<th data-priority="4" data-orderable="false" data-class-name="center max-w65">progress</th>
				<th data-priority="8" data-orderable="false" data-class-name="center max-w80">tasks</th>
				<th data-priority="9" data-orderable="false" data-class-name="center max-w80">issues</th>
				<th data-priority="5" data-orderable="false" data-class-name="min-max-w80">{!! fill_up_space('start date') !!}</th>
				<th data-priority="6" data-orderable="false" data-class-name="min-max-w80">{!! fill_up_space('end date') !!}</th>
				<th data-priority="7" data-orderable="false" data-class-name="all">owner</th>

                @if ((isset($table_format) && $table_format['action'] == true) ||
                    (! isset($table_format) && isset($milestones_sequence_table['action']) && $milestones_sequence_table['action'] == true))
                    <th data-priority="3" data-orderable="false" data-class-name="align-r all" class="action-column"></th>
                @endif
			</tr>
		</thead>
	</table>
</div>
