@include('partials.tabs.tab-kanban', [
    'multiple_view' => true,
    'prefix'        => 'My',
    'item'          => 'issue',
    'data_default'  => 'issue_owner:' . $staff->id,
    'module_name'   => 'staff',
    'module'        => $staff,
    'module_id'     => $staff->id,
])
