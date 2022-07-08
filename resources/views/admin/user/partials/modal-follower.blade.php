<div class="modal-body perfectscroll">
    <div class="form-group mb0-imp">
        <div class="col-xs-12">
            <table id="modal-datatable" class="table modal-table responsive middle" cellspacing="0" width="100%" data-item="follower" data-url="{{ 'follower-data' }}" data-column='{{ $follower_table['json_columns'] }}' data-btn='{{ DataTable::showhideColumn($follower_table) }}'>
                <thead>
                    <tr>
                        <th data-priority="1" data-class-name="all column-dropdown avt-exists">NAME</th>
                        <th data-priority="2">PHONE</th>
                        <th data-priority="3">EMAIL</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div> <!-- end form-group -->
</div> <!-- end modal-body -->
