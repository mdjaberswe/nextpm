        <div id="content-loader" class="pg-loader"></div>

        <audio id="notification-sound" class="none">
            <source src="{{ asset('img/sound/notify.mp3') }}" type="audio/mpeg">
            <source src="{{ asset('img/sound/notify.wav') }}" type="audio/wav">
        </audio>

        <script>
            /**
             * Global globalVar - uses all over pages script for multiple purposes
             * like to get base URL, admin base URL, chart data, notify plugin template, etc.
             *
             * @type {Object}
             */
            var globalVar              = {};
            globalVar.ajaxRequest      = [];
            globalVar.defaultDropdown  = [];
            globalVar.baseUrl          = '{{ url('/') }}';
            globalVar.baseAdminUrl     = '{{ url('/admin') }}';
            globalVar.dataTable        = [];
            globalVar.pieChart         = [];
            globalVar.lineChart        = [];
            globalVar.barChart         = [];
            globalVar.dropzone         = [];
            globalVar.perfectscroll    = [];
            globalVar.infoNotify       = defaultNotifyConfig('info', { delay: 3000 });
            globalVar.successNotify    = defaultNotifyConfig('success', { delay: 3000 });
            globalVar.warningNotify    = defaultNotifyConfig('warning', { delay: 3000 });
            globalVar.dangerNotify     = defaultNotifyConfig('danger', { delay: 3000 });
            globalVar.ladda            = null;
            globalVar.ajaxErrorHandler = function (jqXHR, textStatus, errorThrown) {
                // ajaxErrorHandler defined in js/app.js
                ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
            };

            /**
             * Default notify configuration.
             *
             * @param {string} type
             * @param {Object} options
             *
             * @return {Object}
             */
            function defaultNotifyConfig (type, options) {
                return {
                    showProgressbar : true,
                    placement       : { from: 'bottom', align: 'right' },
                    offset          : { x: 20, y: 25 },
                    delay           : options.delay ? options.delay : 3000,
                    timer           : options.timer ? options.timer : 260,
                    animate         : { enter: 'animated fadeInRight', exit: 'animated fadeOutUp' },
                    template        : notifyTemplate(type)
                };
            }

            /**
             * Get the notify template.
             *
             * @param {string} type
             *
             * @return {string}
             */
            function notifyTemplate (type) {
                var icon = 'fa fa-info-circle';

                // Get alter CSS class according to alert type.
                if (type === 'info') {
                    icon = 'fa fa-info-circle';
                } else if (type === 'success') {
                    icon = 'fa fa-check-circle';
                } else if (type === 'warning') {
                    icon = 'fa fa-exclamation-triangle';
                } else if (type === 'danger') {
                    icon = 'fa fa-exclamation-circle';
                }

                var template = "<div data-notify='container' class='alert alert-" + type + " slight' role='alert'>" +
                                    "<span class='" + icon + "'></span>" +
                                    "<button type='button' class='close' data-notify='dismiss'><span aria-hidden='true'>&times;</span></button>" +
                                    '{2}' +
                                    "<div class='progress' data-notify='progressbar'>" +
                                        "<div class='progress-bar' role='progressbar' aria-valuenow='0' aria-valuemin='0' aria-valuemax='100' style='width: 10%;'></div>" +
                                    '</div>' +
                                '</div>';

                return template;
            }
        </script>

        @include('partials.global-scripts')

        @stack('scripts')

        {{ HTML::script('js/fallback.js') }}

    </body>
</html>
