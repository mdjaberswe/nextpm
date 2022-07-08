<script>
    $(document).ready(function () {
        // Gantt filter change to user's default Gantt view
        $("select[name='gantt_filter']").val($("select[name='gantt_filter']").attr('data-default'));
        $("select[name='gantt_filter']").trigger('change');
        $("select[name='gantt_filter']").closest('div').find('.select2-hidden-accessible').trigger('change');

        // Gantt items show per page change to user's default Gantt items per page
        $("select[name='gantt_per_page']").val($("select[name='gantt_per_page']").attr('data-default'));
        $("select[name='gantt_per_page']").trigger('change');
        $("select[name='gantt_per_page']").closest('div').find('.select2-hidden-accessible').trigger('change');

        // Gantt filter change event and ajax request to load Gantt data according to the filter parameter
        $(document).on('change', "select[name='gantt_filter']", function () {
            var projectId    = $(this).data('project');
            var filter       = $(this).val();
            var ganttDataUrl = globalVar.baseAdminUrl + '/project-gantt-data/' + projectId + '/' + filter;
            $(this).closest('#item-tab-content').find('.gantt').attr('data-url', ganttDataUrl);
            // initGanttChart defined in js/app.js
            initGanttChart();
        });

        // Gantt items show per page change event
        $(document).on('change', "select[name='gantt_per_page']", function () {
            $(this).closest('#item-tab-content').find('.gantt').attr('data-per-page', $(this).val());
            initGanttChart();
        });
    });
</script>
