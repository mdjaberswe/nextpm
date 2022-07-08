<div class="col-xs-12 col-md-4">
	<div class="full">
		<div class="timeline-sidebox float-sm-auto-md-right">
			<div class="strong uppercase">Progress</div>

			<div id="completion-percentage" class="circlebox">
				{!! $milestone->classified_completion !!}
			</div>

			<div class="full">
				<h4>Last modified:</h4>
				<div class="full" data-realtime="last_modified">
					<p data-toggle="tooltip" data-placement="bottom" title="{{ $milestone->readableDateAmPm('modified_at') }}">{!! time_short_form($milestone->modified_at->diffForHumans()) !!}</p>
				</div>
			</div>

            <div class="full">
                <h4>Duration:</h4>
                <p data-realtime="duration">{!! $milestone->duration_html !!}</p>
            </div>

            <div class="full">
                <h4>Milestone Age:</h4>
                <div class="full" data-realtime="age">{!! $milestone->age_html !!}</div>
            </div>
		</div> <!-- end timeline-sidebox -->
	</div>

    <div class="full follower-container-box">
        {!! $milestone->display_followers !!}
    </div>
</div>
