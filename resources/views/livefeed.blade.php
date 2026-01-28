<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Live Upload Status</title>

    <!-- Pusher -->
    <script src="https://js.pusher.com/8.2/pusher.min.js"></script>

    <style>
        body {
            font-family: monospace;
            background-color: #0f172a;
            color: #e5e7eb;
            padding: 30px;
        }

        h2 {
            margin-bottom: 20px;
        }

        .log {
            background: #020617;
            border-left: 4px solid #22c55e;
            padding: 12px;
            margin-bottom: 12px;
        }

        .success {
            color: #22c55e;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .label {
            color: #93c5fd;
        }
    </style>
</head>
<body>

<h2>📡 Live Image Upload Status</h2>

<div id="logs">
    <div class="log">
        Waiting for uploads...
    </div>
</div>

<script>
    
    // 🔔 Pusher configuration
    const pusher = new Pusher('10c6dc9fb8040abd25e2', {
        cluster: 'ap1',
        forceTLS: true
    });

    const channel = pusher.subscribe('uploads');

    channel.bind('image.uploaded', function (event) {
        const logs = document.getElementById('logs');

        const div = document.createElement('div');
        div.className = 'log';

        div.innerHTML = `
            <div class="success">✅ Image upload success</div>
            <div><span class="label">ID:</span> ${event.data.id}</div>
            <div><span class="label">URL:</span> ${event.data.image_url}</div>
            <div><span class="label">Time:</span> ${event.data.created_at}</div>
        `;

        logs.prepend(div);
    });
</script>

</body>
</html>
