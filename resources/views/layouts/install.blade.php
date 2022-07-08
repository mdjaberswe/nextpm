@include('partials.install.header')

    <main role="main" class="center-content {{ $page['content_size'] or null }}">

        @yield('content')

        @yield('modals')

    </main>

@include('partials.install.footer')
