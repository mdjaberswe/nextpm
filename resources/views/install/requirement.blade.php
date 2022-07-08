<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="{{ asset('img/default-favicon.png') }}">
        <title>{{ isset($page['title']) ? $page['title'] : config('app.name') }}</title>

        @include('partials.global-css')

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>

    <body class="{{ array_key_exists('multi_section', $page) ? 'multiple-section' : null }}">

    @if (auth()->check())
        <header>
            <div id="logo" class="header-logo left-justify">
                @if (not_null_empty(config('setting.logo')) && file_exists(public_path(config('setting.logo'))))
                    <a href="{{ route('home') }}" class="logo-link logo-img"><img src="{{ asset(config('setting.logo')) }}" alt="logo" realtime="logo"/></a>
                @else
                    <a href="{{ route('home') }}" class="logo-link">{{ config('setting.app_name') }}</a>
                @endif
            </div> <!-- end logo -->

            <div id="top-nav" class="header-nav left-justify">
                <div class="btn-group">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown">
                        <img class="circle-avt" src="{{ auth_staff()->avatar }}" alt="{{ auth_staff()->name }}" title="{{ auth_staff()->name }}">
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('auth.signout') }}"><i class="fa fa-power-off"></i> Sign Out</a></li>
                    </ul>
                </div>

                <a class="nav-link expand" id="fullscreen"><i class="mdi mdi-crop-free"></i></a>
            </div> <!-- end top-nav -->
        </header> <!-- end header -->
    @endif

        <main role="main" class="center-content {{ auth()->check() ? 'top-space' : null }} {{ $page['content_size'] or null }}">
            <div class="full center-panel">
                <div class="full panel-hd">
                    <h2>{{ $page['title'] }}</h2>
                </div>

                <div class="full panel-cont">
                    @if ($page['system_info']['errors']->where('type', 'component')->count())
                        <h3><i class="fa fa-puzzle-piece"></i> Requirements</h3>

                        <ul class="line-hr-list">
                            @foreach ($page['system_info']['errors']->where('type', 'component')->all() as $component)
                                <li>
                                    <h5>
                                        <i class="fa fa-times-circle false"></i>
                                        {{ $component['name'] }}
                                    </h5>

                                    <p class="msg">{{ $component['message'] }}</p>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($page['system_info']['errors']->where('type', 'directory')->count())
                        <h3><i class="fa fa-folder"></i> Permissions</h3>

                        <ul class="line-hr-list">
                            @foreach ($page['system_info']['errors']->where('type', 'directory')->all() as $permission)
                                <li>
                                    <h5>
                                        <i class="fa fa-times-circle false"></i>
                                        {{ $permission['short_name'] }}
                                    </h5>

                                    <p class="msg">{{ $permission['message'] }}</p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="full panel-ft">
                    <a href="{{ route('system.requirement') }}" class="btn"><i class="fa fa-repeat"></i> Try again</a>
                </div>
            </div>
        </main>

        <script>
            var globalVar             = {};
            globalVar.ajaxRequest     = [];
            globalVar.defaultDropdown = [];
            globalVar.baseUrl         = '{{ url('/') }}';
        </script>

        @include('partials.global-scripts')

        @stack('scripts')

    </body>
</html>
