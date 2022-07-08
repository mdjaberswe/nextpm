@include('partials.tabs.tab-kanban', [
    'multiple_view' => true,
    'percentage'    => true,
    'prefix'        => 'My',
    'item'          => 'task',
    'data_default'  => 'task_owner:' . $staff->id,
    'module_name'   => 'staff',
    'module'        => $staff,
    'module_id'     => $staff->id,
])
