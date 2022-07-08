@extends('layouts.master')

@section('content')

	<div id="chat-room" class="full chat-room">
		<div id="chat-room-member" class="col-xs-2 col-sm-5 col-md-4 col-lg-3 p0-imp">
			<div class="full title-container">
                <img class="focus" src="{{ auth_staff()->avatar }}" alt="{{ auth_staff()->name }}">
                <h5>{!! auth_staff()->name_link !!}</h5>
                <p>{{ auth_staff()->title }}</p>
			</div>

			<div class="full messenger-search-box">
                <div class="left-icon light">
                    <i class="fa fa-search"></i>
                    <input id="search-room-member" class="form-control" placeholder="Search Messenger">
                </div>

                <div class="vr-menu">
                    <a class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i></a>

                    <ul class="dropdown-menu">
                        <li>
                            <a class="chat-sound-btn">
                                @if (auth_staff()->getSettingVal('chat_sound') == 'on')
                                    <i class="mdi mdi-volume-high lg"></i>
                                @else
                                    <i class="mdi mdi-volume-off lg"></i>
                                @endif

                                Chat Sound
                            </a>
                        </li>
                        <li><a class="add-multiple" data-item="announcement" data-action="{{ route('admin.announcement.store') }}" data-content="message.partials.announcement-form" modal-title="Announcement" data-default="active_chatroom_id:{{ $data['active_chatroom']->id or null }}" data-modalsize="tiny" save-new="false" save-txt="Send"><i class="fa fa-bullhorn"></i> Announcement</a></li>
                    </ul>
                </div>
            </div>

			<div class="full">
				<ul class="navlist scroll-box only-thumb">
					@if (count($data['chat_rooms']) && ! is_null($data['active_chatroom']))
						@foreach ($data['chat_rooms'] as $chat_room)
                            {!! $chat_room->getNavHtmlAttribute(null, $data['active_chatroom']->id) !!}
						@endforeach
					@endif
				</ul>
			</div>
		</div> <!-- end chat-room-member -->

		<div id="chat-message" class="col-xs-10 col-sm-7 col-md-8 col-lg-9 message-container">
			<div id="chat-message-title" class="full title-container always-show">
				@if (count($data['chat_rooms']) && ! is_null($data['active_chatroom']))
					<img src="{{ $data['active_chatroom']->avatar }}" alt="{{ $data['active_chatroom']->meaningful_name }}">
					<h5>{!! $data['active_chatroom']->getMeaningfulNameAttribute(true) !!}</h5>
					@if (! is_null($data['active_chatroom']->chat_partner))
						<p>{{ $data['active_chatroom']->chat_partner->title }}</p>
					@endif
				@endif
			</div> <!-- end chat-message-title -->

            <div class="full pr5-imp">
                <div class="content-loader history"></div>

    			<div id="chat-message-box" class="full pt15-imp message-box scroll-box only-thumb" data-roomid="{{ non_property_checker($data['active_chatroom'], 'id') }}" data-load="{{ non_property_checker($data['active_chatroom'], 'load_status') ? 'true' : 'false' }}">
                    @if (count($data['chat_rooms']) && ! is_null($data['active_chatroom']))
    					{!! $data['active_chatroom']->history_html !!}
    				@endif
    			</div> <!-- end chat-message-box -->
            </div>

			<div class="content-loader initial"></div>

            <div class="full msg-form-box">
                @if (count($data['chat_rooms']) && ! is_null($data['active_chatroom']))
                    <div class="msg-file-container scroll-box only-thumb">
                        <div class="modalfree dropzone-container">
                            <div class="modalfree-dropzone" data-linked="chat_sender" data-url="{{ route('admin.file.upload') }}" data-removeurl="{{ route('admin.file.remove') }}"></div>
                            <div class="dz-preview-container"></div>
                        </div>
                    </div>
                @endif

                <div class="msg-form-input">
                    <input type="hidden" id="room" name="room" value="{{ non_property_checker($data['active_chatroom'], 'id') }}">
                    <textarea name="message" id="send-msg" class="input-msg emoji" placeholder="{{ non_property_checker($data['active_chatroom'], 'inactive') || is_null($data['active_chatroom']) ? 'You can not reply to this conversation' : 'Type a message...' }}" @if (non_property_checker($data['active_chatroom'], 'inactive') || is_null($data['active_chatroom'])) disabled @endif></textarea>
                    <a class="msg-emoji" data-toggle="tooltip" data-placement="top" title="{{ fill_up_space('Choose an emoji') }}"><i class="mdi mdi-emoticon"></i></a>
                    <a class="msg-attach dropzone-attach" data-toggle="tooltip" data-placement="top" title="Add Files"><i class="mdi mdi-file-plus"></i></a>
                    <a class="send-msg-btn" data-html="true" data-toggle="tooltip" title="{{ fill_up_space('Press Enter to send') . '<br>' . fill_up_space('Use Shift + Enter for a new line') }}"><i class="mdi mdi-android-auto"></i></a>
                </div>
            </div>
        </div> <!-- end chat-message -->
	</div> <!-- end chat-room -->

@endsection

@push('scripts')
	{{ HTML::script('js/chat.js') }}
@endpush
