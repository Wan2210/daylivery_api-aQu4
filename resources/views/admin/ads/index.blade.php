<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sponsor &amp; Iklan | Admin</title>
    <style>
        :root { color-scheme: light; --ink:#192333; --muted:#738096; --line:#e3e8ed; --surface:#fff; --page:#f4f6f8; --green:#13795b; --red:#b42318; }
        * { box-sizing:border-box; }
        body { margin:0; background:var(--page); color:var(--ink); font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif; }
        a { color:inherit; text-decoration:none; }
        .ads-shell { max-width:1320px; margin:0 auto; padding:30px 24px 48px; }
        .ads-heading { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:22px; }
        .ads-heading h1 { margin:0; font-size:1.65rem; }
        .ads-heading p { margin:7px 0 0; color:var(--muted); font-size:.85rem; }
        .back-link { padding:9px 12px; border:1px solid var(--line); border-radius:7px; background:#fff; color:#526176; font-size:.78rem; font-weight:650; }
        .notice { padding:12px 14px; margin-bottom:18px; border:1px solid #b8e1cd; border-radius:7px; background:#eef8f2; color:#176b4e; font-size:.82rem; }
        .ads-layout { display:grid; grid-template-columns:minmax(280px,350px) minmax(0,1fr); gap:18px; align-items:start; }
        .panel { min-width:0; overflow:hidden; border:1px solid var(--line); border-radius:8px; background:var(--surface); }
        .panel-heading { padding:17px 18px; border-bottom:1px solid var(--line); }
        .panel-heading h2 { margin:0; font-size:.98rem; }
        .panel-heading p { margin:5px 0 0; color:var(--muted); font-size:.75rem; }
        .ad-form { padding:18px; }
        .field { margin-bottom:14px; }
        .field label { display:block; margin-bottom:6px; color:#4c596b; font-size:.74rem; font-weight:700; }
        .field input,.field select,.field textarea { width:100%; min-height:40px; padding:9px 10px; border:1px solid #dce2e8; border-radius:6px; background:#fff; color:var(--ink); font:inherit; font-size:.8rem; }
        .field textarea { min-height:78px; resize:vertical; }
        .field input:focus,.field select:focus,.field textarea:focus { border-color:#5b9c80; outline:2px solid #dcefe5; }
        .current-image { display:block; width:100%; max-height:150px; margin:0 0 10px; border-radius:6px; background:#edf0f2; object-fit:cover; }
        .field-error { margin-top:5px; color:var(--red); font-size:.7rem; }
        .form-actions { display:flex; gap:8px; margin-top:18px; }
        .button { min-height:39px; display:inline-flex; align-items:center; justify-content:center; padding:0 13px; border:1px solid var(--green); border-radius:6px; background:var(--green); color:#fff; font-size:.76rem; font-weight:700; cursor:pointer; }
        .button-secondary { border-color:var(--line); background:#fff; color:#536174; }
        .ads-list { padding:0 18px; }
        .ad-row { display:grid; grid-template-columns:100px minmax(0,1fr) auto; gap:14px; align-items:center; padding:15px 0; border-bottom:1px solid #edf0f2; }
        .ad-row:last-child { border-bottom:0; }
        .ad-image { width:100px; aspect-ratio:16/9; border-radius:6px; background:#edf0f2; object-fit:cover; }
        .ad-copy { min-width:0; }
        .ad-copy strong { display:block; overflow-wrap:anywhere; font-size:.84rem; }
        .ad-copy p { margin:5px 0; color:var(--muted); font-size:.73rem; }
        .ad-meta { display:flex; flex-wrap:wrap; gap:6px; color:#667286; font-size:.68rem; }
        .badge { padding:3px 7px; border-radius:4px; background:#eef1f4; }
        .badge-active { background:#e9f6ef; color:#16724f; }
        .ad-actions { display:flex; flex-direction:column; gap:7px; }
        .text-link { color:#176b4e; font-size:.73rem; font-weight:700; }
        .delete-button { padding:0; border:0; background:transparent; color:var(--red); font:inherit; font-size:.73rem; font-weight:700; cursor:pointer; }
        .empty { padding:34px 15px; color:var(--muted); text-align:center; font-size:.8rem; }
        .pagination { padding:15px 18px; }
        @media(max-width:800px) { .ads-layout { grid-template-columns:1fr; } }
        @media(max-width:520px) { .ads-shell { padding:20px 14px 34px; } .ads-heading { align-items:flex-start; flex-direction:column; } .ad-row { grid-template-columns:76px minmax(0,1fr); } .ad-image { width:76px; } .ad-actions { grid-column:2; flex-direction:row; } }
    </style>
</head>
<body>
<main class="ads-shell">
    <header class="ads-heading">
        <div>
            <h1>Sponsor &amp; Iklan</h1>
            <p>Kelola banner sponsor yang ditampilkan di aplikasi.</p>
        </div>
        <a class="back-link" href="{{ route('admin.dashboard') }}">Kembali ke dashboard</a>
    </header>

    @if (session('success'))
        <div class="notice" role="status">{{ session('success') }}</div>
    @endif

    <div class="ads-layout">
        <section class="panel" aria-labelledby="ad-form-title">
            <div class="panel-heading">
                <h2 id="ad-form-title">{{ $editingAd ? 'Edit iklan' : 'Tambah iklan' }}</h2>
                <p>Gambar akan tersedia untuk aplikasi melalui API iklan.</p>
            </div>
            <form class="ad-form" method="POST" enctype="multipart/form-data" action="{{ $editingAd ? route('admin.ads.update', $editingAd) : route('admin.ads.store') }}">
                @csrf
                @if ($editingAd)
                    @method('PUT')
                @endif

                <div class="field">
                    <label for="title">Judul</label>
                    <input id="title" name="title" value="{{ old('title', $editingAd?->title) }}" maxlength="160" required>
                    @error('title')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" maxlength="1000">{{ old('description', $editingAd?->description) }}</textarea>
                    @error('description')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="image">Gambar banner {{ $editingAd ? '(opsional untuk edit)' : '' }}</label>
                    @if ($editingAd)
                        <img class="current-image" src="{{ Storage::disk('public')->url($editingAd->image_path) }}" alt="Banner saat ini">
                    @endif
                    <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" @required(!$editingAd)>
                    @error('image')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="target_url">Tautan tujuan</label>
                    <input id="target_url" name="target_url" type="url" value="{{ old('target_url', $editingAd?->target_url) }}" placeholder="https://">
                    @error('target_url')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="placement">Placement</label>
                    <select id="placement" name="placement" required>
                        @foreach ($placements as $value => $label)
                            <option value="{{ $value }}" @selected(old('placement', $editingAd?->placement ?? 'home_banner') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('placement')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="sort_order">Urutan tampil</label>
                    <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $editingAd?->sort_order ?? 0) }}" required>
                    @error('sort_order')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="is_active">Status</label>
                    <select id="is_active" name="is_active" required>
                        <option value="1" @selected((string) old('is_active', (int) ($editingAd?->is_active ?? true)) === '1')>Aktif</option>
                        <option value="0" @selected((string) old('is_active', (int) ($editingAd?->is_active ?? true)) === '0')>Nonaktif</option>
                    </select>
                    @error('is_active')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-actions">
                    <button class="button" type="submit">{{ $editingAd ? 'Simpan perubahan' : 'Simpan iklan' }}</button>
                    @if ($editingAd)
                        <a class="button button-secondary" href="{{ route('admin.ads.index') }}">Batal</a>
                    @endif
                </div>
            </form>
        </section>

        <section class="panel" aria-labelledby="ad-list-title">
            <div class="panel-heading">
                <h2 id="ad-list-title">Iklan terdaftar</h2>
                <p>{{ $ads->total() }} iklan tersimpan</p>
            </div>
            @forelse ($ads as $ad)
                <article class="ads-list ad-row">
                    <img class="ad-image" src="{{ Storage::disk('public')->url($ad->image_path) }}" alt="Banner {{ $ad->title }}">
                    <div class="ad-copy">
                        <strong>{{ $ad->title }}</strong>
                        <p>{{ $ad->description ?: 'Tanpa deskripsi' }}</p>
                        <div class="ad-meta">
                            <span class="badge">{{ $placements[$ad->placement] ?? $ad->placement }}</span>
                            <span class="badge">Urutan {{ $ad->sort_order }}</span>
                            <span class="badge {{ $ad->is_active ? 'badge-active' : '' }}">{{ $ad->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                    </div>
                    <div class="ad-actions">
                        <a class="text-link" href="{{ route('admin.ads.index', ['edit' => $ad->id]) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.ads.destroy', $ad) }}" onsubmit="return confirm('Arsipkan iklan ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="delete-button" type="submit">Arsipkan</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="empty">Belum ada iklan.</div>
            @endforelse
            @if ($ads->hasPages())
                <div class="pagination">{{ $ads->links() }}</div>
            @endif
        </section>
    </div>
</main>
</body>
</html>