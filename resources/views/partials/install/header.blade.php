<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{!! csrf_token() !!}">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="{!! Auth::check() ? asset(config('setting.favicon')) : asset('img/default-favicon.png') !!}">
        <title>{!! isset($page['title']) ? $page['title'] : config('app.name') !!}</title>

        @include('partials.global-css')

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>

    <body class="{!! array_key_exists('multi_section', $page) ? 'multiple-section' : null !!}">

    @if (Auth::check())
        <header>
            <div id="logo" class="header-logo left-justify">
                @if (not_null_empty(config('setting.logo')) && file_exists(public_path(config('setting.logo'))))
                    <a href="{!! route('home') !!}" class="logo-link logo-img"><img src="{!! asset(config('setting.logo')) !!}" alt="logo" realtime="logo"/></a>
                @else
                    <a href="{!! route('home') !!}" class="logo-link">{!! config('setting.app_name') !!}</a>
                @endif
            </div> <!-- end logo -->

            <div id="top-nav" class="header-nav left-justify">
                <div class="btn-group">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown">
                        <img class="circle-avt" src="{!! auth_staff()->avatar !!}" alt="{!! auth_staff()->name !!}" title="{!! auth_staff()->name !!}">
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="{!! route('auth.signout') !!}"><i class="fa fa-power-off"></i> Sign Out</a></li>
                    </ul>
                </div>

                <a class="nav-link expand" id="fullscreen"><i class="mdi mdi-crop-free"></i></a>
            </div> <!-- end top-nav -->
        </header> <!-- end header -->
    @endif
