@extends('layouts.default')

@section('content')
	<div class="row m0-imp">
		<div class="full content-header">
            <div class="col-xs-12 col-md-5">
                <h4 class="breadcrumb-title">{!! $page['item_title'] !!}</h4>
            </div>

			<div class="col-xs-12 col-md-7 sm-left-md-right">
				<div class="btn-group light">
					<a href="{{ route('admin.issue.index') }}" class="btn thin btn-regular" data-toggle="tooltip" data-placement="bottom" title="Tabular"><i class="fa fa-list"></i></a>
					<a href="{{ route('admin.issue.kanban') }}" class="btn thin btn-regular active" data-toggle="tooltip" data-placement="bottom" title="Kanban"><i class="fa fa-align-left rot-90"></i></a>
					<a href="{{ route('admin.issue.calendar') }}" class="btn thin btn-regular" data-toggle="tooltip" data-placement="bottom" title="Calendar"><i class="fa fa-calendar"></i></a>
				</div>

				<button type="button" class="btn btn-regular only-icon common-filter-btn" data-toggle="tooltip" data-placement="bottom" title="Filter" data-item="issue" data-url="{{ route('admin.filter.form.content', 'issue') }}" data-posturl="{{ route('admin.filter.form.post', 'issue') }}">
					<i class="fa fa-filter"></i>
					@if ($page['current_filter']->param_count)
						<span class="num-notify">{{ $page['current_filter']->param_count }}</span>
					@else
						<span class="num-notify none"></span>
					@endif
				</button>

				@if (permit('issue.create') && permit('import.issue'))
					<button type="button" class="btn btn-regular only-icon import-btn"  data-item="issue" data-url="{{ route('admin.import.csv') }}" data-toggle="tooltip" data-placement="bottom" title="Import Issues"><i class="mdi mdi-file-excel pe-va"></i></button>
				@endif

				@permission('issue.create')
        			<button type="button" id="add-new-btn" class="btn btn-info" data-default="{{ 'issue_owner:' . auth_staff()->id }}"><i class="fa fa-plus-circle"></i> Add Issue</button>
	        	@endpermission
			</div>
		</div> <!-- end content-header -->

		<div class="full funnel-wrap">
			<div class="full funnel-container scroll-box-x only-thumb" data-source="issue" data-stage="issue_status_id" data-order="desc">
				@foreach ($issues_kanban as $key => $issue_kanban)
					<div id="{{ $key }}" class="funnel-stage" data-display="{{ str_limit($issue_kanban['status']['name'], 17, '.') }}" data-stage="{{ $issue_kanban['status']['id'] }}" data-count="{{ count($issue_kanban['data']) }}" data-load="{{ $issue_kanban['status']['load_status'] }}" data-url="{{ $issue_kanban['status']['load_url'] }}">
						<div class="funnel-stage-header">
							<h3 class="title">
								{{ str_limit($issue_kanban['status']['name'], 25) }} <span class="shadow count">{{ count($issue_kanban['data']) }}</span>
							</h3>
							<div class="funnel-arrow"><span class="bullet"></span></div>
						</div> <!-- end funnel-stage-header -->

						<div class="funnel-card-container scroll-box only-thumb" data-card-type="issue">
							<ul class="kanban-list">
								<div id="{{ $key . '-cards' }}" class="full li-container">
									@foreach ($issue_kanban['quick_data'] as $issue)
										{!! $issue->kanban_card_html !!}
									@endforeach
								</div>

								<span class="content-loader bottom"></span>
							</ul>
						</div> <!-- end funnel-card-container -->
					</div> <!-- end funnel-stage -->
				@endforeach

				<span class="content-loader all"></span>
			</div> <!-- end funnel-container -->
			<a class="funnel-container-arrow left"><i class="fa fa-chevron-left"></i></a>
			<a class="funnel-container-arrow right"><i class="fa fa-chevron-right"></i></a>
		</div> <!-- end funnel-wrap -->
	</div>
@endsection

@section('modalcreate')
	{{ Form::open(['route' => 'admin.issue.store', 'method' => 'post', 'class' => 'modal-form']) }}
	    @include('admin.issue.partials.form', ['form' => 'create'])
	{{ Form::close() }}
@endsection
