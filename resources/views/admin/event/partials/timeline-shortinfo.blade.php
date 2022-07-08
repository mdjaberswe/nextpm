<div class="col-xs-12 col-md-4">
	<div class="full">
		<div class="timeline-sidebox float-sm-auto-md-right">
			<div class="strong uppercase">No Of Attendees</div>

			<div id="completion-percentage" class="circlebox" data-realtime="no_of_attendees">
				{!! $event->classified_total_attendees !!}
			</div>

			<div class="full">
				<h4>Last modified:</h4>
				<div class="full" data-realtime="last_modified">
					<p data-toggle="tooltip" data-placement="bottom" title="{!! $event->readableDateAmPm('modified_at') !!}">{!! time_short_form($event->modified_at->diffForHumans()) !!}</p>
				</div>
			</div>

			<div class="full">
				<h4>Duration:</h4>
				<p data-realtime="duration">{!! $event->duration_html !!}</p>
			</div>

			<div class="full">
				<h4>Participants:</h4>
				<p data-realtime="attendees">{!! $event->attendees_html or '<span class="color-shadow l-space1">--</span>' !!}</p>
			</div>
		</div> <!-- end timeline-sidebox -->
	</div>

    <div class="full follower-container-box">
        {!! $event->display_followers !!}
    </div>
</div>
