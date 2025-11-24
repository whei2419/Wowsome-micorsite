@extends('layouts.admin')

@section('page-title', 'Dashboard')

@section('content')
<div class="row row-deck row-cards">
    <!-- Summary Cards -->
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-primary text-white avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="7" r="4" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">
                            TOTAL CUSTOMERS
                        </div>
                        <div class="text-muted">
                            1181
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-red text-white avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="4" y="5" width="16" height="16" rx="2" /><line x1="16" y1="3" x2="16" y2="7" /><line x1="8" y1="3" x2="8" y2="7" /><line x1="4" y1="11" x2="20" y2="11" /><line x1="11" y1="15" x2="12" y2="15" /><line x1="12" y1="15" x2="12" y2="18" /></svg>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">
                            TODAY'S CUSTOMER
                        </div>
                        <div class="text-muted">
                            0
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-green text-white avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="3" y1="12" x2="6" y2="12" /><line x1="12" y1="3" x2="12" y2="6" /><line x1="7.8" y1="7.8" x2="5.6" y2="5.6" /><line x1="16.2" y1="7.8" x2="18.4" y2="5.6" /><line x1="7.8" y1="16.2" x2="5.6" y2="18.4" /><path d="M12 12l9 3l-4 2l-2 4l-3 -9" /></svg>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">
                            COMPLETION RATE
                        </div>
                        <div class="text-muted">
                            86.45%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-orange text-white avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9" /><path d="M9 12l2 2l4 -4" /></svg>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">
                            CUSTOMERS FINISHED
                        </div>
                        <div class="text-muted">
                            1021
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Banner Cards -->
    <div class="col-md-4">
        <div class="card bg-primary text-primary-fg">
            <div class="card-body" style="background: linear-gradient(90deg, #003366 0%, #0066cc 100%); border-radius: 4px;">
                <div class="text-center py-4">
                    <h3 class="mb-0">Weekdays<br>Reward</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-danger-fg">
            <div class="card-body" style="background: linear-gradient(90deg, #660000 0%, #cc0000 100%); border-radius: 4px;">
                <div class="text-center py-4">
                    <h3 class="mb-0">Weekends<br>Reward</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-success-fg">
            <div class="card-body" style="background: linear-gradient(90deg, #003300 0%, #339900 100%); border-radius: 4px;">
                <div class="text-center py-4">
                    <h3 class="mb-0">Referral<br>Reward</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Customers Overview</h3>
                <div class="card-actions">
                    <a href="#" class="btn-action">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="6" x2="20" y2="6" /><line x1="4" y1="12" x2="20" y2="12" /><line x1="4" y1="18" x2="20" y2="18" /></svg>
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div id="chart-customers-overview" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Hourly Customer Registrations by Date</h3>
                <div class="card-actions">
                    <a href="#" class="btn-action">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="6" x2="20" y2="6" /><line x1="4" y1="12" x2="20" y2="12" /><line x1="4" y1="18" x2="20" y2="18" /></svg>
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div id="chart-hourly-registrations" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Customers Overview Chart
        var optionsOverview = {
            series: [{
                name: "Registration",
                data: [213, 302, 80, 102, 96, 228, 160]
            }],
            chart: {
                height: 300,
                type: 'line',
                zoom: {
                    enabled: false
                },
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: true
            },
            stroke: {
                curve: 'smooth'
            },
            xaxis: {
                categories: ['September 15', 'September 16', 'September 17', 'September 18', 'September 19', 'September 20', 'September 21'],
            },
            colors: ['#206bc4'],
        };

        var chartOverview = new ApexCharts(document.querySelector("#chart-customers-overview"), optionsOverview);
        chartOverview.render();

        // Hourly Customer Registrations Chart
        var optionsHourly = {
            series: [{
                name: '2025-09-15',
                data: [1, 1, 11, 10, 17, 35, 37, 41, 62]
            }, {
                name: '2025-09-16',
                data: [0, 0, 9, 6, 13, 19, 20, 27, 12]
            }, {
                name: '2025-09-17',
                data: [0, 0, 0, 7, 13, 20, 21, 16, 20]
            }],
            chart: {
                type: 'bar',
                height: 300,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: ['3am', '9am', '10am', '11am', '12pm', '1pm', '2pm', '3pm', '4pm'],
            },
            fill: {
                opacity: 1
            },
        };

        var chartHourly = new ApexCharts(document.querySelector("#chart-hourly-registrations"), optionsHourly);
        chartHourly.render();
    });
</script>
@endsection
