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
        <div class="row mt-4 mb-3 px-3">
            <div class="col-md-3">
                <input type="date" id="startDate" class="form-control">
            </div>
            <div class="col-md-3">
                <input type="date" id="endDate" class="form-control">
            </div>
            <div class="col-md-3">
                <button id="filterDate" class="btn btn-primary">Filter</button>
                <button id="resetDate" class="btn btn-secondary">Reset</button>
            </div>
        </div>
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
                                <th>Fullname</th>
                                <th>Email</th>
                                <th>Created At</th>
                                <th>Marketing</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data['users'] as $user)
                                <tr data-user-id="{{ $user->id }}">
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->fname }}</td>
                                    <td>{{ $user->email ?? '-' }}</td>
                                    <td>{{ $user->created_at ? $user->created_at->format('d M Y, h:i A') : '-' }}</td>
                                    <td>{{ $user->marketing ? 'Yes' : 'No' }}</td>
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

    <div class="modal fade" id="exportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Name Export File</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="exportFileName" class="form-control" placeholder="Enter file name">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button id="confirmExport" class="btn btn-primary">Export</button>
                </div>
            </div>
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
        let exportType = 'export';

        const today = new Date().toISOString().split('T')[0];

        $('#startDate').val(today);
        $('#endDate').val(today);

        $('#startDate, #endDate').on('change', function() {
            const startDate = $('#startDate').val();
            const endDate = $('#endDate').val();

            if (startDate && endDate && endDate < startDate) {
                alert('"To date" cannot be earlier than "From date".');
                $('#endDate').val(startDate); // auto-fix
            }
        });

        $('#startDate').on('change', function() {
            const startDate = $(this).val();
            $('#endDate').attr('min', startDate);
        });

        /* ===================== DATATABLE INIT ===================== */
        var table = $('#customer-table').DataTable({
            responsive: true,
            dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6 d-flex justify-content-end'f>>" +
                "<'row'<'col-sm-12 table-responsive custom-table' tr>>" +
                "<'row'<'d-flex justify-content-start col-sm-12 col-md-6 mt-3'i>" +
                "<'col-sm-12 col-md-6 mt-3 d-flex justify-content-end'p>>",

            buttons: [{
                    extend: 'csvHtml5',
                    className: 'd-none btn-export-csv',
                    filename: function() {
                        return exportFileName; // ✅ ALWAYS correct
                    },
                    exportOptions: {
                        columns: ':visible',
                        modifier: {
                            search: 'applied'
                        }
                    }
                },
                {
                    extend: 'excelHtml5',
                    className: 'd-none btn-export-excel',
                    filename: function() {
                        return exportFileName; // ✅ ALWAYS correct
                    },
                    exportOptions: {
                        columns: ':visible',
                        modifier: {
                            search: 'applied'
                        }
                    }
                },
                {
                    text: '<i class="fa fa-file-csv"></i> CSV',
                    className: 'btn btn-info',
                    action: function() {
                        exportType = 'csv';
                        $('#exportModal').modal('show');
                    }
                },
                {
                    text: '<i class="fa fa-file-excel"></i> Excel',
                    className: 'btn btn-success',
                    action: function() {
                        exportType = 'excel';
                        $('#exportModal').modal('show');
                    }
                }
            ],

            order: [
                [0, 'desc']
            ],

            columnDefs: [{
                orderable: false,
                targets: -1 // Action/Delete column
            }],

            initComplete: function() {
                $('.loader-container').hide();
                $('#customer-table').show();

                // Initialize Bootstrap tooltips
                var tooltipTriggerList = [].slice.call(
                    document.querySelectorAll('[data-bs-toggle="tooltip"]')
                );
                tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
            }
        });

        /* ===================== DATE RANGE FILTER ===================== */
        $.fn.dataTable.ext.search.push(function(settings, data) {
            let start = $('#startDate').val();
            let end = $('#endDate').val();

            // Timestamp column (second last column)
            let rowDate = new Date(data[data.length - 2]);

            // Convert row date to YYYY-MM-DD (DATE ONLY)
            let rowDateOnly = rowDate.getFullYear() + '-' +
                String(rowDate.getMonth() + 1).padStart(2, '0') + '-' +
                String(rowDate.getDate()).padStart(2, '0');

            if (!start && !end) return true;

            if (start && rowDateOnly < start) return false;
            if (end && rowDateOnly > end) return false;

            return true;
        });

        /* ===================== DATE FILTER BUTTONS ===================== */
        $('#filterDate').on('click', function() {
            table.draw();
        });

        $('#resetDate').on('click', function() {
            $('#startDate').val('');
            $('#endDate').val('');
            table.draw();
        });

        /* ===================== EXPORT CONFIRM ===================== */
        $('#confirmExport').on('click', function() {
            // ✅ Store filename BEFORE triggering export
            exportFileName = $('#exportFileName').val().trim();

            if (!exportFileName) {
                alert('Please enter a file name');
                return;
            }

            if (exportType === 'csv') {
                table.button('.btn-export-csv').trigger();
            }

            if (exportType === 'excel') {
                table.button('.btn-export-excel').trigger();
            }

            // ✅ Clear AFTER export
            $('#exportModal').modal('hide');
            $('#exportFileName').val('');
        });

        /* ===================== SEARCH INPUT POSITION ===================== */
        $('.dataTables_filter').addClass('float-end');
        $('.dataTables_filter label').addClass('w-100');

        /* ===================== ROW CLICK REDIRECT ===================== */
        $('#customer-table tbody').on('click', 'tr', function(e) {
            if ($(e.target).closest('.delete-user-btn').length) return;

            var userId = $(this).data('user-id');
            window.location.href = "{{ route('userData', ['user' => ':userId']) }}"
                .replace(':userId', userId);
        });

        /* ===================== DELETE BUTTON ===================== */
        $('#customer-table tbody').on('click', '.delete-user-btn', function(e) {
            e.stopPropagation();

            const userId = $(this).data('user-id');
            const userName = $(this).data('user-name');

            let deleteUrl = @json(route('users.destroy', ['id' => ':id']));
            deleteUrl = deleteUrl.replace(':id', userId);

            $('#deleteUserForm').attr('action', deleteUrl);
            $('#deleteUserName').text(userName);
            $('#deleteUserModal').modal('show');
        });
    </script>

    @if (session('success'))
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
            <div id="successToast" class="toast align-items-center bg-success text-white border-0 fade show"
                role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
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
            document.addEventListener('DOMContentLoaded', function() {
                var toastEl = document.getElementById('successToast');
                if (toastEl) {
                    var toast = new bootstrap.Toast(toastEl, {
                        delay: 3000
                    });
                    toast.show();
                }
            });
        </script>
    @endif

    @if (session('error'))
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
            document.addEventListener('DOMContentLoaded', function() {
                var toastEl = document.getElementById('errorToast');
                if (toastEl) {
                    var toast = new bootstrap.Toast(toastEl, {
                        delay: 5000
                    });
                    toast.show();
                }
            });
        </script>
    @endif
@endsection
