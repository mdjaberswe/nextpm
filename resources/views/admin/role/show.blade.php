@extends('layouts.master')

@section('content')

    <div class="row page-content">
        <div class="full content-header mb20 bottom-border">
            <div class="col-xs-12 col-sm-7 col-md-7 col-lg-8">
                <h4 class="breadcrumb-title">{!! $page['item_title'] !!}</h4>
            </div>

            <div class="col-xs-12 col-sm-5 col-md-5 col-lg-4 xs-left-sm-right">
                @if (! $role->fixed && (permit('role.edit') || permit('role.delete')))
                    <div class="dropdown clean inline-block">
                        <a class="btn thiner btn-regular dropdown-toggle" animation="fadeIn|fadeOut" data-toggle="dropdown" aria-expanded="false">
                            <i class="mdi mdi-dots-vertical fa-md pe-va"></i>
                        </a>

                        <ul class="dropdown-menu up-caret">
                            @if (permit('role.edit'))
                                <li><a href="{{ route('admin.role.edit', $role->id) }}"><i class="fa fa-edit"></i> Edit Role</a></li>
                            @endif

                            @if (permit('role.delete'))
                                <li>
                                    {{ Form::open(['route' => ['admin.role.destroy', $role->id], 'method' => 'delete']) }}
                                        {{ Form::hidden('id', $role->id) }}
                                        {{ Form::hidden('redirect', true) }}
                                        <button type="submit" class="delete" data-item="role"><i class="mdi mdi-delete"></i> Delete</button>
                                    {{ Form::close() }}
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif

                <div class="inline-block prev-next">
                    <a @if ($role->prev_record) href="{{ route('admin.role.show', $role->prev_record->id) }}" @endif class="inline-block prev @if (is_null($role->prev_record)) disabled @endif" data-toggle="tooltip" data-placement="bottom" title="{{ fill_up_space('Previous Record') }}"><i class="pe pe-7s-angle-left pe-va"></i></a>
                    <a @if ($role->next_record) href="{{ route('admin.role.show', $role->next_record->id) }}" @endif class="inline-block next @if (is_null($role->next_record)) disabled @endif" data-toggle="tooltip" data-placement="bottom" title="{{ fill_up_space('Next Record') }}"><i class="pe pe-7s-angle-right pe-va"></i></a>
                </div>
            </div>
        </div>

        <div class="full">
            {{ Form::model($role, ['class' => 'page-form render']) }}

                <div class="form-group">
                    <label for="name" class="col-xs-12 col-sm-3 col-md-2 col-lg-2">Role Name <span class="color-danger">*</span></label>

                    <div class="col-xs-12 col-sm-9 col-md-10 col-lg-10">
                        {{ Form::text('name', isset($role) ? $role->display_name : null, ['class' => 'form-control', 'readonly' => true]) }}
                    </div>
                </div> <!-- end form-group -->

                <div class="form-group">
                    {{ Form::label('description', 'Role Description', ['class' => 'col-xs-12 col-sm-3 col-md-2 col-lg-2']) }}

                    <div class="col-xs-12 col-sm-9 col-md-10 col-lg-10">
                        {{ Form::textarea('description', null, ['class' => 'form-control', 'readonly' => true]) }}
                    </div>
                </div> <!-- end form-group -->

                <div class="form-group permission-container">
                    {{ Form::label('permissions', 'Permissions', ['class' => 'mb15 col-xs-12 col-sm-3 col-md-2 col-lg-2']) }}

                    <div class="col-xs-12 col-sm-9 col-md-10 col-lg-10">
                        @foreach ($permissions_groups as $permissions_group)
                            <div class="full permission-group">
                                <div class="full permission-group-title">
                                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 toggle-header">
                                        <h2 class="left-justify">{{ $permissions_group['display_name'] }} Permissions</h2>
                                    </div>
                                </div> <!-- end permission-group-title -->

                                @foreach ($permissions_group['modules'] as $module)
                                    <div class="full permission-row">
                                        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 permission-row-title">
                                            <span class="left-justify para-cap {{ in_array(strtolower($module->display_name), ['import', 'mass update', 'mass delete', 'change owner']) ? 'tooltip-lg-min' : null }}">
                                                {{ ucfirst($module->display_name) }}

                                                @if (strtolower($module->display_name) == 'mass update')
                                                    <i class="hints fa fa-info-circle" data-toggle="tooltip" data-html="true" title="{{ fill_up_space('Only modules with edit permission<br>are permitted for mass update') }}"></i>
                                                @elseif(strtolower($module->display_name) == 'mass delete')
                                                    <i class="hints fa fa-info-circle" data-toggle="tooltip" data-html="true" title="{{ fill_up_space('Only modules with delete permission<br>are permitted for mass delete') }}"></i>
                                                @elseif(strtolower($module->display_name) == 'change owner')
                                                    <i class="hints fa fa-info-circle" data-toggle="tooltip" data-html="true" title="{{ fill_up_space('Only modules with edit permission<br>are permitted for change owner') }}"></i>
                                                @elseif(strtolower($module->display_name) == 'import')
                                                    <i class="hints fa fa-info-circle" data-toggle="tooltip" data-html="true" title="{{ fill_up_space('Only modules with create permission<br>are permitted for import') }}"></i>
                                                @elseif(strtolower($module->display_name) == 'user')
                                                    <i class="hints fa fa-info-circle" data-toggle="tooltip" data-html="true" title="{{ fill_up_space('Only Administrator can <br> create / delete user') }}"></i>
                                                @elseif(strtolower($module->display_name) == 'role')
                                                    <i class="hints fa fa-info-circle" data-toggle="tooltip" data-html="true" title="{{ fill_up_space('Only Administrator can <br> create / edit / delete role') }}"></i>
                                                @endif
                                            </span>

                                            <label class="right-justify switch">
                                                @if ($permissions_group['module_permissions']['has_permission'][$module->name] == true)
                                                    <input type="checkbox" name="permissions[]" value="{{ $module->id }}" checked disabled>
                                                @else
                                                    <input type="checkbox" name="permissions[]" value="{{ $module->id }}" disabled>
                                                @endif
                                                <span class="slider round"></span>
                                            </label>
                                        </div> <!-- end permission-row-title -->

                                        @if (count($permissions_group['module_permissions'][$module->name]) > 0)
                                            {!! HtmlElement::renderModulePermissions($permissions_group['module_permissions'][$module->name], true) !!}
                                        @endif
                                    </div>
                                @endforeach
                            </div> <!-- end permission-group -->
                        @endforeach
                    </div>
                </div> <!-- end form-group -->
            {{ Form::close() }}
        </div>
    </div> <!-- end row -->

