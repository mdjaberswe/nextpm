@include('partials.tabs.tab-kanban', [
    'multiple_view' => true,
    'can_create'    => $milestone->project->authCanDo('issue_create'),
    'module_id'     => $milestone->id,
    'module'        => $milestone,
    'module_name'   => 'milestone',
    'item'          => 'issue',
    'data_default'  => 'related_type:project|related_id:' . $milestone->project_id .
                       '|release_milestone_id:' . $milestone->id . '|affected_milestone_id:' . $milestone->default_affected_id .
                       '|milestone_val:' . $milestone->id . '|affected_milestone_val:' . $milestone->default_affected_id,
])
