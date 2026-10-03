<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengajuan {{ $type === 'seller' ? 'Seller' : 'Driver' }} | Daylivery Admin</title>
    <style>
        body{margin:0;background:#f4f6f8;color:#172033;font:15px system-ui,sans-serif}.wrap{max-width:1100px;margin:auto;padding:28px 18px}.top,.columns,.actions{display:flex;gap:12px;align-items:center;justify-content:space-between}.columns{align-items:flex-start;margin-top:20px}.list,.detail{background:#fff;border:1px solid #dfe5e8;border-radius:8px;padding:18px}.list{width:38%}.detail{width:62%}a{color:#176b5b;text-decoration:none}.row{padding:12px 0;border-bottom:1px solid #e8ecee}.muted{color:#647477;font-size:13px}.field{display:grid;gap:7px;margin:14px 0}textarea{min-height:90px}input,textarea,button{font:inherit;padding:9px;border:1px solid #cbd5d7;border-radius:5px}button{cursor:pointer;background:#176b5b;color:#fff;border-color:#176b5b}.reject{background:#fff;color:#a13232;border-color:#d9aaaa}.flash{padding:10px;background:#e3f4ed;margin:12px 0}.error{color:#a13232}.meta{display:grid;grid-template-columns:150px 1fr;gap:9px}.doc{display:inline-block;margin:6px 8px 0 0}@media(max-width:760px){.columns{display:block}.list,.detail{width:auto;margin-bottom:12px}.top{align-items:flex-start;flex-direction:column}}
    </style>
</head>
<body>
<main class="wrap">
    <header class="top">
        <div><h1>Pengajuan {{ $type === 'seller' ? 'Seller' : 'Driver' }}</h1><p class="muted">Tinjau data dan dokumen sebelum mengaktifkan akses mitra.</p></div>
        <nav class="actions"><a href="{{ route('admin.dashboard') }}">Dashboard</a><a href="{{ route('admin.applications.index', 'seller') }}">Seller</a><a href="{{ route('admin.applications.index', 'driver') }}">Driver</a></nav>
    </header>
    @if (session('success'))<p class="flash" role="status">{{ session('success') }}</p>@endif
    @if ($errors->any())<p class="error">{{ $errors->first() }}</p>@endif
    <div class="columns">
        <section class="list" aria-label="Daftar pengajuan">
            @forelse ($applications as $item)
                <div class="row"><a href="{{ route('admin.applications.show', [$type, $item->id]) }}"><strong>{{ $type === 'seller' ? $item->shop_name : $item->full_name }}</strong></a><br><span class="muted">{{ $item->user->name }} · {{ $item->status }} · {{ $item->created_at->format('d/m/Y') }}</span></div>
            @empty<p class="muted">Belum ada pengajuan.</p>@endforelse
            {{ $applications->links() }}
        </section>
        <section class="detail" aria-label="Detail pengajuan">
            @if ($application)
                <h2>{{ $type === 'seller' ? $application->shop_name : $application->full_name }}</h2>
                <div class="meta">
                    <strong>Akun</strong><span>{{ $application->user->name }} · {{ $application->user->email }}</span>
                    @foreach (($type === 'seller' ? ['phone' => 'Telepon', 'shop_address' => 'Alamat toko', 'business_category' => 'Kategori', 'description' => 'Deskripsi'] : ['phone' => 'Telepon', 'address' => 'Alamat', 'identity_number' => 'Nomor identitas', 'license_number' => 'Nomor SIM', 'vehicle_type' => 'Jenis kendaraan', 'vehicle_number' => 'Nomor kendaraan']) as $field => $label)
                        <strong>{{ $label }}</strong><span>{{ $application->{$field} ?: '-' }}</span>
                    @endforeach
                    <strong>Status</strong><span>{{ $application->status }}</span>
                    <strong>Catatan admin</strong><span>{{ $application->admin_note ?: '-' }}</span>
                </div>
                <h3>Dokumen pendukung</h3>
                @forelse ($application->document_paths ?? [] as $index => $path)
                    <a class="doc" target="_blank" rel="noopener" href="{{ route('admin.applications.document', [$type, $application->id, $index]) }}">Dokumen {{ $index + 1 }}</a>
                @empty<p class="muted">Tidak ada dokumen.</p>@endforelse
                @if ($application->status === 'pending')
                    <form method="POST" action="{{ route('admin.applications.review', [$type, $application->id]) }}">
                        @csrf
                        <label class="field">Catatan admin<textarea name="admin_note" maxlength="2000">{{ old('admin_note', $application->admin_note) }}</textarea></label>
                        <div class="actions"><button name="status" value="approved">Setujui</button><button class="reject" name="status" value="rejected">Tolak</button></div>
                    </form>
                @endif
            @else
                <p class="muted">Pilih pengajuan untuk melihat detail.</p>
            @endif
        </section>
    </div>
</main>
</body>
</html>