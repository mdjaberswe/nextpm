<div class="modal-body perfectscroll">
    <div class="form-group mb0-imp">
        <div class="col-xs-12">
            <table id="modal-datatable" class="table modal-table responsive middle" cellspacing="0" width="100%" data-item="member" data-url="project-member-data" data-column='{{ DataTable::jsonColumn(['name', 'phone', 'email', 'tasks', 'issues']) }}' data-btn='{{ DataTable::showhideColumn(['checkbox' => false, 'action' => false, 'thead' => ['user', 'phone', 'email', 'tasks', 'issues']]) }}'>
                <thead>
                    <tr>
                        <th data-priority="1" data-class-name="all column-dropdown avt-exists">USER</th>
                        <th data-priority="2">PHONE</th>
                        <th data-priority="3">EMAIL</th>
                        <th data-priority="4" data-class-name="max-w80-imp">{!! fill_up_space('OWN TASKS') !!}</th>
                        <th data-priority="5" data-class-name="max-w80-imp">{!! fill_up_space('OWN ISSUES') !!}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div> <!-- end form-group -->
</div> <!-- end modal-body -->
