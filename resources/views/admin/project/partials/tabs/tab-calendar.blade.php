@include('partials.tabs.tab-calendar', [
    'multiple_view' => $project->access == 'private' ? $project->authCanDo('event.view') : permit('event.view'),
    'module_name'   => 'project',
    'module'        => $project,
    'module_id'     => $project->id,
    'can_create'    => $project->authCanDo('event.create'),
])
