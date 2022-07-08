<div class="col-xs-12 col-md-4">
	<div class="full">
		<div class="timeline-sidebox float-sm-auto-md-right">
			<div class="strong uppercase">Activity completion</div>

			<div id="completion-percentage" class="circlebox">
				{!! $staff->classified_completion !!}
			</div>

			<div class="full">
				<h4>Last Login:</h4>
				<p>{!! $staff->getLastLoginHtmlAttribute('bottom') !!}</p>
			</div>

			<div class="full">
				<h4>Last Activity:</h4>

                @if ($staff->last_activity_type)
                    <div class="full" data-realtime="last_activity">
                        <p data-toggle="tooltip" data-placement="bottom" title="{{ ucfirst($staff->last_activity_type) . ':&nbsp;' . $staff->readableDateAmPm('last_activity_date') }}">{{ time_short_form($staff->last_activity_date->diffForHumans()) }}</p>
                    </div>
                @else
                    <p class="color-shadow l-space1">--</p>
                @endif
			</div>

			<div class="full">
				<h4>Next Activity:</h4>

                @if ($staff->next_activity_type)
    				<div class="full" data-realtime="next_activity">
    					<p data-toggle="tooltip" data-placement="bottom" title="{{ ucfirst($staff->next_activity_type) . ':&nbsp;' . $staff->readableDateAmPm('next_activity_date') }}">{{ time_short_form($staff->next_activity_date->diffForHumans()) }}</p>
    				</div>
                @else
                    <p class="color-shadow l-space1">--</p>
                @endif
			</div>
		</div> <!-- end timeline-sidebox -->
	</div>
</div>
