<div class="modal-body perfectscroll">
    <div class="form-group mb0-imp">
        <div class="col-xs-12">
            <div class="table-filter none">
                <select name="name" id="import-name" class="white-select-type-single-b">
                    <option value="all">All Imported</option>
                    <option value="created">Added</option>
                    <option value="updated">Updated</option>
                    <option value="skipped">Skipped</option>
                </select>
            </div>

            <table id="modal-datatable" class="table modal-table responsive" cellspacing="0" width="100%" data-item="import" data-url="import-data"
                   data-column='{{ DataTable::jsonColumn(['name', 'due_date', 'status', 'priority', 'related_to', 'owner', 'error']) }}'
                   data-btn='{{ DataTable::showhideColumn(['checkbox' => false, 'action' => false, 'thead' => ['task name', 'due date', 'status', 'priority', 'related to', 'owner', 'errors / warnings']]) }}'>
                <thead>
                    <tr>
                        <th data-priority="1" data-class-name="all column-dropdown">Task Name</th>
                        <th data-priority="2">Due Date</th>
                        <th data-priority="3">Status</th>
                        <th data-priority="4">Priority</th>
                        <th data-priority="5">Related To</th>
                        <th data-priority="6">Owner</th>
                        <th data-priority="7">Errors / Warnings</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div> <!-- end form-group -->
</div> <!-- end modal-body -->
