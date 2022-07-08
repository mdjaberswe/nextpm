@extends('layouts.install')

@section('content')
    <div class="full center-panel">
        <div class="full panel-hd">
            <h2>{{ $page['title'] }}</h2>
        </div>

        @include('install.partials.progress-nav')

        {{ Form::open(['route' => 'install.post.import', 'class' => 'page-form']) }}
            {{ Form::hidden('import_status', $page['is_importing']) }}

            <div class="full panel-cont">
                <h3><i class="mdi mdi-database"></i> Database configuration</h3>
                <p class="note">The settings was successfully configured! Click <strong>Setup Database</strong> button to start importing data to database '{{ $page['database_name'] }}'.</p>

                <div class="full import-progress pr40 {{ $page['is_importing'] ? null : 'none' }}">
                    <div class="progress">
                        <div class="progress-bar color-success" role="progressbar" aria-valuenow="{{ $page['import_progress'] }}" aria-valuemin="0" aria-valuemax="100" style="width:{{ $page['import_progress'] . '%' }}">
                            <span class="sr-only">{{ $page['import_progress'] . '%' }}</span>
                        </div>
                        <span class="shadow runner">{{ $page['import_progress'] . '%' }}</span>
                    </div>

                    @if (! $page['is_ready'])
                        <div class="mt--15">Importing <span class="dots-processing">...</span></div>
                    @endif
                </div>
            </div>

            @if (! $page['is_importing'])
                <div class="full panel-ft">
                    <button class="import-db btn btn-info">Setup Database <i class="mdi mdi-arrow-right-bold right"></i></button>
                </div>
            @endif
        {{ Form::close() }}
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Ajax request to start importing initial data into DB
            $('.import-db').on('click', function (e) {
                e.preventDefault();
                $(this).attr('disabled', true);
                var importBtn = $(this);

                $.ajax({
                    type     : 'POST',
                    url      : globalVar.baseUrl + '/install/post-import',
                    data     : { import_status: $("input[name='import_status']").val() },
                    dataType : 'JSON',
                    success  : function (data) {
                        if (data.status === true) {
                            $('.import-progress').removeClass('none');
                        } else {
                            importBtn.attr('disabled', false);

                            if (data.error != null) {
                                $.notify({ message: data.error }, globalVar.dangerNotify);
                            }
                        }
                    }
                });
            });

            // Ajax request to get import status and update the progress bar
            var getImportStatus = function () {
                $.ajax({
                    type     : 'GET',
                    url      : globalVar.baseUrl + '/install/importing',
                    data     : { import_status: $("input[name='import_status']").val() },
                    dataType : 'JSON',
                    success  : function (data) {
                        if (data.status === true) {
                            updateProgressBar(100);
                            var redirect = globalVar.baseUrl + '/install/complete';
                            setTimeout(function () {
                                window.location.replace(redirect);
                            }, 1000);
                        } else {
                            updateProgressBar(data.importProgress);
                        }
                    }
                });
            };

            setInterval(getImportStatus, 1750);
        });

        /**
         * Realtime change on the import progress bar.
         *
         * @param {numeric} progressVal
         *
         * @return {void}
         */
        function updateProgressBar (progressVal) {
            $('.progress-bar').css('width', progressVal + '%');
            $('.progress-bar .sr-only').html(progressVal + '%');
            $('.progress .shadow').html(progressVal + '%');
        }
    </script>
@endpush
