<div class="col-xs-12 col-md-4">
	<div class="full">
		<div class="timeline-sidebox float-sm-auto-md-right">
			<div class="strong uppercase">Days remaining</div>

			<div id="days-remaining" class="circlebox">
				{!! $issue->days_remaining_html !!}
			</div>

			<div class="full">
				<h4>Last modified:</h4>
				<div class="full" data-realtime="last_modified">
					<p data-toggle="tooltip" data-placement="bottom" title="{!! $issue->readableDateAmPm('modified_at') !!}">{!! time_short_form($issue->modified_at->diffForHumans()) !!}</p>
				</div>
			</div>

			<div class="full">
                <h4>Duration:</h4>
                <p data-realtime="duration">{!! $issue->duration_html !!}</p>
            </div>

            <div class="full">
                <h4>Overdue:</h4>
                <div class="full" data-realtime="overdue">{!! $issue->overdue_days_html !!}</div>
            </div>
		</div> <!-- end timeline-sidebox -->
	</div>

    <div class="full follower-container-box">
        {!! $issue->display_followers !!}
    </div>
</div>
