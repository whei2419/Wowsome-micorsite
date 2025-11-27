@extends('layouts.admin')

@section('page-title', 'Dashboard')

@section('content')
<div class="row row-deck row-cards">
    <!-- Summary Cards -->
    <x-admin.stat-card 
        title="TOTAL CUSTOMERS" 
        :value="$stats['totalCustomers']"
        color="primary">
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="7" r="4" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
        </x-slot:icon>
    </x-admin.stat-card>

    <x-admin.stat-card 
        title="TODAY'S CUSTOMER" 
        :value="$stats['todayCustomers']"
        color="red">
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="4" y="5" width="16" height="16" rx="2" /><line x1="16" y1="3" x2="16" y2="7" /><line x1="8" y1="3" x2="8" y2="7" /><line x1="4" y1="11" x2="20" y2="11" /><line x1="11" y1="15" x2="12" y2="15" /><line x1="12" y1="15" x2="12" y2="18" /></svg>
        </x-slot:icon>
    </x-admin.stat-card>

    <x-admin.stat-card 
        title="COMPLETION RATE" 
        :value="$stats['completionRate'] . '%'"
        color="green">
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="3" y1="12" x2="6" y2="12" /><line x1="12" y1="3" x2="12" y2="6" /><line x1="7.8" y1="7.8" x2="5.6" y2="5.6" /><line x1="16.2" y1="7.8" x2="18.4" y2="5.6" /><line x1="7.8" y1="16.2" x2="5.6" y2="18.4" /><path d="M12 12l9 3l-4 2l-2 4l-3 -9" /></svg>
        </x-slot:icon>
    </x-admin.stat-card>

    <x-admin.stat-card 
        title="CUSTOMERS FINISHED" 
        :value="$stats['customersFinished']"
        color="orange">
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9" /><path d="M9 12l2 2l4 -4" /></svg>
        </x-slot:icon>
    </x-admin.stat-card>

    <!-- Banner Cards -->
    <x-admin.banner-card 
        gradient="linear-gradient(90deg, #003366 0%, #0066cc 100%)">
        <x-slot:title>Weekdays<br>Reward</x-slot:title>
    </x-admin.banner-card>

    <x-admin.banner-card 
        gradient="linear-gradient(90deg, #660000 0%, #cc0000 100%)">
        <x-slot:title>Weekends<br>Reward</x-slot:title>
    </x-admin.banner-card>

    <x-admin.banner-card 
        gradient="linear-gradient(90deg, #003300 0%, #339900 100%)">
        <x-slot:title>Referral<br>Reward</x-slot:title>
    </x-admin.banner-card>

    <!-- Charts -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Customers Overview</h3>
                <div class="card-actions">
                    <div class="dropdown">
                        <a href="#" class="btn-action dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="6" x2="20" y2="6" /><line x1="4" y1="12" x2="20" y2="12" /><line x1="4" y1="18" x2="20" y2="18" /></svg>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#" onclick="exportChart('chart-customers-overview', 'csv'); return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><line x1="12" y1="11" x2="12" y2="17" /><polyline points="9 14 12 17 15 14" /></svg>
                                Download CSV
                            </a>
                            <a class="dropdown-item" href="#" onclick="exportChart('chart-customers-overview', 'png'); return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="15" y1="8" x2="15.01" y2="8" /><rect x="4" y="4" width="16" height="16" rx="3" /><path d="M4 15l4 -4a3 5 0 0 1 3 0l5 5" /><path d="M14 14l1 -1a3 5 0 0 1 3 0l2 2" /></svg>
                                Download PNG
                            </a>
                            <a class="dropdown-item" href="#" onclick="exportChart('chart-customers-overview', 'svg'); return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><polyline points="9 7 4 12 9 17" /><polyline points="15 7 20 12 15 17" /><line x1="4" y1="12" x2="20" y2="12" /></svg>
                                Download SVG
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="chart-customers-overview" 
                     style="min-height: 300px;"
                     data-dates="{{ json_encode($chartData['registrations']['dates']) }}"
                     data-counts="{{ json_encode($chartData['registrations']['counts']) }}">
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Hourly Customer Registrations by Date</h3>
                <div class="card-actions">
                    <div class="dropdown">
                        <a href="#" class="btn-action dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="6" x2="20" y2="6" /><line x1="4" y1="12" x2="20" y2="12" /><line x1="4" y1="18" x2="20" y2="18" /></svg>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#" onclick="exportChart('chart-hourly-registrations', 'csv'); return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><line x1="12" y1="11" x2="12" y2="17" /><polyline points="9 14 12 17 15 14" /></svg>
                                Download CSV
                            </a>
                            <a class="dropdown-item" href="#" onclick="exportChart('chart-hourly-registrations', 'png'); return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="15" y1="8" x2="15.01" y2="8" /><rect x="4" y="4" width="16" height="16" rx="3" /><path d="M4 15l4 -4a3 5 0 0 1 3 0l5 5" /><path d="M14 14l1 -1a3 5 0 0 1 3 0l2 2" /></svg>
                                Download PNG
                            </a>
                            <a class="dropdown-item" href="#" onclick="exportChart('chart-hourly-registrations', 'svg'); return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><polyline points="9 7 4 12 9 17" /><polyline points="15 7 20 12 15 17" /><line x1="4" y1="12" x2="20" y2="12" /></svg>
                                Download SVG
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="chart-hourly-registrations" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@vite(['resources/js/admin/dashboard.js'])
@endsection
