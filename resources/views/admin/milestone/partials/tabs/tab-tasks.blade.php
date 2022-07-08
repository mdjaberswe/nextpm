@include('partials.tabs.tab-tasks', [
    'multiple_view'        => true,
    'can_create'           => $milestone->project->authCanDo('task_create'),
    'module_id'            => $milestone->id,
    'module_name'          => 'milestone',
    'parent_tabkey'        => 'tasks',
    'tabkey'               => 'taskskanban',
    'default_hide_columns' => ['related_to'],
    'data_default'         => 'related_type:project|related_id:' . $milestone->project_id . '|milestone_id:' . $milestone->id . '|milestone_val:' . $milestone->id,
])
