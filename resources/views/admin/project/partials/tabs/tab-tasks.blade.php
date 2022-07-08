@include('partials.tabs.tab-tasks', [
    'multiple_view'        => true,
    'parent_tabkey'        => 'tasks',
    'tabkey'               => 'taskskanban',
    'default_hide_columns' => ['related_to'],
    'module_name'          => 'project',
    'module_id'            => $project->id,
    'can_create'           => $project->authCanDo('task_create'),
])
