@extends('layouts.admin')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    .media-wrap { font-family: "Inter", sans-serif; }

    /* KPI mini-cards */
    .mkpi {
        border: none; border-radius: 16px; padding: 1.1rem 1.3rem;
        color: #fff; position: relative; overflow: hidden;
        box-shadow: 0 6px 20px rgba(0,0,0,.12);
        transition: transform .2s;
    }
    .mkpi:hover { transform: translateY(-3px); }
    .mkpi::after {
        content: ''; position: absolute;
        width: 90px; height: 90px; border-radius: 50%;
        background: rgba(255,255,255,.08);
        right: -20px; bottom: -20px;
    }
    .mkpi-grad-blue { background: linear-gradient(135deg,#667eea,#764ba2); }
    .mkpi-grad-rose { background: linear-gradient(135deg,#f093fb,#f5365c); }
    .mkpi-grad-teal { background: linear-gradient(135deg,#4facfe,#00f2fe); }
    .mkpi-label { font-size: .65rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; opacity: .8; }
    .mkpi-value { font-size: 1.8rem; font-weight: 800; line-height: 1.1; }
    .mkpi-sub   { font-size: .7rem; opacity: .72; margin-top: .1rem; }
    .mkpi-icon  { position: absolute; top: .9rem; right: 1.1rem; font-size: 1.6rem; opacity: .2; }

    /* Panel */
    .panel { border: none; border-radius: 20px; background: #fff; box-shadow: 0 4px 20px rgba(0,0,0,.06); }
    .panel-header { padding: 1rem 1.4rem .7rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f3f6; }
    .panel-title { font-size: .68rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #94a3b8; }
    .panel-body { padding: 1.1rem 1.4rem; }

    /* Tabs */
    .media-tabs { display: flex; gap: 0; }
    .media-tab {
        border: none; background: none;
        padding: .65rem 1.1rem;
        font-family: "Inter", sans-serif;
        font-size: .78rem; font-weight: 600; color: #94a3b8;
        border-bottom: 2.5px solid transparent; margin-bottom: -1px;
        cursor: pointer; transition: color .2s, border-color .2s;
    }
    .media-tab:hover { color: #667eea; }
    .media-tab.active { color: #667eea; border-bottom-color: #667eea; }

    /* Media grid */
    .media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: .75rem; }

    .media-card {
        border-radius: 12px; overflow: hidden; position: relative;
        aspect-ratio: 2/3; background: #0f172a; cursor: pointer;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
        transition: transform .18s, box-shadow .18s;
    }
    .media-card:hover { transform: translateY(-4px); box-shadow: 0 8px 22px rgba(0,0,0,.16); }
    .media-card img { width:100%; height:100%; object-fit:cover; display:block; }

    .media-card-overlay {
        position: absolute; inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,.72) 0%, transparent 50%);
        display: flex; flex-direction: column; justify-content: flex-end; padding: .45rem;
        opacity: 0; transition: opacity .18s;
    }
    .media-card:hover .media-card-overlay { opacity: 1; }
    .media-card-name { font-size: .58rem; color: rgba(255,255,255,.9); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .media-card-meta { font-size: .52rem; color: rgba(255,255,255,.55); margin-top: 1px; }

    .mc-actions { position: absolute; top: 5px; right: 5px; display: flex; flex-direction: column; gap: 4px; opacity: 0; transition: opacity .18s; }
    .media-card:hover .mc-actions { opacity: 1; }
    .mc-btn { width: 24px; height: 24px; border-radius: 50%; border: none; display: flex; align-items: center; justify-content: center; font-size: .6rem; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,.3); transition: transform .15s; }
    .mc-btn:hover { transform: scale(1.15); }
    .mc-preview { background: rgba(255,255,255,.9); color: #667eea; }
    .mc-preview:hover { background: #667eea; color: #fff; }
    .mc-delete  { background: rgba(255,255,255,.9); color: #f5365c; }
    .mc-delete:hover  { background: #f5365c; color: #fff; }

    .mc-play { position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); font-size: 1.6rem; color: rgba(255,255,255,.55); pointer-events: none; transition: color .18s; }
    .media-card:hover .mc-play { color: rgba(255,255,255,.85); }

    .empty-state { text-align: center; padding: 3.5rem 1rem; color: #94a3b8; }
    .empty-state i { font-size: 2.8rem; opacity: .3; display: block; margin-bottom: .75rem; }

    /* Lightbox */
    #ml-lightbox { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.92); z-index: 9999; align-items: center; justify-content: center; flex-direction: column; }
    #ml-lightbox.open { display: flex; }
    #ml-lightbox img, #ml-lightbox video { max-width: 90vw; max-height: 82vh; border-radius: 12px; box-shadow: 0 12px 60px rgba(0,0,0,.7); }
    #ml-lb-close { position: absolute; top: 18px; right: 26px; font-size: 2rem; color: rgba(255,255,255,.7); cursor: pointer; background: none; border: none; line-height: 1; transition: color .15s; }
    #ml-lb-close:hover { color: #fff; }
    #ml-lb-meta { margin-top: .75rem; font-size: .78rem; color: rgba(255,255,255,.55); text-align: center; font-family: "Inter", sans-serif; }

    /* Delete modal */
    #del-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.55); z-index: 10000; align-items: center; justify-content: center; }
    #del-modal.open { display: flex; }
    .del-modal-box {
        background: #fff; border-radius: 20px; padding: 2rem 2rem 1.5rem;
        max-width: 380px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,.25);
        text-align: center; font-family: "Inter", sans-serif;
        animation: delPop .18s ease;
    }
    @keyframes delPop { from { transform: scale(.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    .del-modal-icon { width: 52px; height: 52px; border-radius: 50%; background: #fff1f3; display: flex; align-items: center; justify-content: center; margin: 0 auto .9rem; font-size: 1.3rem; color: #f5365c; }
    .del-modal-title { font-size: 1rem; font-weight: 700; color: #1e293b; margin-bottom: .35rem; }
    .del-modal-msg { font-size: .8rem; color: #64748b; margin-bottom: 1.4rem; word-break: break-all; }
    .del-modal-actions { display: flex; gap: .7rem; justify-content: center; }
    .del-btn-cancel { flex: 1; padding: .55rem; border-radius: 10px; border: 1.5px solid #e2e8f0; background: #fff; color: #475569; font-size: .82rem; font-weight: 600; cursor: pointer; transition: background .15s; }
    .del-btn-cancel:hover { background: #f8fafc; }
    .del-btn-confirm { flex: 1; padding: .55rem; border-radius: 10px; border: none; background: linear-gradient(135deg,#f5365c,#c0392b); color: #fff; font-size: .82rem; font-weight: 700; cursor: pointer; transition: opacity .15s; }
    .del-btn-confirm:hover { opacity: .88; }
</style>

<div class="media-wrap px-2 px-md-3 py-3">

    {{-- KPI Mini Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="mkpi mkpi-grad-blue">
                <i class="fa-solid fa-camera mkpi-icon"></i>
                <div class="mkpi-label">Photos</div>
                <div class="mkpi-value">{{ $stats['total_captures'] }}</div>
                <div class="mkpi-sub">{{ round($stats['captures_size'] / 1048576, 1) }} MB on disk</div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="mkpi mkpi-grad-rose">
                <i class="fa-solid fa-video mkpi-icon"></i>
                <div class="mkpi-label">Videos</div>
                <div class="mkpi-value">{{ $stats['total_videos'] }}</div>
                <div class="mkpi-sub">{{ round($stats['videos_size'] / 1048576, 1) }} MB on disk</div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="mkpi mkpi-grad-teal">
                <i class="fa-solid fa-database mkpi-icon"></i>
                <div class="mkpi-label">Total Storage</div>
                <div class="mkpi-value">{{ round(($stats['captures_size'] + $stats['videos_size']) / 1048576, 1) }} <span style="font-size:1rem;font-weight:600;">MB</span></div>
                <div class="mkpi-sub">{{ $stats['total_captures'] + $stats['total_videos'] }} files total</div>
            </div>
        </div>
    </div>

    {{-- Media Panel --}}
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Media Library</span>
            <div class="media-tabs">
                <button class="media-tab active" data-tab="captures" onclick="switchTab('captures', this)">
                    <i class="fa-solid fa-camera me-1"></i> Photos
                    <span style="background:#667eea;color:#fff;font-size:.6rem;padding:1px 7px;border-radius:20px;margin-left:5px;font-weight:700;">{{ $stats['total_captures'] }}</span>
                </button>
                <button class="media-tab" data-tab="videos" onclick="switchTab('videos', this)">
                    <i class="fa-solid fa-video me-1"></i> Videos
                    <span style="background:#f5365c;color:#fff;font-size:.6rem;padding:1px 7px;border-radius:20px;margin-left:5px;font-weight:700;">{{ $stats['total_videos'] }}</span>
                </button>
            </div>
        </div>

        <div class="panel-body">

            {{-- Captures Tab --}}
            <div id="tab-captures">
                @if($captures->isEmpty())
                    <div class="empty-state">
                        <i class="fa-solid fa-camera"></i>
                        No captured photos yet.
                    </div>
                @else
                    <div class="media-grid">
                        @foreach($captures as $file)
                            <div id="card-capture-{{ $loop->index }}">
                                <div class="media-card">
                                    <img src="{{ $file['url'] }}" alt="{{ $file['filename'] }}" loading="lazy" />
                                    <div class="media-card-overlay">
                                        <div class="media-card-name">{{ $file['filename'] }}</div>
                                        <div class="media-card-meta">{{ round($file['size']/1024, 1) }} KB &bull; {{ $file['uploaded_at']->format('d M Y') }}</div>
                                    </div>
                                    <div class="mc-actions">
                                        <button class="mc-btn mc-preview" title="Preview"
                                            onclick="mlLightbox('image','{{ $file['url'] }}','{{ $file['filename'] }}','{{ round($file['size']/1024,1) }} KB','{{ $file['uploaded_at']->format('d M Y, H:i') }}')">
                                            <i class="fa-solid fa-expand"></i>
                                        </button>
                                        <button class="mc-btn mc-delete" title="Delete"
                                            onclick="confirmDelete('capture','{{ $file['filename'] }}','card-capture-{{ $loop->index }}')">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Videos Tab --}}
            <div id="tab-videos" style="display:none">
                @if($videos->isEmpty())
                    <div class="empty-state">
                        <i class="fa-solid fa-video"></i>
                        No uploaded videos yet.
                    </div>
                @else
                    <div class="media-grid">
                        @foreach($videos as $file)
                            <div id="card-video-{{ $loop->index }}">
                                <div class="media-card" style="background:#0f172a;">
                                    <i class="fa-solid fa-circle-play mc-play"></i>
                                    <div class="media-card-overlay">
                                        <div class="media-card-name">{{ $file['filename'] }}</div>
                                        <div class="media-card-meta">{{ round($file['size']/1048576, 2) }} MB &bull; {{ $file['uploaded_at']->format('d M Y') }}</div>
                                    </div>
                                    <div class="mc-actions">
                                        <button class="mc-btn mc-preview" title="Preview"
                                            onclick="mlLightbox('video','{{ $file['url'] }}','{{ $file['filename'] }}','{{ round($file['size']/1048576,2) }} MB','{{ $file['uploaded_at']->format('d M Y, H:i') }}')">
                                            <i class="fa-solid fa-expand"></i>
                                        </button>
                                        <button class="mc-btn mc-delete" title="Delete"
                                            onclick="confirmDelete('video','{{ $file['filename'] }}','card-video-{{ $loop->index }}')">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>{{-- .panel-body --}}
    </div>{{-- .panel --}}

</div>{{-- .media-wrap --}}

{{-- Delete Confirmation Modal --}}
<div id="del-modal" onclick="if(event.target===this)delModalClose()">
    <div class="del-modal-box">
        <div class="del-modal-icon"><i class="fa-solid fa-trash"></i></div>
        <div class="del-modal-title">Delete file?</div>
        <div class="del-modal-msg" id="del-modal-fname"></div>
        <div class="del-modal-actions">
            <button class="del-btn-cancel" onclick="delModalClose()">Cancel</button>
            <button class="del-btn-confirm" id="del-modal-confirm">Delete</button>
        </div>
    </div>
</div>

{{-- Lightbox --}}
<div id="ml-lightbox" onclick="if(event.target===this)mlLbClose()">
    <button id="ml-lb-close" onclick="mlLbClose()">&times;</button>
    <div id="ml-lb-content"></div>
    <div id="ml-lb-meta"></div>
</div>

<script>
    const _csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function switchTab(tab, btn) {
        document.getElementById('tab-captures').style.display = tab === 'captures' ? '' : 'none';
        document.getElementById('tab-videos').style.display   = tab === 'videos'   ? '' : 'none';
        document.querySelectorAll('.media-tab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }

    function mlLightbox(type, url, filename, size, date) {
        const c = document.getElementById('ml-lb-content');
        c.innerHTML = type === 'image'
            ? `<img src="${url}" alt="${filename}" />`
            : `<video src="${url}" controls autoplay style="max-width:90vw;max-height:82vh;border-radius:12px;"></video>`;
        document.getElementById('ml-lb-meta').textContent = `${filename} \u2014 ${size} \u2014 ${date}`;
        document.getElementById('ml-lightbox').classList.add('open');
    }

    function mlLbClose() {
        document.getElementById('ml-lightbox').classList.remove('open');
        document.getElementById('ml-lb-content').innerHTML = '';
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') { mlLbClose(); delModalClose(); } });

    function delModalClose() {
        document.getElementById('del-modal').classList.remove('open');
        document.getElementById('del-modal-confirm').onclick = null;
    }

    function confirmDelete(type, filename, cardId) {
        document.getElementById('del-modal-fname').textContent = filename;
        document.getElementById('del-modal').classList.add('open');
        document.getElementById('del-modal-confirm').onclick = function () {
            delModalClose();
            fetch(`/admin/media/${type}/${encodeURIComponent(filename)}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': _csrf, 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const el = document.getElementById(cardId);
                    if (el) { el.style.transition = 'opacity .3s, transform .3s'; el.style.opacity = '0'; el.style.transform = 'scale(.85)'; setTimeout(() => el.remove(), 320); }
                } else {
                    alert('Delete failed: ' + (data.error || 'unknown error'));
                }
            })
            .catch(() => alert('Network error. Please try again.'));
        };
    }
</script>
@endsection
