@extends('layouts.guest')

@section('title', 'Wishing Lantern')

@section('content')
<style>
    .download-page {
  background: linear-gradient(180deg, #fde7a9, #f8d27a);
  min-height: 100svh;
}

.text-orange {
  color: #d96b00;
}

.image-box {
  width: 80%;
  max-width: 280px;
  aspect-ratio: 1 / 1;
  background: #d9d9d9;
  border-radius: 8px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.preview-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.download-btn {
  background-color: #d96b00;
  color: #fff;
  padding: 10px 40px;
  border-radius: 999px;
  font-weight: 600;
}

.download-btn:hover {
  background-color: #bf5f00;
  color: #fff;
}

</style>
<div class="download-page">
  <div class="main-content with-scroll text-center">

    {{-- Branding --}}
    <div class="mb-3">
      @include('components.branding')
    </div>

    {{-- Title --}}
    <h3 class="text-center text-orange fw-bold mb-2">
      福气许愿灯笼
    </h3>
    <h4 class="text-center text-orange fw-bold mb-4">
      WISHING LANTERN
    </h4>

    {{-- Date & Time --}}
    <div class="date-time text-orange mb-4">
      <div class="d-flex justify-content-center align-items-center gap-2 mb-1">
        <i class="bi bi-calendar"></i>
        <span>{{ $date }}</span>
      </div>
      <div class="d-flex justify-content-center align-items-center gap-2">
        <i class="bi bi-clock"></i>
        <span>{{ $time }}</span>
      </div>
    </div>

    {{-- Image Preview Box --}}
    <div class="image-box mx-auto mb-4">
      @if($imageUrl)
        <img
          src="{{ $imageUrl }}"
          alt="Lantern Image"
          class="preview-image"
        >
      @else
        <div class="placeholder"></div>
      @endif
    </div>

    {{-- Download Button --}}
    @if($downloadUrl)
      <a
        href="{{ $downloadUrl }}"
        class="btn download-btn"
        download
      >
        DOWNLOAD
      </a>
    @endif

    <x-footer />
  </div>
</div>
@endsection
