@extends('layouts.admin') 
@section('content')
<style>

</style>
<div class="mt-4 row justify-content-center">
    <div class="mb-4 col-lg-8 mb-lg-0">
        <div class="card scanner-container text-center">
            <div class="p-3 pb-0 card-header">
                <h6 class="mb-2">QR Scanner</h6>
            </div>
            <div id="reader">
            </div>
            <div class="mt-3 py-3">
                <h4>Scanned Code:</h4>
                <div id="scanned-result" class="font-weight-bold"></div>
            </div>

            <!-- <div class="mt-3">
                <label for="manual-code" class="form-label">Manual Code Entry:</label>
                <input type="text" id="manual-code" class="form-control" placeholder="Enter code here"
                    style="max-width: 300px; margin: 0 auto" />
                <button id="add-code" class="btn btn-primary mt-2" style="max-width: 150px">
                    Add Code
                </button>
            </div> -->
        </div>
    </div>
</div>

<!-- Response Message Modal -->
<div class="modal fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responseModalLabel">QR Scan Result</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <span id="responseMessage" class="fs-5"></span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="closeResponseModal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>


<!-- QuaggaJS Library -->
<script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
<script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Initialize QuaggaJS
        const html5QrCode = new Html5Qrcode("reader");

        html5QrCode.start({
            facingMode: "environment"
        }, {
            fps: 10,
            qrbox: 200,
            aspectRatio: 2 / 2 // Set the aspect ratio to 16:9
        },
            qrCodeMessage => {
                sendMessage(`${qrCodeMessage}`);
                html5QrCode.stop();
            },
            errorMessage => {
                console.log(`QR Code no longer in front of camera.`);
            })
            .catch(err => {
                console.log(`Unable to start scanning, error: ${err}`);
            });
    });



    function sendMessage(message) {
        // Fetch the CSRF token from the meta tag
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        console.log(message);

        $.ajax({
            url: '{{ route('admin.workshop.scan') }}', // Using Laravel's route() helper function
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken, // Include the CSRF token in the headers
            },
            data: {
                qrCodeMessage: message,
            },
            success: function (response) {
                // You can customize this based on your actual response
                console.log(response);
                let message = '';

                if (response.status === 'success') {
                    message = '✅ Scanned Successfully ';
                } else if (response.status === 'already_redeemed') {
                    message = '⚠️ Already Attended';
                } else if (response.status === 'invalid') {
                    message = '❌ Invalid QR';
                } else {
                    message = 'ℹ️ Unknown response';
                }


                $("#responseMessage").text(message);
                $("#responseModal").modal('show');
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                $("#responseMessage").text('❌ An error occurred while processing the QR code.');
                $("#responseModal").modal('show');
            }
        });
    }

    $("#closeResponseModal").on("click", function () {
        location.reload(); // Refresh the page
    });
</script>
@endsection
