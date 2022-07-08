<div class="modal fade" id="license-info">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">How to get a license key?</h4>
            </div>
            <div class="modal-body">
                <div class="col-xs-12">
                    <div class="alert-note">
                        <h3>Email Us</h3>
                        <ul style="list-style: none; padding: 0;">
                            <li>To: <strong>mdjaber.swe@gmail.com</strong></li>
                            <li>Subject: <strong>NextPM - Purchase Code Request</strong></li>
                        </ul>
                    </div>
                    <h6 style="padding-left: 5px;">OR,</h6>
                    <div class="alert-note">
                        <h3>Contact Us on Facebook</h3>
                        <a href="https://facebook.com/mdjaber.swe" target="_blank">https://facebook.com/mdjaber.swe</a>
                    </div>
                </div>
            </div> <!-- end modal-body -->
            <div class="modal-footer btn-container">
                <button type="button" class="cancel btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div> <!-- end modal-dialog -->
</div> <!-- end confirm-delete -->

@push('scripts')
    <script>
        $(document).ready(function () {
            $(document).on('click', '.license-info', function (event) {
                $("#license-info").modal({
                    show     : true,
                    backdrop : false,
                    keyboard : false
                });
            });
        });
    </script>
@endpush
