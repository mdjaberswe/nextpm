@include('partials.tabs.tab-events', [
    'multiple_view'        => true,
    'can_create'           => $project->authCanDo('event_create'),
    'module_id'            => $project->id,
    'module'               => $project,
    'module_name'          => 'project',
    'tabkey'               => 'events',
    'parent_tabkey'        => 'calendar',
    'default_hide_columns' => ['related_to'],
])
