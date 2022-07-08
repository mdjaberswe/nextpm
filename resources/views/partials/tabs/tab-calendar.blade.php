<div class="full">
    <h4 class="tab-title">{{ $prefix or null }} Calendar</h4>

    <div class="right-top">
        @if (isset($multiple_view) && $multiple_view == true)
            <div class="btn-group light">
                <a class="btn thin btn-regular active" data-toggle="tooltip" data-placement="bottom" title="Calendar"><i class="fa fa-calendar"></i></a>
                <a class="btn thin btn-regular tab-link" tabkey="events" parent-tabkey="calendar" data-toggle="tooltip" data-placement="bottom" title="Tabular"><i class="fa fa-list"></i></a>
            </div>
        @endif

        @if ((! isset($can_create) && permit('event.create')) || (isset($can_create) && $can_create == true))
            <button type="button" class="btn btn-regular add-multiple" data-item="event"
                data-action="{{ route('admin.event.store') }}"
                data-content="event.partials.form"
                @if (isset($data_default))
                    data-default="{{ $data_default }}"
                @else
                    data-default="{{ 'related_type:' . $module_name . '|related_id:' . $module_id }}"
                @endif
                save-new="false">
                <i class="fa fa-plus-circle"></i> Add Event
            </button>
        @endif
    </div>

    <div class="full p20-t0">
        <div class="calendar" data-url="{{ route('admin.related.calendar.data', [$module_name, $module_id]) }}" data-viewonly="true"></div>
    </div>
</div>
