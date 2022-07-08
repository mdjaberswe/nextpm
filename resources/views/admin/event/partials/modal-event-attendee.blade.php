<div class="modal-body perfectscroll">
    <div class="form-group">
        <div class="col-xs-9">
            {{ Form::select('attendees[]', $attendees_list, null, ['class' => 'form-control white-select-type-multiple', 'multiple' => 'multiple', 'data-placeholder' => 'Add new attendees']) }}
            <span field="attendees" class="validation-error"></span>
        </div>

        <div class="inline-block btn-container">
            <button type="button" class="add-attendee btn thin-both btn-warning">Add</button>
        </div>
    </div> <!-- end form-group -->

    <div class="form-group mb0-imp datatable-left">
        <div class="col-xs-12">
            <div class="table-filter none">
                {!! DataTable::filterHtml($attendees_table['filter_input'], 'event_attendee', true) !!}
            </div>

            <table id="modal-datatable" class="table modal-table responsive middle" cellspacing="0" width="100%" data-item="event_attendee" data-url="event-attendee-data" data-addurl="event-attendee-store" data-column='{{ $attendees_table['json_columns'] }}' data-btn='{{ DataTable::showhideColumn($attendees_table) }}'>
                <thead>
                    <tr>
                        <th data-priority="1" data-class-name="all column-dropdown avt-exists">NAME</th>
                        <th data-priority="3">PHONE</th>
                        <th data-priority="4">EMAIL</th>
                        <th data-priority="5">TYPE</th>
                        <th data-priority="2" data-orderable="false" data-class-name="align-r" class="action-column"></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div> <!-- end form-group -->
</div> <!-- end modal-body -->
