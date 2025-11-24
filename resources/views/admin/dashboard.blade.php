@extends('layouts.admin')

@section('page-title', 'Admin Dashboard')

@section('content')
<div class="row row-deck row-cards">
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Total Users</div>
                </div>
                <div class="h1 mb-3">{{ \App\Models\User::count() }}</div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Admins</div>
                </div>
                <div class="h1 mb-3">{{ \App\Models\User::role('admin')->count() }}</div>
            </div>
        </div>
    </div>
    
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Welcome to Admin Dashboard</h3>
            </div>
            <div class="card-body">
                <p>You are logged in as an administrator. This is your admin dashboard with Tabler styling.</p>
            </div>
        </div>
    </div>
</div>
@endsection
