<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengaturan Admin</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        :root {
            --bg:#f4f7fb; --card:#fff; --line:#e7edf6; --text:#182333; --muted:#697d96; --primary:#635bff; --success:#159a65; --warning:#c88b15;
        }
        * { box-sizing: border-box; }
        body { margin:0; font-family:Inter,system-ui,-apple-system,sans-serif; background:linear-gradient(180deg,#f8faff,#edf3fb); color:var(--text); }
        a { text-decoration:none; color:inherit; }
        .wrap { max-width:960px; margin:0 auto; padding:32px 18px 50px; }
        .top { display:flex; justify-content:space-between; align-items:center; gap:14px; margin-bottom:20px; }
        .top h1 { margin:0; font-size:clamp(1.8rem,3vw,2.3rem); letter-spacing:-.04em; }
        .card { background:var(--card); border:1px solid var(--line); border-radius:18px; box-shadow:0 18px 40px rgba(16,24,40,.06); }
        .panel { padding:22px; }
        .grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:16px; }
        .info { padding:18px 20px; border:1px solid var(--line); border-radius:14px; background:#fafbfd; }
        .info small { display:block; color:var(--muted); margin-bottom:8px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; }
        .info strong { font-size:1.1rem; }
        .alert { margin-bottom:18px; border:1px solid #dbe8fb; background:#eff6ff; color:#1d4ed8; padding:12px 14px; border-radius:12px; font-weight:700; }
        .btn { border:1px solid var(--line); background:#fff; border-radius:12px; padding:10px 16px; font-weight:700; cursor:pointer; }
        .btn-primary { background:var(--primary); border-color:var(--primary); color:#fff; }
        @media (max-width:700px) { .grid { grid-template-columns:1fr; } .top { flex-direction:column; align-items:flex-start; } }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="top">
            <h1>Pengaturan</h1>
            <a href="{{ route('admin.dashboard') }}" class="btn">Kembali ke dashboard</a>
        </div>

        <div class="alert">
            Konfigurasi aplikasi dan akun admin saat ini sedang aktif dan siap dipakai.
        </div>

        <div class="card panel">
            <div class="grid">
                <div class="info">
                    <small>Nama aplikasi</small>
                    <strong>{{ $appName }}</strong>
                </div>
                <div class="info">
                    <small>Environment</small>
                    <strong>{{ $appEnv }}</strong>
                </div>
                <div class="info">
                    <small>Database</small>
                    <strong>{{ $dbConnection }}</strong>
                </div>
                <div class="info">
                    <small>Database aktif</small>
                    <strong>{{ $dbDatabase ?: '-' }}</strong>
                </div>
                <div class="info">
                    <small>Email admin</small>
                    <strong>{{ $adminEmail ?: '-' }}</strong>
                </div>
                <div class="info">
                    <small>Status password admin</small>
                    <strong>{{ $adminPasswordConfigured ? 'Sudah dikonfigurasi' : 'Belum dikonfigurasi' }}</strong>
                </div>
                <div class="info" style="grid-column:1 / -1;">
                    <small>Terakhir diperbarui</small>
                    <strong>{{ $updatedAt }}</strong>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
