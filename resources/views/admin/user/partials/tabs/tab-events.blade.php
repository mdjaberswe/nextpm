@include('partials.tabs.tab-events', [
    'multiple_view'        => true,
    'prefix'               => 'My',
    'tabkey'               => 'events',
    'parent_tabkey'        => 'calendar',
    'default_hide_columns' => ['event_owner'],
    'data_default'         => 'event_owner:' . $staff->id,
    'module_name'          => 'staff',
    'module'               => $staff,
    'module_id'            => $staff->id,
])
