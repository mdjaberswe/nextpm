@if (! isset($page['modal']) || (isset($page['modal']) && $page['modal'] == true))
	{{-- Autoload Modals --}}
	@if (! isset($page['modal_create']) || (isset($page['modal_create']) && $page['modal_create'] == true))
		@include('partials.modals.create')
	@endif

	@if (! isset($page['modal_edit']) || (isset($page['modal_edit']) && $page['modal_edit'] == true))
		@include('partials.modals.edit')
	@endif

	@if (! isset($page['modal_bulk_delete']) || (isset($page['modal_bulk_delete']) && $page['modal_bulk_delete'] == true))
		@include('partials.modals.bulk-delete')
	@endif

	@if (! isset($page['modal_bulk_update']) || (isset($page['modal_bulk_update']) && $page['modal_bulk_update'] == true))
		@include('partials.modals.bulk-update')
	@endif

	{{-- Call Modals --}}
	@if (isset($page['bulk']) && strpos($page['bulk'], 'email') !== false)
		@include('partials.modals.bulk-email')
	@endif

	@if (isset($page['bulk']) && strpos($page['bulk'], 'status') !== false)
		@include('partials.modals.bulk-update-status')
	@endif

	@if ((isset($page['permission']) && permit('import.' . $page['permission'])) || (isset($page['import']) && $page['import'] == true))
		@include('partials.modals.import.init')
	@endif

	@if (isset($page['filter']) && $page['filter'] == true)
		@include('partials.modals.common-filter')
		@include('partials.modals.common-view')
	@endif
@endif
