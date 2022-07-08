@extends('layouts.install')

@section('content')
	<div class="full center-panel">
		<div class="full panel-hd">
			<h2>{{ $page['title'] }}</h2>
		</div>

		@include('install.partials.progress-nav')

		<div class="full panel-cont">
			<h3 class="success"><i class="mdi mdi-check-all"></i> Congratulations, you've successfully installed {{ config('app.item_name') }}</h3>
			<p>Remember that all your configurations were saved in [APP_ROOT]/.env file. You can change it when needed.</p>
			<p>Now, you can go to your <a href="{{ route('auth.signin') }}">Admin Panel</a>.</p>
			<p>Thank you for choosing <a href="https://github.com/mdjaberswe/nextpm">{{ config('app.item_name') }}</a>.</p>
		</div>
	</div>
@endsection
