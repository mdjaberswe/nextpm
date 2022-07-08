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
                data-column='{{ DataTable::jsonColumn(['name', 'start_date', 'end_date', 'status', 'owner', 'error']) }}'
                data-btn='{{ DataTable::showhideColumn(['checkbox' => false, 'action' => false, 'thead' => ['project name', 'start date', 'end date', 'status', 'owner', 'errors / warnings']]) }}'>
                <thead>
                    <tr>
                        <th data-priority="1" data-class-name="all column-dropdown">Project Name</th>
                        <th data-priority="2">Start Date</th>
                        <th data-priority="3">End Date</th>
                        <th data-priority="4">Status</th>
                        <th data-priority="5">Owner</th>
                        <th data-priority="6">Errors / Warnings</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div> <!-- end form-group -->
</div> <!-- end modal-body -->
