@extends('layouts.default')

@section('content')
	<div class="row m0-imp">
		<div class="full content-header">
            <div class="col-xs-12 col-md-5">
                <h4 class="breadcrumb-title">{!! $page['item_title'] !!}</h4>
            </div>

			<div class="col-xs-12 col-md-7 sm-left-md-right">
				<div class="btn-group light">
					<a href="{{ route('admin.project.index') }}" class="btn thin btn-regular" data-toggle="tooltip" data-placement="bottom" title="Tabular"><i class="fa fa-list"></i></a>
					<a href="{{ route('admin.project.kanban') }}" class="btn thin btn-regular active" data-toggle="tooltip" data-placement="bottom" title="Kanban"><i class="fa fa-align-left rot-90"></i></a>
				</div>

				<button type="button" class="btn btn-regular only-icon common-filter-btn" data-toggle="tooltip" data-placement="bottom" title="Filter" data-item="project" data-url="{{ route('admin.filter.form.content', 'project') }}" data-posturl="{{ route('admin.filter.form.post', 'project') }}">
					<i class="fa fa-filter"></i>
					@if ($page['current_filter']->param_count)
						<span class="num-notify">{{ $page['current_filter']->param_count }}</span>
					@else
						<span class="num-notify none"></span>
					@endif
				</button>

				@if (permit('project.create') && permit('import.project'))
					<button type="button" class="btn btn-regular only-icon import-btn"  data-item="project" data-url="{{ route('admin.import.csv') }}" data-toggle="tooltip" data-placement="bottom" title="Import Projects"><i class="mdi mdi-file-excel pe-va"></i></button>
				@endif

				@permission('project.create')
        			<button type="button" id="add-new-btn" class="btn btn-info" data-default="{{ 'project_owner:' . auth_staff()->id }}"><i class="fa fa-plus-circle"></i> Add Project</button>
	        	@endpermission
			</div>
		</div> <!-- end full -->

		<div class="full funnel-wrap">
			<div class="full funnel-container scroll-box-x only-thumb" data-source="project" data-stage="project_status_id" data-order="desc">
				@foreach ($projects_kanban as $key => $project_kanban)
					<div id="{{ $key }}" class="funnel-stage" data-display="{{ str_limit($project_kanban['status']['name'], 17, '.') }}" data-stage="{{ $project_kanban['status']['id'] }}" data-count="{{ count($project_kanban['data']) }}" data-load="{{ $project_kanban['status']['load_status'] }}" data-url="{{ $project_kanban['status']['load_url'] }}">
						<div class="funnel-stage-header">
							<h3 class="title">
								{{ str_limit($project_kanban['status']['name'], 25) }} <span class="shadow count">{{ count($project_kanban['data']) }}</span>
							</h3>
							<div class="funnel-arrow"><span class="bullet"></span></div>
						</div> <!-- end funnel-stage-header -->

						<div class="funnel-card-container scroll-box only-thumb" data-card-type="project">
							<ul class="kanban-list">
								<div id="{{ $key . '-cards' }}" class="full li-container">
									@foreach ($project_kanban['quick_data'] as $project)
										{!! $project->kanban_card_html !!}
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
	{{ Form::open(['route' => 'admin.project.store', 'method' => 'post', 'class' => 'modal-form']) }}
	    @include('admin.project.partials.form', ['form' => 'create'])
	{{ Form::close() }}
@endsection
