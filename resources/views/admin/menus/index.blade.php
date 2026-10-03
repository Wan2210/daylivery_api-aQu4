<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Menu Belanja | Admin</title>
    <style>
        .menu-workspace {
            --menu-ink: #172033;
            --menu-muted: #738096;
            --menu-line: #e5eaf0;
            --menu-soft: #f7f9fb;
            --menu-green: #13795b;
            --menu-green-soft: #e8f5ef;
            --menu-orange: #c8782c;
            color: var(--menu-ink);
        }
        .menu-workspace *, .menu-workspace *::before, .menu-workspace *::after { box-sizing: border-box; }
        .menu-shell { max-width: 1450px; margin: 0 auto; padding: 8px 0 28px; }
        .menu-heading { display: flex; justify-content: space-between; align-items: flex-end; gap: 20px; margin-bottom: 24px; }
        .menu-eyebrow { margin-bottom: 8px; color: var(--menu-green); font-size: .66rem; font-weight: 800; letter-spacing: .12em; }
        .menu-heading h1 { margin: 0; font-size: 1.8rem; line-height: 1.15; }
        .menu-heading p { margin: 8px 0 0; color: var(--menu-muted); font-size: .82rem; }
        .menu-notice { padding: 12px 15px; margin-bottom: 18px; border: 1px solid #b9e2cd; border-radius: 10px; background: #edf8f1; color: #176b4e; font-size: .8rem; font-weight: 650; }
        .menu-summary { display: flex; gap: 1px; margin-bottom: 18px; overflow: hidden; border: 1px solid var(--menu-line); border-radius: 12px; background: var(--menu-line); }
        .menu-summary-item { flex: 1; min-width: 0; display: flex; align-items: center; gap: 12px; padding: 15px 18px; background: #fff; }
        .menu-summary-mark { width: 34px; height: 34px; display: grid; place-items: center; flex: 0 0 auto; border-radius: 9px; background: var(--menu-green-soft); color: var(--menu-green); font-size: .72rem; font-weight: 850; }
        .menu-summary-item span { display: block; color: var(--menu-muted); font-size: .68rem; }
        .menu-summary-item strong { display: block; margin-top: 2px; font-size: .95rem; }
        .menu-layout { display: grid; grid-template-columns: minmax(275px, 350px) minmax(0, 1fr); align-items: start; gap: 18px; }
        .menu-panel { min-width: 0; border: 1px solid var(--menu-line); border-radius: 13px; background: #fff; box-shadow: 0 8px 24px rgba(22, 35, 52, .045); }
        .menu-panel-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 18px 19px; border-bottom: 1px solid var(--menu-line); }
        .menu-panel-head h2 { margin: 0; font-size: .96rem; }
        .menu-panel-head p { margin: 5px 0 0; color: var(--menu-muted); font-size: .7rem; line-height: 1.45; }
        .menu-count { padding: 5px 8px; border-radius: 7px; background: var(--menu-soft); color: #59667a; font-size: .67rem; font-weight: 750; white-space: nowrap; }
        .menu-form { padding: 18px 19px 20px; }
        .menu-field { margin-bottom: 15px; }
        .menu-field label { display: block; margin-bottom: 7px; color: #556176; font-size: .72rem; font-weight: 750; }
        .menu-field input, .menu-field select, .menu-field textarea { width: 100%; min-height: 41px; padding: 9px 11px; border: 1px solid #dfe5eb; border-radius: 8px; outline: none; background: #fbfcfd; color: var(--menu-ink); font: inherit; font-size: .78rem; transition: border-color .15s, box-shadow .15s, background .15s; }
        .menu-field textarea { min-height: 88px; resize: vertical; line-height: 1.5; }
        .menu-field input:focus, .menu-field select:focus, .menu-field textarea:focus { border-color: #5c9d81; background: #fff; box-shadow: 0 0 0 3px rgba(19, 121, 91, .1); }
        .menu-field input::placeholder, .menu-field textarea::placeholder { color: #a0a9b5; }
        .menu-price-wrap { position: relative; }
        .menu-price-wrap span { position: absolute; left: 11px; top: 50%; color: var(--menu-muted); font-size: .76rem; transform: translateY(-50%); }
        .menu-price-wrap input { padding-left: 36px; }
        .menu-stock-input { display: flex; align-items: center; }
        .menu-stock-input input { border-radius: 8px 0 0 8px; }
        .menu-stock-unit { min-height: 41px; display: grid; place-items: center; padding: 0 11px; border: 1px solid #dfe5eb; border-left: 0; border-radius: 0 8px 8px 0; background: #f1f4f6; color: var(--menu-muted); font-size: .72rem; }
        .menu-error { margin-top: 5px; color: #b42318; font-size: .68rem; }
        .menu-checkbox { display: flex; align-items: center; gap: 9px; padding: 11px 12px; border: 1px solid var(--menu-line); border-radius: 8px; background: var(--menu-soft); }
        .menu-checkbox input { width: 16px; height: 16px; margin: 0; accent-color: var(--menu-green); }
        .menu-checkbox label { margin: 0; color: #39465a; font-size: .73rem; font-weight: 650; cursor: pointer; }
        .menu-submit { width: 100%; min-height: 42px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; margin-top: 15px; border: 1px solid var(--menu-green); border-radius: 8px; background: var(--menu-green); color: white; font-size: .77rem; font-weight: 750; cursor: pointer; transition: background .15s, transform .15s; }
        .menu-submit:hover { background: #0e684d; transform: translateY(-1px); }
        .menu-list { padding: 4px 19px 14px; }
        .menu-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 16px; min-height: 88px; padding: 14px 0; border-bottom: 1px solid #edf0f3; }
        .menu-row:last-child { border-bottom: 0; }
        .menu-product { display: flex; align-items: center; gap: 13px; min-width: 0; }
        .menu-product-mark { width: 43px; height: 43px; display: grid; place-items: center; flex: 0 0 auto; border: 1px solid #dcebe3; border-radius: 10px; background: linear-gradient(145deg, #eff8f2, #e5f0e9); color: var(--menu-green); font-size: .68rem; font-weight: 850; }
        .menu-product-copy { min-width: 0; }
        .menu-product-copy strong { display: block; overflow-wrap: anywhere; color: #202b3c; font-size: .82rem; }
        .menu-product-copy p { display: -webkit-box; overflow: hidden; margin: 4px 0 6px; color: var(--menu-muted); font-size: .68rem; line-height: 1.45; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
        .menu-product-meta { display: flex; align-items: center; flex-wrap: wrap; gap: 7px; color: #8590a0; font-size: .64rem; }
        .menu-category { padding: 3px 7px; border-radius: 5px; background: #fff3e8; color: var(--menu-orange); font-weight: 750; }
        .menu-sponsored { padding: 3px 7px; border: 1px solid #d8c48a; border-radius: 5px; background: #fff8df; color: #785b12; font-weight: 800; }
        .menu-product-side { display: flex; align-items: flex-end; flex-direction: column; gap: 8px; white-space: nowrap; }
        .menu-price { color: #1e2b3d; font-size: .82rem; font-weight: 800; }
        .menu-sponsor-toggle { padding: 5px 8px; border: 1px solid #dfe5eb; border-radius: 6px; background: #fff; color: #59667a; font: inherit; font-size: .65rem; font-weight: 700; cursor: pointer; }
        .menu-sponsor-toggle:hover { border-color: #c4a94f; color: #785b12; }
        .menu-status { display: inline-flex; align-items: center; gap: 5px; font-size: .64rem; font-weight: 700; }
        .menu-status::before { width: 6px; height: 6px; border-radius: 50%; background: currentColor; content: ''; }
        .menu-status-active { color: var(--menu-green); }
        .menu-status-inactive { color: #7a8492; }
        .menu-empty { padding: 38px 18px; color: var(--menu-muted); text-align: center; font-size: .78rem; }
        .menu-pagination { padding: 0 19px 18px; }
        .menu-pagination nav { display: flex; justify-content: flex-end; }
        @media (max-width: 1050px) { .menu-layout { grid-template-columns: minmax(260px, 320px) minmax(0, 1fr); gap: 13px; } .menu-panel-head, .menu-form, .menu-list { padding-left: 15px; padding-right: 15px; } }
        @media (max-width: 760px) { .menu-shell { padding-top: 0; } .menu-layout { grid-template-columns: 1fr; } .menu-heading { align-items: flex-start; } .menu-summary-item { padding: 12px; } }
        @media (max-width: 520px) { .menu-heading { flex-direction: column; gap: 13px; margin-bottom: 17px; } .menu-heading h1 { font-size: 1.55rem; } .menu-summary { flex-direction: column; } .menu-summary-item { min-height: 62px; } .menu-row { grid-template-columns: minmax(0, 1fr); gap: 10px; } .menu-product-side { flex-direction: row; align-items: center; justify-content: space-between; padding-left: 56px; } }
    </style>
</head>
<body>
    <div class="menu-workspace">
        <div class="menu-shell">
            <header class="menu-heading">
                <div>
                    <div class="menu-eyebrow">KATALOG PRODUK</div>
                    <h1>Menu Belanja</h1>
                    <p>Kelola produk, pemilik, harga, dan ketersediaan dalam satu tempat.</p>
                </div>
            </header>

            @if (session('success'))
                <div class="menu-notice" role="status">{{ session('success') }}</div>
            @endif

            <div class="menu-summary" aria-label="Ringkasan katalog">
                <div class="menu-summary-item">
                    <span class="menu-summary-mark" aria-hidden="true">PR</span>
                    <div><span>Total produk</span><strong>{{ number_format($menuItems->total(), 0, ',', '.') }}</strong></div>
                </div>
                <div class="menu-summary-item">
                    <span class="menu-summary-mark" aria-hidden="true">PJ</span>
                    <div><span>Penjual tersedia</span><strong>{{ number_format($sellerOptions->count(), 0, ',', '.') }}</strong></div>
                </div>
            </div>

            <div class="menu-layout">
                <section class="menu-panel" aria-labelledby="menu-form-title">
                    <div class="menu-panel-head">
                        <div>
                            <h2 id="menu-form-title">Tambah produk</h2>
                            <p>Masukkan detail produk yang akan ditampilkan.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.menus.store') }}" class="menu-form">
                        @csrf

                        <div class="menu-field">
                            <label for="user_id">Pemilik produk</label>
                            <select name="user_id" id="user_id">
                                <option value="">Pilih penjual</option>
                                @foreach ($sellerOptions as $seller)
                                    <option value="{{ $seller->id }}" @selected((string) old('user_id') === (string) $seller->id)>{{ $seller->name }} ({{ ucfirst($seller->role) }})</option>
                                @endforeach
                            </select>
                            @error('user_id')<div class="menu-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="menu-field">
                            <label for="name">Nama produk</label>
                            <input id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Ayam Geprek" maxlength="120" required>
                            @error('name')<div class="menu-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="menu-field">
                            <label for="category">Kategori</label>
                            <input id="category" name="category" value="{{ old('category', 'makanan') }}" placeholder="makanan" maxlength="50" required>
                            @error('category')<div class="menu-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="menu-field">
                            <label for="price">Harga jual</label>
                            <div class="menu-price-wrap"><span>Rp</span><input id="price" name="price" type="number" min="0" step="1" value="{{ old('price') }}" placeholder="25000" required></div>
                            @error('price')<div class="menu-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="menu-field">
                            <label for="stock">Stok tersedia</label>
                            <div class="menu-stock-input"><input id="stock" name="stock" type="number" min="0" step="1" value="{{ old('stock', 0) }}" placeholder="0" required><span class="menu-stock-unit">unit</span></div>
                            @error('stock')<div class="menu-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="menu-field">
                            <label for="description">Deskripsi <span style="font-weight:500;color:#8993a1">(opsional)</span></label>
                            <textarea id="description" name="description" maxlength="1000" placeholder="Bahan, porsi, atau detail singkat produk">{{ old('description') }}</textarea>
                            @error('description')<div class="menu-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="menu-checkbox">
                            <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', true))>
                            <label for="is_active">Tampilkan sebagai produk aktif</label>
                        </div>

                        <button type="submit" class="menu-submit"><span aria-hidden="true">+</span> Simpan produk</button>
                    </form>
                </section>

                <section class="menu-panel" aria-labelledby="menu-list-title">
                    <div class="menu-panel-head">
                        <div>
                            <h2 id="menu-list-title">Produk terdaftar</h2>
                            <p>Produk terbaru beserta penjual dan ketersediaannya.</p>
                        </div>
                        <span class="menu-count">{{ number_format($menuItems->total(), 0, ',', '.') }} produk</span>
                    </div>

                    @if ($menuItems->isEmpty())
                        <div class="menu-empty">Belum ada produk. Tambahkan produk pertama melalui formulir.</div>
                    @else
                        <div class="menu-list">
                            @foreach ($menuItems as $item)
                                <article class="menu-row">
                                    <div class="menu-product">
                                        <span class="menu-product-mark" aria-hidden="true">{{ strtoupper(substr($item->category, 0, 2)) }}</span>
                                        <div class="menu-product-copy">
                                            <strong>{{ $item->name }}</strong>
                                            <p>{{ $item->description ?: 'Tidak ada deskripsi produk.' }}</p>
                                            <div class="menu-product-meta">
                                                <span class="menu-category">{{ ucfirst($item->category) }}</span>
                                                @if ($item->is_sponsored)
                                                    <span class="menu-sponsored">Sponsor</span>
                                                @endif
                                                <span>{{ $item->user?->name ?? 'Admin' }}</span>
                                                <span>Stok: {{ number_format($item->stock, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="menu-product-side">
                                        <span class="menu-price">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                        @if ($item->is_active)
                                            <span class="menu-status menu-status-active">Aktif</span>
                                        @else
                                            <span class="menu-status menu-status-inactive">Nonaktif</span>
                                        @endif
                                        <form method="POST" action="{{ route('admin.menus.sponsor', $item) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="is_sponsored" value="{{ $item->is_sponsored ? 0 : 1 }}">
                                            <button type="submit" class="menu-sponsor-toggle">
                                                {{ $item->is_sponsored ? 'Hapus sponsor' : 'Jadikan sponsor' }}
                                            </button>
                                        </form>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                        @if ($menuItems->hasPages())
                            <div class="menu-pagination">{{ $menuItems->links() }}</div>
                        @endif
                    @endif
                </section>
            </div>
        </div>
    </div>
</body>
</html>
