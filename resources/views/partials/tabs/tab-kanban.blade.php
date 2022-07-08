<div class="full">
    <h4 class="tab-title near-border">{{ $prefix or null }} {{ ucfirst($item) . 's' }}</h4>

    <div class="right-top">
        <div class="btn-group light">
            <a class="btn thin btn-regular tab-link" tabkey="{{ $parent_key or $item . 's' }}" data-toggle="tooltip" data-placement="bottom" title="Tabular"><i class="fa fa-list"></i></a>
            <a class="btn thin btn-regular active" data-toggle="tooltip" data-placement="bottom" title="Kanban"><i class="fa fa-align-left rot-90"></i></a>
        </div>

        @if ((! isset($can_create) && permit($item . '.create')) || (isset($can_create) && $can_create == true))
            <button type="button" class="btn btn-regular add-multiple"
                data-item="{{ $item }}"
                data-action="{{ route('admin.' . $item . '.store') }}"
                data-content="{{ $item . '.partials.form' }}"
                @if (isset($data_default))
                    data-default="{{ $data_default }}"
                @else
                    data-default="{{ 'related_type:' . $module_name . '|related_id:' . $module_id }}"
                @endif
                save-new="false">
                <i class="fa fa-plus-circle"></i> Add {{ ucfirst($item) }}
            </button>
        @endif
    </div>

    <div class="full funnel-wrap">
        <div class="full funnel-container scroll-box-x only-thumb" data-source="{{ $item }}" data-stage="{{ $item . '_status_id' }}" data-order="desc" data-parent="{{ $module_name }}" data-parent-id="{{ $module_id }}">
            @foreach ($module->getActivityKanbanData($item) as $key => $kanban)
                <div id="{{ $key }}" class="funnel-stage" data-display="{{ str_limit($kanban['status']['name'], 17, '.') }}" data-stage="{{ $kanban['status']['id'] }}" data-count="{{ count($kanban['data']) }}" data-load="{{ $kanban['status']['load_status'] }}" data-url="{{ $kanban['status']['load_url'] . '/' . $module_name . '/' . $module_id }}">
                    <div class="funnel-stage-header">
                        <h3 class="title">
                            {{ str_limit($kanban['status']['name'], 25) }} <span class="shadow count">{{ count($kanban['data']) }}</span>
                            @if (isset($percentage) && $percentage == true)
                                <p class="stat">{{ $kanban['status']['completion_percentage'] }}<i>%</i></p>
                            @endif
                        </h3>
                        <div class="funnel-arrow"><span class="bullet"></span></div>
                    </div> <!-- end funnel-stage-header -->

                    <div class="funnel-card-container scroll-box only-thumb" data-card-type="{{ $item }}">
                        <ul class="kanban-list">
                            <div id="{{ $key . '-cards' }}" class="full li-container">
                                @foreach ($kanban['quick_data'] as $kanban_item)
                                    {!! $kanban_item->kanban_card_html !!}
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
    </div>
</div>
