<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kategori Produk | Daylivery Admin</title>
    <style>
        body{margin:0;background:#f3f6f5;color:#172b2b;font:15px system-ui,sans-serif}.wrap{max-width:950px;margin:auto;padding:28px 18px}header,.actions,form{display:flex;gap:10px;align-items:center;justify-content:space-between}header{margin-bottom:22px}a{color:#176b5b;text-decoration:none}.panel{background:#fff;border:1px solid #dce5e1;border-radius:8px;padding:18px;margin-bottom:16px}.field{flex:1;display:flex;gap:8px;align-items:center}input,button{font:inherit;padding:9px 11px;border:1px solid #cbd8d1;border-radius:5px}input{min-width:0;flex:1}button{background:#176b5b;color:#fff;border-color:#176b5b;cursor:pointer}.delete{background:#fff;color:#9c3434;border-color:#ddb7b7}.row{display:flex;gap:12px;align-items:center;padding:11px 0;border-bottom:1px solid #e8eeeb}.row:last-child{border-bottom:0}.row form{flex:1}.muted{color:#62736c;font-size:13px}.flash{padding:10px;background:#e3f4ed;color:#176b5b}.error{color:#a13232}@media(max-width:600px){header,.row,form{align-items:stretch;flex-direction:column}.row form{width:100%}.actions{flex-wrap:wrap}}
    </style>
</head>
<body>
<main class="wrap">
    <header><div><h1>Kategori Produk</h1><p class="muted">Kelola kategori katalog Daylivery.</p></div><nav class="actions"><a href="{{ route('admin.dashboard') }}">Dashboard</a><a href="{{ route('admin.menus.index') }}">Produk</a></nav></header>
    @if (session('success'))<p class="panel flash" role="status">{{ session('success') }}</p>@endif
    @if ($errors->any())<p class="panel error">{{ $errors->first() }}</p>@endif
    <section class="panel">
        <h2>Tambah kategori</h2>
        <form method="POST" action="{{ route('admin.categories.store') }}">@csrf<label class="field"><span>Nama</span><input name="name" value="{{ old('name') }}" maxlength="255" required></label><button type="submit">Tambah</button></form>
    </section>
    <section class="panel" aria-label="Daftar kategori">
        @forelse ($categories as $category)
            <div class="row">
                <form method="POST" action="{{ route('admin.categories.update', $category) }}">@csrf @method('PUT')<label class="field"><input aria-label="Nama kategori" name="name" value="{{ $category->name }}" maxlength="255" required><span class="muted">{{ $category->menu_items_count }} produk</span></label><button type="submit">Simpan</button></form>
                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini?')">@csrf @method('DELETE')<button class="delete" type="submit" @disabled($category->menu_items_count > 0)>Hapus</button></form>
            </div>
        @empty<p class="muted">Belum ada kategori.</p>@endforelse
        {{ $categories->links() }}
    </section>
</main>
</body>
</html>