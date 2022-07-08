@include('partials.tabs.tab-milestones', [
    'prefix'               => 'My',
    'tabkey'               => null,
    'parent_tabkey'        => null,
    'default_hide_columns' => ['milestone_owner'],
    'data_default'         => 'milestone_owner:' . $staff->id,
    'module_name'          => 'staff',
    'module'               => $staff,
    'module_id'            => $staff->id,
])
