<h4 class="tab-title">History</h4>

<div class="full">
    <div class="col-xs-12 col-md-8">
        <div class="full timeline" data-url="{{ route('admin.history.data', $module_name) }}" data-relatedtype="{{ $module_name }}" data-relatedid="{{ $module_id }}">
            <div class="timeline-info start">
                <div class="timeline-icon">Today</div>
            </div>

            {!! $module->all_histories_html !!}

            <div class="timeline-info end-down {{ ($module->all_histories->count() < 30) ? 'disable' : null }}">
                <i class="load-icon fa fa-circle-o-notch fa-spin"></i>
                <div class="timeline-icon"><a class="load-timeline"><i class="fa fa-angle-down"></i></a></div>
            </div>
        </div> <!-- end timeline -->
    </div>

    @if (view()->exists("admin.$module_name.partials.timeline-shortinfo"))
        @include("admin.$module_name.partials.timeline-shortinfo")
    @elseif(isset($view) && view()->exists("admin.$view.partials.timeline-shortinfo"))
        @include("admin.$view.partials.timeline-shortinfo")
    @endif
</div>
