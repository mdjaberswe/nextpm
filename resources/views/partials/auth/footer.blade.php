        @if (! isset($page['animation']) || (isset($page['animation']) && $page['animation'] == true))
            <ul class="pools">
                <li><i class="fa fa-users"></i></li>
                <li><i class="mdi mdi-bell"></i></li>
                <li><i class="fa fa-thumb-tack"></i></li>
                <li><i class="mdi mdi-message-text"></i></li>
                <li><i class="fa fa-area-chart"></i></li>
                <li><i class="fa fa-filter"></i></li>
                <li><i class="fa fa-bar-chart"></i></li>
                <li><i class="fa fa-line-chart"></i></li>
                <li><i class="fa fa-calendar"></i></li>
                <li><i class="fa fa-map-signs"></i></li>
                <li><i class="fa fa-tasks"></i></li>
                <li><i class="fa fa-bug"></i></li>
                <li><i class="fa fa-bullhorn"></i></li>
            </ul>
        @endif

        <script>
            var globalVar = {};
            globalVar.baseUrl = '{!! url('/') !!}';
            globalVar.ajaxRequest = [];
        </script>

        @include('partials.global-scripts')

        @stack('scripts')

    </body>
</html>
