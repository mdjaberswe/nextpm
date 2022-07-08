@include('partials.tabs.tab-files', [
    'module_name' => 'project',
    'module_id'   => $project->id,
    'can_create'  => $project->authCanDo('attachment_create'),
])
