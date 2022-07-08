@include('partials.tabs.tab-issues', [
    'multiple_view'        => true,
    'prefix'               => 'My',
    'parent_tabkey'        => 'issues',
    'tabkey'               => 'issueskanban',
    'default_hide_columns' => ['issue_owner'],
    'data_default'         => 'issue_owner:' . $staff->id,
    'module_name'          => 'staff',
    'module_id'            => $staff->id,
])
