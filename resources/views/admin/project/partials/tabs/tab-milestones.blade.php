@include('partials.tabs.tab-milestones-sequence', [
    'module_name'   => 'project',
    'module'        => $project,
    'module_id'     => $project->id,
    'table_format'  => $project->getMilestoneTabTableFormat(),
    'can_create'    => $project->authCanDo('milestone_create'),
    'drag_drop'     => $project->authCanDo('milestone_edit'),
])
