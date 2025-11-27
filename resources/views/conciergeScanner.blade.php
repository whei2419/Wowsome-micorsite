@extends('layouts.app')
@section('content')
<style>
    .select2-container--default .select2-selection--single {
    height: calc(2.375rem + 2px) !important;
    padding: 4px 8px !important;
    }
    .select2-selection__rendered {
        line-height: 2.375rem !important;
    }
    .select2-selection__arrow {
        height: 2.375rem !important;
    }
    /* Match height with Bootstrap form-control */
    .select2-container .select2-selection--single {
        height: calc(2.375rem + 2px); /* same as .form-control */
    }

    .select2-container .select2-selection--single .select2-selection__rendered {
        line-height: 2 !important
    }

    .select2-container .select2-selection--single .select2-selection__arrow {
        height: calc(2.375rem + 2px); /* aligns arrow with input */
    }

    .modal-content .close {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 24px;
            cursor: pointer;
            z-index: 10;
        }

</style>
<div class="container-fluid start completed-screen main-content main-background with-scroll pt-4">
        <div class="animate-entry">
            @include('components.branding')
        </div>
         <h2 class="mx-4 text-center sub-heading-text animate-entry mt-4" >SCANNER</h2>
        <div class="">
            <div class="mt-4 row justify-content-center">
                <div class="mb-4 col-lg-8 mb-lg-0">
                    <div class="card scanner-container text-center mb-5">
                        <div id="reader">
                        </div>
                    </div>
                    <div class="form-container text-center">
                        <form action="">
                            <label for="email">Key-In Customer Email</label>
                            <input type="text" name="email" id="email" class="form-control mb-2" placeholder="Enter customer email">
                            <button type="submit" class="custom-btn custom-btn-primary">Submit</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="rewardSelectionModal" tabindex="-1" aria-labelledby="rewardSelectionModal" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <span class="close text-dark " data-bs-dismiss="modal">&times;</span>
                        <div class="modal-body text-start">
                            <p class="text-danger mb-2">
                                Customer Detail
                            </p>
                            <p class="mb-2">Name:<span>Alex Morgan</span></p>
                            <p class="mb-2">Email:<span>amorgan@gmail.com</span></p>
                            <p class="text-danger mb-2">
                                Reward
                            </p>
                            <div class="col-12">
                                <select name="reward" id="rewardSelect" class="form-control">
                                    <option value="reward1">Reward 1</option>
                                    <option value="reward2">Reward 2</option>
                                    <option value="reward3">Reward 3</option>
                                </select>
                            </div>
                            <div class="row mt-3">
                                <div class="col-6">
                                    <button class="custom-btn custom-btn-danger text-white" data-bs-dismiss="modal">
                                        Cancel
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="custom-btn custom-btn-primary">
                                        Submit
                                    </button>
                                </div>
                            </div>
                        </div>
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
        </div>
    </div>



<!-- QuaggaJS Library -->
<!-- <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
<script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>

<script>

    
    document.addEventListener("DOMContentLoaded", function () {

        showRewardModal();

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
            url: '#', // Using Laravel's route() helper function
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

    function showRewardModal() {
    const modalEl = document.getElementById('rewardSelectionModal');
    const myModal = bootstrap.Modal.getOrCreateInstance(modalEl);
    myModal.show();

    // initialize select2 only once
    if (!$('#rewardSelect').data('select2')) {
        $('#rewardSelect').select2({
            dropdownParent: $('#rewardSelectionModal'),
            width: '100%'
        });
    }

    // Close button in footer
    modalEl.querySelector('.close').addEventListener('click', function () {
        myModal.hide(); // hides modal
        removeBackdrop();
    });

    // Also remove backdrop if somehow stuck
    modalEl.addEventListener('hidden.bs.modal', function () {
        removeBackdrop();
    });

    function removeBackdrop() {
        document.querySelectorAll('.modal-backdrop').forEach(function (el) {
            el.remove();
        });
    }
}

    $("#closeResponseModal").on("click", function () {
        location.reload(); // Refresh the page
    });
</script>
@endsection