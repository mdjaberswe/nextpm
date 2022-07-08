@include('partials.tabs.tab-tasks', [
    'multiple_view'        => true,
    'prefix'               => 'My',
    'parent_tabkey'        => 'tasks',
    'tabkey'               => 'taskskanban',
    'default_hide_columns' => ['task_owner'],
    'data_default'         => 'task_owner:' . $staff->id,
    'module_name'          => 'staff',
    'module_id'            => $staff->id,
])
