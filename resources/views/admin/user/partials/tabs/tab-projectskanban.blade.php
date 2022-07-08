@include('partials.tabs.tab-kanban', [
    'multiple_view' => true,
    'prefix'        => 'My',
    'item'          => 'project',
    'data_default'  => 'project_owner:' . $staff->id,
    'module_name'   => 'staff',
    'module'        => $staff,
    'module_id'     => $staff->id,
])
