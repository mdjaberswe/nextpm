@include('partials.header')

@include('partials.nav')

<main role="main" class="{{ $class['main'] }}">

	@include('partials.message')

    @yield('content')

    @yield('modals')

    @include('partials.modals.common-initialize')

    @yield('extend')

</main>

@include('partials.footer')

