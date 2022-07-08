<div class="full">
	<h4 class="tab-title near-border">Gantt Chart</h4>

	<div class="right-top">
		<div class="inline-block w55-mr3">
			{{ Form::select('gantt_per_page', [10 => '10', 25 => '25', 50 => '50', 75 => '75', 100 => '100'], 25, ['class' => 'white-select-type-single-b', 'data-project' => $project->id, 'data-default' => 25]) }}
		</div>

		<div class="inline-block w150">
			{{ Form::select('gantt_filter', $gantt_filter_list, $gantt_default_filter, ['class' => 'white-select-type-single', 'data-project' => $project->id, 'data-default' => $gantt_default_filter]) }}
		</div>
	</div>

	<div class="full">
		<div class="gantt" data-url="{{ route('admin.project.gantt.data', $project->id) }}" data-scale="days" data-min-scale="days" data-max-scale="weeks" data-per-page="25" data-title="{{ $project->name }}" data-progress="{{ $project->completion_percentage }}" data-create-form="admin.task.partials.form" data-create-url="{{ route('admin.task.store') }}" data-item="task" data-default="{{ 'related_type:project|related_id:' . $project->id }}" save-new="false"></div>
	</div>
</div>
