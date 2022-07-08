@include('partials.tabs.tab-kanban', [
    'multiple_view' => true,
    'item'          => 'issue',
    'module_name'   => 'project',
    'module'        => $project,
    'module_id'     => $project->id,
    'can_create'    => $project->authCanDo('issue_create'),
])
