<div class="full">
    {!! HtmlElement::renderTabNav($page['tabs']) !!}
</div> <!-- end full -->

<div id="item-tab-details" class="full {{ $page['tabs']['min_height_class'] or 'min-h200' }}"
     item="{{ array_key_exists('tab_item', $page['tabs']) ? $page['tabs']['tab_item'] : $page['item'] }}"
     itemid="{{ $page['tabs']['item_id'] }}" taburl="{{ $page['tabs']['url'] }}">
    <div id="item-tab-content">
        @include($page['view'] . '.partials.tabs.tab-' . $page['tabs']['default'])
    </div> <!-- item-tab-content -->
</div> <!-- end item-tab-details -->

@push('scripts')
    <script>
        $(document).ready(function () {
            // tabDatatableInit defined in js/app.js
            tabDatatableInit(null, 'item-tab-details');
        });
    </script>
@endpush
