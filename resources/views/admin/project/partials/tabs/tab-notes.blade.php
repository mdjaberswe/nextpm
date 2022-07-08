@include('partials.tabs.tab-notes', [
    'module_name' => 'project',
    'module'      => $project,
    'module_id'   => $project->id,
    'can_create'  => $project->authCanDo('note_create'),
])
