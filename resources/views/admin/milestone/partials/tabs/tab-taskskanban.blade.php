@include('partials.tabs.tab-kanban', [
    'multiple_view' => true,
    'percentage'    => true,
    'can_create'    => $milestone->project->authCanDo('task_create'),
    'module_id'     => $milestone->id,
    'module'        => $milestone,
    'module_name'   => 'milestone',
    'item'          => 'task',
    'data_default'  => 'related_type:project|related_id:' . $milestone->project_id . '|milestone_id:' . $milestone->id . '|milestone_val:' . $milestone->id,
])
