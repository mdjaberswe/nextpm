{{ Form::open(['route' => 'admin.import.post', 'method' => 'post', 'class' => 'modal-form']) }}
    <div class="modal-body perfectscroll">
        {{ Form::hidden('import', null) }}

        <div class="form-group">
            <div class="col-xs-12">
                <p class="para-clean">Map the source file's column with the appropriate {{ $module }} fields.</p>

                <div class="alert-note warning">
                    <p>You've to map the <strong>{{ ucfirst($module) }} Name</strong> mandatory field to start importing data.</p>
                </div>
            </div>
        </div> <!-- end form-group -->

        <div class="full">
            <div class="col-xs-12 error-content"></div>
        </div>

        <div class="form-group mt15-imp">
            <div class="col-xs-12 table-responsive">
                <table class="table modal-table table-hover middle less-border space">
                    <thead>
                        <tr>
                            <th class="min-w130">FILE HEADERS</th>
                            <th class="w220">{{ strtoupper($module) }}  FIELDS</th>
                        </tr>
                    </thead>

                    <tbody class="unique-field-val">
                        {!! $tr or null !!}
                    </tbody>
                </table>
            </div>
        </div>
    </div> <!-- end modal-body -->
{{ Form::close() }}
