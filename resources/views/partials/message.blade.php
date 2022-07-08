@if (session('message'))
    <div class="alert alert-info slim">
        <span class="fa fa-info-circle"></span>
        <button type="button" class="close"><span aria-hidden="true">&times;</span></button>
        {!! session('message') !!}
    </div>
@endif

@if (session('success_message'))
    <div class="alert alert-success slim">
        <span class="fa fa-check-circle"></span>
        <button type="button" class="close"><span aria-hidden="true">&times;</span></button>
        {!! session('success_message') !!}
    </div>
@endif

@if (session('warning_message'))
    <div class="alert alert-warning slim">
        <span class="fa fa-exclamation-triangle"></span>
        <button type="button" class="close"><span aria-hidden="true">&times;</span></button>
        {!! session('warning_message') !!}
    </div>
@endif

@if (session('danger_message'))
    <div class="alert alert-danger slim">
        <span class="fa fa-exclamation-circle"></span>
        <button type="button" class="close"><span aria-hidden="true">&times;</span></button>
        {!! session('danger_message') !!}
    </div>
@endif

@if (session('delete_message'))
    <div class="alert alert-danger slim">
        <span class="fa fa-times-circle"></span>
        <button type="button" class="close"><span aria-hidden="true">&times;</span></button>
        {!! session('delete_message') !!}
    </div>
@endif
