@include('partials.tabs.tab-calendar', [
    'multiple_view' => permit('event.view'),
    'prefix'        => 'My',
    'module_name'   => 'staff',
    'module'        => $staff,
    'module_id'     => $staff->id,
    'data_default'  => 'event_owner:' . $staff->id,
])
