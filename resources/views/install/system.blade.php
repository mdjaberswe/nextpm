@extends('layouts.install')

@section('content')
    <div class="full center-panel">
        <div class="full panel-hd">
            <h2>{{ $page['title'] }}</h2>
        </div>

        @include('install.partials.progress-nav')

        <div class="full panel-cont">
            <h3><i class="fa fa-puzzle-piece"></i> Requirements</h3>

            <ul class="line-hr-list">
                @foreach ($requirement['components'] as $component)
                    <li>
                        <h5>
                            @if ($component['status'])
                                <i class="fa fa-check-circle true"></i>
                            @else
                                <i class="fa fa-times-circle false"></i>
                            @endif

                            {{ $component['name'] }}
                        </h5>

                        <p class="msg">
                            {{ $component['message'] }}

                            @if (array_key_exists('note', $component))
                                <span class="color-shadow sm">({!! $component['note'] !!})</span>
                            @endif
                        </p>
                    </li>
                @endforeach
            </ul>

            <h3><i class="fa fa-folder"></i> Permissions</h3>

            <ul class="line-hr-list">
                @foreach ($requirement['permissions'] as $permission)
                    <li>
                        <h5>
                            @if ($permission['status'])
                                <i class="fa fa-check-circle true"></i>
                            @else
                                <i class="fa fa-times-circle false"></i>
                            @endif

                            {{ $permission['name'] }}
                        </h5>

                        <p class="msg">{{ $permission['message'] }}</p>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="full panel-ft">
            @if ($requirement['status'])
                <a href="{{ route('install.config') }}" class="btn btn-info">Next <i class="mdi mdi-arrow-right-bold right"></i></a>
            @else
                <a href="{{ route('install.system') }}" class="btn"><i class="fa fa-repeat"></i> Try again</a>
            @endif
        </div>
    </div>
@endsection
