@extends('layouts.admin')

@section('content')
@php
    use Carbon\Carbon;
@endphp
<div class="row pt-2 mt-4">
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Customers</p>
                            <h5 class="font-weight-bolder">
                                {{ $data['usersCount'] }}
                            </h5>
                            {{-- <p class="mb-0">
                                <span class="text-success text-sm font-weight-bolder">+55%</span>
                                since yesterday
                            </p> --}}
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                            <i class="fa-solid fa-user text-lg opacity-10" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Today's Customer</p>
                            <h5 class="font-weight-bolder">
                                {{ $data['userToday'] }}
                            </h5>
                            {{-- <p class="mb-0">
                                <span class="text-success text-sm font-weight-bolder">+3%</span>
                                since last week
                            </p> --}}
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                            <i class="fa-solid fa-calendar-day text-lg opacity-10" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Completion Rate</p>
                            <h5 class="font-weight-bolder">
                                {{ $data['percentage'] }}%
                            </h5>

                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                            <i class="fa-solid fa-percent text-lg opacity-10" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Customers Finished</p>
                            <h5 class="font-weight-bolder">
                                {{ $data['completedUsers'] }}
                            </h5>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                            <i class="fa-solid fa-circle-check text-lg opacity-10" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="mt-4 row">
    <div class="mb-4 col-lg-12 mb-lg-0">
        <div class="card table-card py-3">
            {{-- <div class="p-3 pb-0 card-header">
                <div class="d-flex justify-content-between">
                    <h6 class="mb-2">Customer</h6>
                </div>
            </div> --}}
            <div class="p-3 px-4">
                <div class="loader-container">
                    <div class="loader"></div>
                    <p class="mt-2">Loading...</p>
                </div>
                <table id="customer-table" class="display nowrap border" style="display: none; width: 100%;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th class="sticky-action">Name</th>
                            <th>Email</th>
                            <th>Company</th>
                            <th>Number</th>
                            <th>Race</th>
                            <th>Country</th>
                            @foreach ($data['stations'] as $station)
                            <th>{{ $station['name'] }}</th>
                            @endforeach
                            <th>Timestamp</th>
                            <th>Action</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data['users'] as $user)
                        <tr data-user-id="{{ $user->id }}">
                            <td>{{ $user->id }}</td>
                            <td class="sticky-action">
                                {{ $user->name }}
                                @if($user->hasRole('admin'))
                                    <span class="badge bg-warning text-dark ms-1">
                                        <i class="fa fa-crown"></i> Admin
                                    </span>
                                @endif
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->company }}</td>
                            <td>{{ $user->number }}</td>
                            <td>{{ $user->race }}</td>
                            <td>{{ $user->country }}</td>

                            @foreach ($user['stations'] as $station)
                            <td class="text-sm mb-0 {{ $station['value'] ? 'text-success' : 'text-danger' }}">
                                {{ $station['value'] ? 'Yes' : 'No' }}</td>
                            @endforeach

                            <td class="button-delete">
                                @if($user->isProtectedAdmin())
                                    <button class="btn btn-secondary btn-sm btn-protected" disabled 
                                            title="This admin user is protected and cannot be deleted"
                                            data-bs-toggle="tooltip" data-bs-placement="top">
                                        <i class="fa fa-lock"></i> Protected
                                    </button>
                                @else
                                    <button class="btn btn-danger btn-sm delete-user-btn" data-user-id="{{ $user->id }}"
                                        data-user-name="{{ $user->name }}">Delete</button>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($user->created_at)->toDayDateTimeString() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="deleteUserForm">
            @csrf
            @method('DELETE')
            <div class="modal-content">
                <div class="modal-header bg-white text-white">
                    <h5 class="modal-title" id="deleteUserModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete <strong id="deleteUserName"></strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Include DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.css">

<!-- Include DataTables Buttons CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.dataTables.css">