@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Add CSS class "last" on the last item of permission summary
            $('.permission-summary').each(function (index, ui) {
                $(ui).find('.para-soft:first span').removeClass('last');
                $(ui).find('.para-soft:first span:visible:last').addClass('last');
            });

            // Permission summary details show in the dropdown box
            $('main .permission-summary').on('click', function (e) {
                var thisDivPosition    = parseInt($(this).offset().top, 10) - parseInt($(this).closest('.page-content').offset().top, 10);
                var containerDivHeight = $(this).closest('.page-content').height();
                var lowerGap           = containerDivHeight - thisDivPosition;
                var comingDivHeight    = $(this).find('.permission-details').height() + 40;

                if (comingDivHeight > lowerGap) {
                    $(this).find('.permission-details').css('top', 'auto');
                    $(this).find('.permission-details').css('bottom', '100%');
                }

                e.stopPropagation();
                $('.permission-details').not($(this).children('.permission-details')).slideUp(100);
                $(this).find('.permission-details').slideToggle(100);
            });

            $('.permission-summary .permission-details').on('click', function (e) {
                e.stopPropagation();
            });

            // Slide up all opened permission details box when clicking outside
            $('main').on('click', function () {
                $('.permission-details').slideUp(100);
            });
        });
    </script>
@endpush
