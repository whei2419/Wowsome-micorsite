@extends('layouts.frontend')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h2>{{ __('Dashboard') }}</h2>
            </div>
            <div class="card-body">
                <p class="card-text">{{ __("You're logged in!") }}</p>
                <p>Welcome to your user dashboard with Bootstrap 5 styling.</p>
            </div>
        </div>
    </div>
</div>
@endsection