<style>
    .sticky-action {
        position: sticky !important;
        left: 0 !important;
        background-color: white !important;
        z-index: 10 !important;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1) !important;
    }

    /* Sticky header for the name column */
    thead .sticky-action {
        background-color: #f8f9fa !important;
        z-index: 11 !important;
    }

    /* Ensure the table wrapper allows horizontal scrolling */
    .custom-table {
        overflow-x: auto !important;
    }

    /* Make sure the sticky column has proper border */
    .sticky-action::after {
        content: '';
        position: absolute;
        right: -1px;
        top: 0;
        bottom: 0;
        width: 1px;
        background-color: #dee2e6;
    }

    /* Ensure minimum width for the name column */
    .sticky-action {
        min-width: 120px !important;
    }

    /* Protected admin button styling */
    .btn-protected {
        background-color: #6c757d !important;
        border-color: #6c757d !important;
        cursor: not-allowed !important;
        opacity: 0.7 !important;
    }

    .btn-protected:hover {
        background-color: #5c636a !important;
        border-color: #565e64 !important;
        transform: none !important;
    }

    /* Admin badge styling */
    .badge.bg-warning {
        font-size: 0.65rem !important;
        padding: 0.25rem 0.4rem !important;
    }

    .badge .fa-crown {
        font-size: 0.6rem !important;
    }
</style>
<script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/2.0.7/js/dataTables.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/2.0.7/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.dataTables.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.min.js"></script>

<!-- Include DataTables Buttons JS -->

<script>
    // Show the loader
    $('.loader-container').show();
    $('#customer-table').hide();

    var permissionName = "{{ $permission }}";
    var table = $('#customer-table').DataTable({
        responsive: true,
        dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6 d-flex justify-content-end'f>>" +
            "<'row'<'col-sm-12 table-responsive custom-table' tr>>" +
            "<'row'<'d-flex justify-content-start col-sm-12 col-md-6 mt-3'i><'col-sm-12 col-md-6 mt-3 d-flex justify-content-end'p>>",
        buttons: [{
            extend: 'csv',
            text: '<i class="fa fa-file-csv"></i> CSV',
            className: 'btn btn-info'
        }, {
            extend: 'excel',
            text: '<i class="fa fa-file-excel"></i> Excel',
            className: 'btn btn-success'
        }, {
            extend: 'pdf',
            text: '<i class="fa fa-file-pdf"></i> PDF',
            className: 'btn btn-danger'
        }],
        order: [
            [0, 'desc']
        ],
        columnDefs: [{
            orderable: false,
            targets: -1
        } // Disable sorting on last column (Action)
        ],
        initComplete: function (settings, json) {
            $('.loader-container').hide();
            $('#customer-table').show();
            
            // Initialize Bootstrap tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    });


    // Move the search input to the right side
    $('.dataTables_filter').addClass('float-end');
    $('.dataTables_filter label').addClass('w-100');

    $('#customer-table tbody').on('click', 'tr', function (e) {
        // Prevent redirect if the clicked target is inside a delete button
        console.log('cliked');
        if ($(e.target).closest('.delete-user-btn').length) {
            return;
        }

        var userId = $(this).data('user-id');

        window.location.href = "{{ route('userData', ['user' => ':userId']) }}".replace(
            ':userId', userId);
    });

    // Use event delegation for delete button
    $('#customer-table tbody').on('click', '.delete-user-btn', function (e) {
        e.stopPropagation(); // Prevent row click
        const userId = $(this).data('user-id');
        const userName = $(this).data('user-name');

        let deleteUrl = @json(route('users.destroy', ['id' => ':id']));
    deleteUrl = deleteUrl.replace(':id', userId);

    $('#deleteUserForm').attr('action', deleteUrl);
    $('#deleteUserName').text(userName);
    $('#deleteUserModal').modal('show');
            });
</script>

@if(session('success'))
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="successToast" class="toast align-items-center bg-success text-white border-0 fade show" role="alert"
        aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center">
                <i class="fa fa-check-circle me-2"></i>
                {{ session('success') }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toastEl = document.getElementById('successToast');
        if (toastEl) {
            var toast = new bootstrap.Toast(toastEl, { delay: 3000 });
            toast.show();
        }
    });
</script>
@endif

@if(session('error'))
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="errorToast" class="toast align-items-center bg-danger text-white border-0 fade show" role="alert"
        aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center">
                <i class="fa fa-exclamation-circle me-2"></i>
                {{ session('error') }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toastEl = document.getElementById('errorToast');
        if (toastEl) {
            var toast = new bootstrap.Toast(toastEl, { delay: 5000 });
            toast.show();
        }
    });
</script>
@endif
@endsection