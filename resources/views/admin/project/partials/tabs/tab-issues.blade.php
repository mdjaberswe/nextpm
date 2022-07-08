@include('partials.tabs.tab-issues', [
    'multiple_view'        => true,
    'default_hide_columns' => ['related_to'],
    'tabkey'               => 'issueskanban',
    'parent_tabkey'        => 'issues',
    'module_name'          => 'project',
    'module_id'            => $project->id,
    'can_create'           => $project->authCanDo('issue_create'),
])
