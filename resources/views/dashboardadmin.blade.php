@extends('layouts.admin')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    .dash-wrap { font-family: 'Inter', sans-serif; }

    /* ── Stat Cards ── */
    .kpi-card {
        border: none;
        border-radius: 20px;
        padding: 1.5rem 1.6rem;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0,0,0,.15);
        transition: transform .2s;
    }
    .kpi-card:hover { transform: translateY(-4px); }
    .kpi-card::after {
        content: '';
        position: absolute;
        width: 130px; height: 130px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
        right: -30px; bottom: -30px;
    }
    .kpi-card::before {
        content: '';
        position: absolute;
        width: 80px; height: 80px;
        border-radius: 50%;
        background: rgba(255,255,255,.06);
        right: 40px; bottom: 30px;
    }
    .kpi-grad-blue   { background: linear-gradient(135deg,#667eea,#764ba2); }
    .kpi-grad-rose   { background: linear-gradient(135deg,#f093fb,#f5365c); }
    .kpi-grad-teal   { background: linear-gradient(135deg,#4facfe,#00f2fe); }
    .kpi-grad-orange { background: linear-gradient(135deg,#f7971e,#ffd200); }

    .kpi-label { font-size: .7rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; opacity: .8; }
    .kpi-value { font-size: 2.4rem; font-weight: 800; line-height: 1.1; margin: .2rem 0; }
    .kpi-sub   { font-size: .78rem; opacity: .75; }
    .kpi-icon  {
        position: absolute; top: 1.2rem; right: 1.4rem;
        font-size: 2rem; opacity: .25;
    }

    /* ── Panel Cards ── */
    .panel {
        border: none;
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 4px 20px rgba(0,0,0,.06);
    }
    .panel-header {
        padding: 1.25rem 1.5rem .75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #f1f3f6;
    }
    .panel-title {
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: #94a3b8;
    }
    .panel-body { padding: 1.25rem 1.5rem; }

    /* ── Chart ── */
    #uploadsChart { height: 200px !important; }

    /* ── Recent grid ── */
    .recent-grid {
        display: grid;
        grid-template-columns: repeat(8, 1fr);
        gap: .5rem;
    }
    @media(max-width:1200px){ .recent-grid { grid-template-columns: repeat(6,1fr); } }
    @media(max-width:768px){  .recent-grid { grid-template-columns: repeat(4,1fr); } }

    .recent-item {
        border-radius: 10px;
        overflow: hidden;
        position: relative;
        aspect-ratio: 3/4;
        background: #0f172a;
        box-shadow: 0 2px 8px rgba(0,0,0,.1);
        transition: transform .2s, box-shadow .2s;
        cursor: pointer;
    }
    .recent-item:hover { transform: scale(1.04); box-shadow: 0 6px 18px rgba(0,0,0,.2); }
    .recent-item img { width:100%; height:100%; object-fit:cover; display:block; }
    .recent-item-overlay {
        position: absolute; inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,.72) 0%, transparent 50%);
        display: flex; flex-direction: column; justify-content: flex-end;
        padding: .4rem;
    }
    .recent-item-badge {
        display: inline-block;
        font-size: .52rem; font-weight: 700; letter-spacing: .06em;
        text-transform: uppercase;
        background: rgba(255,255,255,.18);
        backdrop-filter: blur(6px);
        color: #fff;
        border-radius: 3px;
        padding: 1px 5px;
        margin-bottom: .2rem;
        width: fit-content;
    }
    .recent-item-name { font-size: .58rem; color: rgba(255,255,255,.85); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .recent-item-date { font-size: .52rem; color: rgba(255,255,255,.5); }
    .video-play-icon {
        position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%);
        font-size: 2rem; color: rgba(255,255,255,.7);
        pointer-events: none;
    }

    /* ── Quick actions ── */
    .action-btn {
        display: flex; align-items: center; gap: .75rem;
        padding: .85rem 1rem;
        border-radius: 14px;
        border: 1.5px solid #e8ecf4;
        background: #fff;
        color: #344767;
        font-weight: 600;
        font-size: .82rem;
        text-decoration: none;
        transition: border-color .2s, box-shadow .2s, transform .15s;
    }
    .action-btn:hover {
        border-color: #667eea;
        box-shadow: 0 4px 16px rgba(102,126,234,.15);
        transform: translateY(-2px);
        color: #344767;
    }
    .action-btn .ab-icon {
        width: 38px; height: 38px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
    }

    /* ── Lightbox ── */
    #dash-lightbox {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,.9); z-index: 9999;
        align-items: center; justify-content: center;
    }
    #dash-lightbox.open { display: flex; }
    #dash-lightbox img, #dash-lightbox video {
        max-width: 90vw; max-height: 85vh;
        border-radius: 12px; box-shadow: 0 12px 60px rgba(0,0,0,.6);
    }
    #dash-lb-close {
        position: absolute; top: 20px; right: 28px;
        font-size: 2rem; color: #fff; cursor: pointer;
        background: none; border: none; line-height: 1;
    }
</style>

<div class="dash-wrap px-2 px-md-3 py-3">

    {{-- ── KPI Row ── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="kpi-card kpi-grad-blue">
                <i class="fa-solid fa-camera kpi-icon"></i>
                <div class="kpi-label">Photos</div>
                <div class="kpi-value">{{ $data['capture_count'] }}</div>
                <div class="kpi-sub">+{{ $data['capture_today'] }} today</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="kpi-card kpi-grad-rose">
                <i class="fa-solid fa-video kpi-icon"></i>
                <div class="kpi-label">Videos</div>
                <div class="kpi-value">{{ $data['video_count'] }}</div>
                <div class="kpi-sub">+{{ $data['video_today'] }} today</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="kpi-card kpi-grad-teal">
                <i class="fa-solid fa-photo-film kpi-icon"></i>
                <div class="kpi-label">Total Uploads</div>
                <div class="kpi-value">{{ $data['capture_count'] + $data['video_count'] }}</div>
                <div class="kpi-sub">+{{ $data['capture_today'] + $data['video_today'] }} today</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="kpi-card kpi-grad-orange">
                <i class="fa-solid fa-hard-drive kpi-icon"></i>
                <div class="kpi-label">Storage Used</div>
                <div class="kpi-value">{{ round($data['total_size'] / 1048576, 1) }}<span style="font-size:1rem;font-weight:600"> MB</span></div>
                <div class="kpi-sub">📷 {{ round($data['capture_size']/1048576,1) }} MB &nbsp;🎬 {{ round($data['video_size']/1048576,1) }} MB</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">

        {{-- ── Upload Chart ── --}}
        <div class="col-lg-8">
            <div class="panel h-100">
                <div class="panel-header">
                    <span class="panel-title">Upload Activity — Last 14 Days</span>
                    <span style="font-size:.72rem;color:#94a3b8;">Photos &amp; Videos combined</span>
                </div>
                <div class="panel-body">
                    <canvas id="uploadsChart"></canvas>
                </div>
            </div>
        </div>

        {{-- ── Quick Actions ── --}}
        <div class="col-lg-4">
            <div class="panel h-100">
                <div class="panel-header">
                    <span class="panel-title">Quick Actions</span>
                </div>
                <div class="panel-body d-flex flex-column gap-2">
                    <a href="{{ route('admin.media') }}" class="action-btn">
                        <div class="ab-icon" style="background:rgba(102,126,234,.12);color:#667eea;">
                            <i class="fa-solid fa-images"></i>
                        </div>
                        <div>
                            <div>Open Media Library</div>
                            <div style="font-size:.72rem;font-weight:400;color:#94a3b8;">View, preview &amp; delete files</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.media') }}?tab=captures" class="action-btn">
                        <div class="ab-icon" style="background:rgba(79,172,254,.12);color:#4facfe;">
                            <i class="fa-solid fa-camera"></i>
                        </div>
                        <div>
                            <div>Browse Photos</div>
                            <div style="font-size:.72rem;font-weight:400;color:#94a3b8;">{{ $data['capture_count'] }} captured photos</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.media') }}?tab=videos" class="action-btn">
                        <div class="ab-icon" style="background:rgba(245,54,92,.1);color:#f5365c;">
                            <i class="fa-solid fa-video"></i>
                        </div>
                        <div>
                            <div>Browse Videos</div>
                            <div style="font-size:.72rem;font-weight:400;color:#94a3b8;">{{ $data['video_count'] }} uploaded videos</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Recent Uploads Grid ── --}}
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Recent Uploads</span>
            <a href="{{ route('admin.media') }}" style="font-size:.75rem;font-weight:600;color:#667eea;text-decoration:none;">View all &rarr;</a>
        </div>
        <div class="panel-body">
            @if($data['recent_uploads']->isEmpty())
                <p class="text-muted small mb-0 text-center py-4">No uploads yet.</p>
            @else
                <div class="recent-grid">
                    @foreach($data['recent_uploads'] as $file)
                        <div class="recent-item"
                            onclick="dashLightbox('{{ $file['type'] }}','{{ $file['url'] }}','{{ $file['filename'] }}','{{ $file['uploaded_at']->format('d M Y, H:i') }}')">
                            @if($file['type'] === 'capture')
                                <img src="{{ $file['url'] }}" alt="{{ $file['filename'] }}" loading="lazy">
                            @else
                                <div style="width:100%;height:100%;background:linear-gradient(135deg,#0f172a,#1e293b);display:flex;align-items:center;justify-content:center;">
                                    <i class="fa-solid fa-film" style="font-size:2.5rem;color:rgba(255,255,255,.2);"></i>
                                </div>
                            @endif
                            @if($file['type'] === 'video')
                                <div class="video-play-icon"><i class="fa-solid fa-circle-play"></i></div>
                            @endif
                            <div class="recent-item-overlay">
                                <div class="recent-item-badge">{{ $file['type'] === 'capture' ? 'Photo' : 'Video' }}</div>
                                <div class="recent-item-name">{{ $file['filename'] }}</div>
                                <div class="recent-item-date">{{ $file['uploaded_at']->diffForHumans() }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>

{{-- Lightbox --}}
<div id="dash-lightbox" onclick="if(event.target===this)dashLbClose()">
    <button id="dash-lb-close" onclick="dashLbClose()">&times;</button>
    <div id="dash-lb-content"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Chart
    const uploadsData = @json(array_values($data['uploads_per_day']));
    const uploadsLabels = @json(array_keys($data['uploads_per_day']));
    const fmtLabels = uploadsLabels.map(d => {
        const dt = new Date(d);
        return dt.toLocaleDateString('en-GB', { day:'numeric', month:'short' });
    });

    new Chart(document.getElementById('uploadsChart'), {
        type: 'line',
        data: {
            labels: fmtLabels,
            datasets: [{
                label: 'Uploads',
                data: uploadsData,
                fill: true,
                tension: 0.4,
                borderColor: '#667eea',
                borderWidth: 2.5,
                backgroundColor: (ctx) => {
                    const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 200);
                    g.addColorStop(0, 'rgba(102,126,234,.35)');
                    g.addColorStop(1, 'rgba(102,126,234,0)');
                    return g;
                },
                pointBackgroundColor: '#667eea',
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleColor: '#94a3b8',
                    bodyColor: '#fff',
                    padding: 10,
                    cornerRadius: 10,
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y} upload${ctx.parsed.y !== 1 ? 's' : ''}`
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#94a3b8', font: { size: 10 } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,.04)' },
                    ticks: { color: '#94a3b8', font: { size: 10 }, stepSize: 1 }
                }
            }
        }
    });

    // Lightbox
    function dashLightbox(type, url, name, date) {
        const c = document.getElementById('dash-lb-content');
        c.innerHTML = type === 'capture'
            ? `<img src="${url}" alt="${name}">`
            : `<video src="${url}" controls autoplay style="max-width:90vw;max-height:85vh;border-radius:12px;"></video>`;
        document.getElementById('dash-lightbox').classList.add('open');
    }
    function dashLbClose() {
        document.getElementById('dash-lightbox').classList.remove('open');
        document.getElementById('dash-lb-content').innerHTML = '';
    }
    document.addEventListener('keydown', e => { if(e.key === 'Escape') dashLbClose(); });
</script>
@endsection
