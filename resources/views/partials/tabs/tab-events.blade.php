<div class="full overflow-table">
	<h4 class="tab-title">{{ $prefix or null }} Events</h4>

    <div class="right-top">
        @if (isset($multiple_view) && $multiple_view == true)
            <div class="btn-group light">
                <a class="btn thin btn-regular tab-link" tabkey="calendar" data-toggle="tooltip" data-placement="bottom" title="Calendar"><i class="fa fa-calendar"></i></a>
                <a class="btn thin btn-regular active" data-toggle="tooltip" data-placement="bottom" title="Tabular"><i class="fa fa-list"></i></a>
            </div>
        @endif

        @if ((! isset($can_create) && permit('event.create')) || (isset($can_create) && $can_create == true))
       		<button type="button" class="btn btn-regular add-multiple" data-item="event"
                data-action="{{ route('admin.event.store') }}"
                data-content="event.partials.form"
                @if (isset($data_default))
                    data-default="{{ $data_default }}"
                @else
                    data-default="{{ 'related_type:' . $module_name . '|related_id:' . $module_id }}"
                @endif
                save-new="false">
       			<i class="fa fa-plus-circle"></i> Add Event
       		</button>
        @endif
    </div>

    <table id="datatable" class="table display responsive" cellspacing="0" width="100%" dataurl="{{ 'connected-event/' . $module_name . '/' . $module_id }}" datacolumn='{{ DataTable::jsonColumn($events_table['columns'], [], $default_hide_columns) }}' databtn='{{ DataTable::showhideColumn($events_table) }}' data-export="{{ permit('export.event') ? 'true' : 'false' }}" data-containerclass="overflow-top scroll-box-x only-thumb" perpage="10">
		<thead>
			<tr>
				<th data-priority="1" data-class-name="all column-dropdown max-w280">{!! fill_up_space('event name') !!}</th>
				<th data-priority="5" data-class-name="min-max-w80">{!! fill_up_space('start date') !!}</th>
				<th data-priority="6" data-class-name="min-max-w80">{!! fill_up_space('end date') !!}</th>
				<th data-priority="7">location</th>
                <th data-priority="8">priority</th>
                <th data-priority="9">{!! fill_up_space('related to') !!}</th>
				<th data-priority="4" data-class-name="min-max-w120">attendees</th>
				<th data-priority="3" data-class-name="all">owner</th>
				<th data-priority="2" data-orderable="false" data-class-name="align-r all" class="action-column"></th>
			</tr>
		</thead>
	</table>
</div> <!-- end full -->
