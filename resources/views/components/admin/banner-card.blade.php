@props(['title', 'gradient'])

<div class="col-md-4">
    <div class="card bg-primary text-primary-fg">
        <div class="card-body" style="background: {{ $gradient }}; border-radius: 4px;">
            <div class="text-center py-4">
                <h3 class="mb-0">{!! $title !!}</h3>
            </div>
        </div>
    </div>
</div>
