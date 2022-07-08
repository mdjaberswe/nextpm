@include('partials.tabs.tab-kanban', [
    'multiple_view' => true,
    'percentage'    => true,
    'item'          => 'task',
    'module_name'   => 'project',
    'module_id'     => $project->id,
    'module'        => $project,
    'can_create'    => $project->authCanDo('task_create'),
])
