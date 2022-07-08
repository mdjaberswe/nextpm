@include('partials.tabs.tab-calendar', [
    'module_name'   => 'milestone',
    'module_id'     => $milestone->id,
    'module'        => $milestone,
    'multiple_view' => false,
    'can_create'    => false,
])
