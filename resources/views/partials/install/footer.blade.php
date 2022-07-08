        <script>
            var globalVar             = {};
            globalVar.ajaxRequest     = [];
            globalVar.defaultDropdown = [];
            globalVar.baseUrl         = '{!! url('/') !!}';
        </script>

        @include('partials.global-scripts')

        @stack('scripts')

    </body>
</html>
