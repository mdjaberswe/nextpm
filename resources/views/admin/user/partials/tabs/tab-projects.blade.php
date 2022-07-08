@include('partials.tabs.tab-projects', [
    'multiple_view' => true,
    'prefix'        => 'My',
    'parent_tabkey' => 'projects',
    'tabkey'        => 'projectskanban',
    'data_default'  => 'project_owner:' . $staff->id,
    'module_name'   => 'staff',
    'module_id'     => $staff->id,
])
