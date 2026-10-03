<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Mitra Admin</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        :root {
            --bg:#f3f6fb; --card:#fff; --line:#e6ebf3; --text:#182333; --muted:#72809a; --primary:#635bff; --success:#1fa975; --warning:#c98a00; --danger:#db5d5d; --shadow:0 15px 40px rgba(20,30,50,.08);
        }
        * { box-sizing:border-box; }
        body { margin:0; font-family:Inter,system-ui,-apple-system,sans-serif; background:linear-gradient(180deg,#f6f8ff,#eef3fb); color:var(--text); }
        a { color:inherit; text-decoration:none; }
        .wrap { max-width:1280px; margin:0 auto; padding:32px 20px 50px; }
        .topbar { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:24px; }
        .title h1 { margin:0; font-size:clamp(1.7rem,3vw,2.4rem); letter-spacing:-.04em; }
        .title p { margin:6px 0 0; color:var(--muted); }
        .actions { display:flex; gap:10px; flex-wrap:wrap; }
        .btn { border:1px solid var(--line); border-radius:12px; padding:10px 16px; background:#fff; font-weight:700; font-size:.82rem; cursor:pointer; }
        .btn-primary { background:var(--primary); border-color:var(--primary); color:#fff; box-shadow:0 12px 25px rgba(99,91,255,.25); }
        .card { background:var(--card); border:1px solid var(--line); border-radius:18px; box-shadow:var(--shadow); }
        .toolbar { padding:20px; display:grid; grid-template-columns:1.3fr 1fr 1fr auto; gap:12px; align-items:end; }
        .field { display:flex; flex-direction:column; gap:8px; }
        .field label { font-size:.72rem; color:var(--muted); font-weight:700; }
        .field input, .field select { height:42px; border:1px solid var(--line); border-radius:10px; background:#fafbff; padding:0 12px; color:var(--text); }
        .summary { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; margin:20px 0; }
        .stat { padding:18px 20px; }
        .stat small { color:var(--muted); font-weight:700; text-transform:uppercase; letter-spacing:.08em; }
        .stat strong { display:block; margin-top:10px; font-size:1.6rem; letter-spacing:-.04em; }
        .table-wrap { overflow:auto; }
        table { width:100%; min-width:980px; border-collapse:collapse; }
        thead th { background:#fafbfd; color:#7f8ca1; font-size:.7rem; letter-spacing:.05em; text-transform:uppercase; text-align:left; padding:14px 18px; border-bottom:1px solid var(--line); }
        tbody td { padding:14px 18px; border-bottom:1px solid #edf1f6; color:#435065; font-size:.8rem; }
        tbody tr:hover { background:#fafbff; }
        .badge { display:inline-flex; align-items:center; padding:6px 10px; border-radius:999px; font-size:.65rem; font-weight:800; }
        .badge-customer { background:#eef7ff; color:#2365d5; }
        .badge-driver { background:#eef2ff; color:#4f57d8; }
        .badge-seller { background:#eefaf5; color:#188b5b; }
        .badge-active { background:#e9faf2; color:#16855a; }
        .badge-suspended { background:#fff4de; color:#ae7600; }
        .badge-blocked { background:#ffecea; color:#d35555; }
        .badge-warning { background:#fff1db; color:#bd7800; }
        .badge-normal { background:#edf0f5; color:#626e83; }
        .rating { font-weight:800; color:#1b2434; }
        .table-actions { display:flex; gap:8px; flex-wrap:wrap; }
        .mini-btn { border:1px solid var(--line); border-radius:10px; padding:7px 10px; background:#fff; font-size:.72rem; font-weight:700; }
        .empty { padding:40px 20px; text-align:center; color:var(--muted); }
        .flash { margin-bottom:18px; background:#ecfff6; border:1px solid #cfeee0; color:#0c7e54; border-radius:12px; padding:12px 14px; font-weight:700; }
        @media (max-width:820px) {
            .topbar { flex-direction:column; align-items:flex-start; }
            .summary { grid-template-columns:repeat(2,minmax(0,1fr)); }
            .toolbar { grid-template-columns:1fr; }
        }
        @media (max-width:520px) {
            .summary { grid-template-columns:1fr; }
            .wrap { padding:20px 14px 40px; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="topbar">
            <div class="title">
                <h1>Kelola Mitra</h1>
                <p>Driver dan penjual yang terdaftar di sistem</p>
            </div>
            <div class="actions">
                <a href="{{ route('admin.dashboard') }}" class="btn">Kembali ke dashboard</a>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">+ Tambah mitra</a>
            </div>
        </div>

        @if (session('success'))
            <div class="flash" role="status">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('admin.users.index') }}" class="card toolbar">
            <div class="field">
                <label for="search">Cari mitra</label>
                <input id="search" type="search" name="q" value="{{ $search }}" placeholder="Nama, email, telepon">
            </div>
            <div class="field">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="">Semua role</option>
                    @foreach ($roleOptions as $key => $label)
                        <option value="{{ $key }}" @selected($selectedRole === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="status">Status akun</label>
                <select id="status" name="status">
                    <option value="">Semua status</option>
                    @foreach ($statusOptions as $key => $label)
                        <option value="{{ $key }}" @selected($selectedStatus === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <button type="submit" class="btn btn-primary">Terapkan</button>
            </div>
        </form>

        @php
            $customerCount = $users->total() ? $users->where('role', 'customer')->count() : 0;
            $driverCount = $users->total() ? $users->where('role', 'driver')->count() : 0;
            $sellerCount = $users->total() ? $users->where('role', 'seller')->count() : 0;
            $activeCount = $users->total() ? $users->where('status', 'active')->count() : 0;
            $warningCount = $users->total() ? $users->whereIn('status', ['suspended', 'blocked'])->count() : 0;
        @endphp

        <section class="summary">
            <div class="card stat">
                <small>Total mitra</small>
                <strong>{{ $users->total() }}</strong>
            </div>
            <div class="card stat">
                <small>Pelanggan</small>
                <strong>{{ $customerCount }}</strong>
            </div>
            <div class="card stat">
                <small>Driver</small>
                <strong>{{ $driverCount }}</strong>
            </div>
            <div class="card stat">
                <small>Penjual / Restoran</small>
                <strong>{{ $sellerCount }}</strong>
            </div>
        </section>

        <div class="card table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Role</th>
                        <th>Kontak</th>
                        <th>Rating</th>
                        <th>Warning</th>
                        <th>Status</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <strong>{{ $user->name }}</strong><br>
                                <small>{{ $user->email }}</small>
                            </td>
                            <td>
                                @php
                                    $roleLabels = [match ($user->role) {
                                        'customer' => 'Pelanggan',
                                        'driver' => 'Driver',
                                        'seller' => 'Penjual',
                                        default => ucfirst((string) $user->role),
                                    }];
                                    if ($user->sellerApplication?->status === 'approved') $roleLabels[] = 'Seller';
                                    if ($user->driverApplication?->status === 'approved') $roleLabels[] = 'Driver';
                                    $roleLabels = array_unique($roleLabels);
                                @endphp
                                @foreach ($roleLabels as $roleLabel)
                                    <span class="badge badge-{{ $roleLabel === 'Pelanggan' ? 'customer' : ($roleLabel === 'Driver' ? 'driver' : 'seller') }}">{{ $roleLabel }}</span>
                                @endforeach
                            </td>
                            <td>
                                <div>{{ $user->phone ?? '-' }}</div>
                                <small>{{ $user->created_at->format('d/m/Y') }}</small>
                            </td>
                            <td><span class="rating">{{ number_format((float) $user->rating, 2) }}</span></td>
                            <td>
                                @php $warningLevel = $user->warning_level ?? 'normal'; @endphp
                                <span class="badge badge-{{ str_replace(['warning','suspended','blocked'], ['warning','suspended','blocked'], $warningLevel) ?? 'normal' }}">{{ ucfirst($warningLevel) }}</span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $user->status }}">{{ $user->status === 'active' ? 'Aktif' : ($user->status === 'suspended' ? 'Ditangguhkan' : 'Diblokir') }}</span>
                            </td>
                            <td>{{ $user->warning_note ?: '-' }}</td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="mini-btn">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty">Belum ada mitra yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div style="margin-top:18px; display:flex; justify-content:center;">{{ $users->links() }}</div>
        @endif
    </div>
</body>
</html>
