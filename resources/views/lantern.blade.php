@extends('layouts.guest')

@section('title', 'Wishing Lantern')

@section('content')
<style>
    .download-page {
  background-image: url('{{ asset("images/brand/main-bg.webp") }}');
  min-height: 100svh;
  background-size: cover;
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
  width: auto;
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

.dt-icon {
    width: 16px;
    height: 16px;
    object-fit: contain;
}

</style>
<div class="download-page">
  <div class="main-content with-scroll text-center">

    {{-- Branding --}}
    <div class="mb-3">
      @include('components.branding')
    </div>

    {{-- Title --}}
    <div class="title-container">
      <h3 class="text-center text-orange fw-bold mb-2">
      福气许愿灯笼
      </h3>
      <h4 class="text-center text-orange fw-bold mb-4">
        WISHING LANTERN
      </h4>
    </div>

    {{-- Date & Time --}}
    <div class="date-time text-orange mb-4">
        <!-- DATE -->
        <div class="d-flex align-items-center gap-2 mb-1">
            <img src="{{ asset('images/brand/date.webp') }}" alt="Date" class="dt-icon">
            <span>{{ $date }}</span>
        </div>

        <!-- TIME -->
        <div class="d-flex align-items-center gap-2">
            <img src="{{ asset('images/brand/time.webp'); }}" alt="Time" class="dt-icon">
            <span>{{ $time }}</span>
        </div>
    </div>

    <div class="image-box mx-auto mb-4 mx-5">
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
        class="btn download-btn w-50 mx-auto p-2"
        download
      >
        DOWNLOAD
      </a>
    @endif

    <x-footer />
  </div>
</div>
@endsection
