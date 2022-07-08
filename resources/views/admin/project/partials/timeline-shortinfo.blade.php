<div class="col-xs-12 col-md-4">
	<div class="full">
		<div class="timeline-sidebox float-sm-auto-md-right">
			<div class="strong uppercase">Progress</div>

			<div id="completion-percentage" class="circlebox">
				{!! $project->classified_completion !!}
			</div>

			<div class="full">
				<h4>Last modified:</h4>
				<div class="full" data-realtime="last_modified">
					<p data-toggle="tooltip" data-placement="bottom" title="{!! $project->readableDateAmPm('modified_at') !!}">{!! time_short_form($project->modified_at->diffForHumans()) !!}</p>
				</div>
			</div>

			<div class="full">
				<h4>Duration:</h4>
				<p data-realtime="duration">{!! $project->duration_html !!}</p>
			</div>

            <div class="full">
                <h4>Project Age:</h4>
                <div class="full" data-realtime="age">{!! $project->age_html !!}</div>
            </div>
		</div> <!-- end timeline-sidebox -->
	</div>

    <div class="full follower-container-box">
        {!! $project->display_followers !!}
    </div>
</div>
