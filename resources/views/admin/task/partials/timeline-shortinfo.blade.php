<div class="col-xs-12 col-md-4">
	<div class="full">
		<div class="timeline-sidebox float-sm-auto-md-right">
			<div class="strong uppercase">Completion</div>

			<div id="completion-percentage" class="circlebox">
				{!! $task->classified_completion !!}
			</div>

			<div class="full">
				<h4>Last modified:</h4>
				<div class="full" data-realtime="last_modified">
					<p data-toggle="tooltip" data-placement="bottom" title="{!! $task->readableDateAmPm('modified_at') !!}">{!! time_short_form($task->modified_at->diffForHumans()) !!}</p>
				</div>
			</div>

			<div class="full">
				<h4>Duration:</h4>
				<p data-realtime="duration">{!! $task->duration_html !!}</p>
			</div>

			<div class="full">
				<h4>Overdue:</h4>
                <div class="full" data-realtime="overdue">{!! $task->overdue_days_html !!}</div>
			</div>
		</div> <!-- end timeline-sidebox -->
	</div>

    <div class="full follower-container-box">
        {!! $task->display_followers !!}
    </div>
</div>
