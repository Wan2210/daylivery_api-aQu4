<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b1020">

    <title>Tambah Pesanan — Daylivery</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        :root {
            --primary: #111827;
            --primary-hover: #1f2937;
            --accent: #2563eb;
            --accent-hover: #1d4ed8;
            --bg: #f5f7fb;
            --card: #ffffff;
            --border: #e5e7eb;
            --text: #111827;
            --muted: #6b7280;
            --danger: #dc2626;
            --success: #16a34a;
            --shadow: 0 25px 60px rgba(15, 23, 42, .10);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, .08), transparent 32%),
                radial-gradient(circle at bottom right, rgba(99, 102, 241, .08), transparent 30%),
                var(--bg);
        }

        button,
        input,
        select {
            font: inherit;
        }

        .page-wrapper {
            min-height: 100vh;
            display: flex;
        }

        /* =========================
           SIDEBAR
        ========================== */

        .sidebar {
            width: 250px;
            min-height: 100vh;
            background: linear-gradient(180deg, #0b1020 0%, #111827 100%);
            color: #fff;
            padding: 24px 16px;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 20;
            display: flex;
            flex-direction: column;
        }

        .sidebar-nav-scroll {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
            padding-right: 5px;
            margin-right: -5px;
            scrollbar-width: thin;
            scrollbar-color: rgba(203, 213, 225, .4) transparent;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 4px 10px 28px;
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 18px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            box-shadow: 0 10px 25px rgba(59, 130, 246, .30);
        }

        .brand-name {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -.4px;
        }

        .brand-subtitle {
            margin-top: 2px;
            font-size: 11px;
            color: #94a3b8;
        }

        .nav-title {
            padding: 0 12px 9px;
            font-size: 10px;
            font-weight: 800;
            color: #64748b;
            letter-spacing: .12em;
        }

        .nav {
            display: grid;
            gap: 5px;
        }

        .nav a {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 12px 13px;
            border-radius: 12px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: .2s ease;
        }

        .nav a:hover {
            color: #fff;
            background: rgba(255, 255, 255, .07);
            transform: translateX(2px);
        }

        .nav a.active {
            color: #fff;
            background: linear-gradient(
                135deg,
                rgba(59, 130, 246, .25),
                rgba(99, 102, 241, .20)
            );
            border: 1px solid rgba(96, 165, 250, .18);
        }

        .nav-icon {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        .sidebar-bottom {
            margin-top: auto;
        }

        .support-card {
            padding: 15px;
            border-radius: 16px;
            background: rgba(255, 255, 255, .055);
            border: 1px solid rgba(255, 255, 255, .07);
        }

        .support-title {
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .support-text {
            color: #94a3b8;
            font-size: 11px;
            line-height: 1.5;
        }

        /* =========================
           MAIN
        ========================== */

        .main {
            width: 100%;
            margin-left: 250px;
            min-height: 100vh;
        }

        .topbar {
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 34px;
            background: rgba(255, 255, 255, .78);
            border-bottom: 1px solid rgba(229, 231, 235, .8);
            backdrop-filter: blur(18px);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--muted);
        }

        .breadcrumb a {
            color: var(--muted);
            text-decoration: none;
            transition: .2s ease;
        }

        .breadcrumb a:hover {
            color: var(--accent);
        }

        .breadcrumb strong {
            color: var(--text);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #64748b;
            font-size: 12px;
            font-weight: 600;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, .12);
        }

        .admin-avatar {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 13px;
            background: linear-gradient(135deg, #111827, #374151);
        }

        .content {
            max-width: 1050px;
            margin: 0 auto;
            padding: 38px 34px 60px;
        }

        /* =========================
           HEADER
        ========================== */

        .page-header {
            margin-bottom: 28px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 22px;
            color: #64748b;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: .2s ease;
        }

        .back-link:hover {
            color: var(--accent);
            transform: translateX(-2px);
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 11px;
            border-radius: 999px;
            background: #eff6ff;
            color: #2563eb;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .1em;
        }

        .live-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
        }

        .page-header h1 {
            margin: 15px 0 7px;
            font-size: clamp(28px, 4vw, 38px);
            line-height: 1.1;
            letter-spacing: -.8px;
        }

        .page-header p {
            margin: 0;
            max-width: 650px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
        }

        /* =========================
           FORM LAYOUT
        ========================== */

        .form-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 300px;
            gap: 22px;
            align-items: start;
        }

        .form-card,
        .info-card {
            background: rgba(255, 255, 255, .92);
            border: 1px solid rgba(229, 231, 235, .9);
            border-radius: 22px;
            box-shadow: var(--shadow);
        }

        .form-card {
            padding: 28px;
        }

        .card-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            padding-bottom: 22px;
            margin-bottom: 24px;
            border-bottom: 1px solid #eef0f4;
        }

        .card-heading h2 {
            margin: 0 0 5px;
            font-size: 18px;
            letter-spacing: -.3px;
        }

        .card-heading p {
            margin: 0;
            color: var(--muted);
            font-size: 12px;
        }

        .secure-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 10px;
            border-radius: 9px;
            background: #f0fdf4;
            color: #15803d;
            font-size: 10px;
            font-weight: 800;
            white-space: nowrap;
        }

        .form-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .field {
            display: flex;
            flex-direction: column;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        .field label {
            margin-bottom: 8px;
            font-size: 12px;
            font-weight: 700;
            color: #374151;
        }

        .optional {
            font-weight: 500;
            color: #9ca3af;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 15px;
            pointer-events: none;
        }

        .field input,
        .field select {
            width: 100%;
            height: 48px;
            padding: 0 14px;
            border: 1px solid #dfe3e8;
            border-radius: 12px;
            outline: none;
            background: #fff;
            color: #111827;
            font-size: 13px;
            transition: .2s ease;
        }

        .field input.has-icon {
            padding-left: 40px;
        }

        .field input:hover,
        .field select:hover {
            border-color: #cbd5e1;
        }

        .field input:focus,
        .field select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, .10);
        }

        .field input::placeholder {
            color: #a1a1aa;
        }

        .form-error {
            margin-top: 6px;
            color: var(--danger);
            font-size: 11px;
            font-weight: 600;
        }

        .form-footer {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            padding-top: 25px;
            margin-top: 25px;
            border-top: 1px solid #eef0f4;
        }

        .button {
            min-height: 44px;
            padding: 0 17px;
            border: 0;
            border-radius: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 750;
            text-decoration: none;
            cursor: pointer;
            transition: .2s ease;
        }

        .button-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .button-secondary:hover {
            background: #e5e7eb;
        }

        .button-primary {
            color: #fff;
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            box-shadow: 0 10px 22px rgba(37, 99, 235, .22);
        }

        .button-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 28px rgba(37, 99, 235, .28);
        }

        .button-primary:active {
            transform: translateY(0);
        }

        /* =========================
           INFO CARD
        ========================== */

        .info-card {
            padding: 22px;
        }

        .info-card h3 {
            margin: 0 0 18px;
            font-size: 14px;
        }

        .info-item {
            display: flex;
            gap: 12px;
            margin-bottom: 17px;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-icon {
            flex: 0 0 34px;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            font-size: 14px;
        }

        .info-item strong {
            display: block;
            margin-bottom: 3px;
            font-size: 12px;
        }

        .info-item span {
            display: block;
            color: #64748b;
            font-size: 11px;
            line-height: 1.5;
        }

        .amount-preview {
            margin-top: 22px;
            padding: 15px;
            border-radius: 14px;
            background: linear-gradient(135deg, #eff6ff, #eef2ff);
            border: 1px solid #dbeafe;
        }

        .amount-label {
            color: #64748b;
            font-size: 10px;
            font-weight: 700;
        }

        .amount-value {
            margin-top: 5px;
            font-size: 21px;
            font-weight: 850;
            letter-spacing: -.4px;
            color: #1e3a8a;
        }

        /* =========================
           FOOTER
        ========================== */

        .footer {
            margin-top: 28px;
            text-align: center;
            color: #94a3b8;
            font-size: 11px;
        }

        /* =========================
           RESPONSIVE
        ========================== */

        @media (max-width: 950px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .info-card {
                order: -1;
            }
        }

        @media (max-width: 760px) {
            .sidebar {
                width: 220px;
            }

            .main {
                margin-left: 220px;
            }

            .topbar {
                padding: 0 20px;
            }

            .content {
                padding: 28px 20px 45px;
            }
        }

        @media (max-width: 620px) {
            .sidebar {
                display: none;
            }

            .main {
                margin-left: 0;
            }

            .topbar {
                height: 62px;
            }

            .breadcrumb {
                font-size: 11px;
            }

            .status-indicator {
                display: none;
            }

            .content {
                padding: 24px 15px 40px;
            }

            .page-header {
                margin-bottom: 20px;
            }

            .form-card {
                padding: 20px;
                border-radius: 18px;
            }

            .form-fields {
                grid-template-columns: 1fr;
                gap: 17px;
            }

            .field.full {
                grid-column: auto;
            }

            .card-heading {
                flex-direction: column;
            }

            .secure-badge {
                align-self: flex-start;
            }

            .form-footer {
                flex-direction: column-reverse;
            }

            .form-footer .button {
                width: 100%;
            }

            .info-card {
                border-radius: 18px;
            }
        }
    </style>
</head>

<body>

<div class="page-wrapper" data-page="order-create-page">

    {{-- SIDEBAR --}}
    <aside class="sidebar">

        <div class="brand">
            <div class="brand-icon">D</div>

            <div>
                <div class="brand-name">Daylivery</div>
                <div class="brand-subtitle">Admin Dashboard</div>
            </div>
        </div>

        <div class="sidebar-nav-scroll">
        <div class="nav-title">MENU UTAMA</div>

        <nav class="nav">

            <a href="{{ route('admin.dashboard') }}">
                <span class="nav-icon">⌂</span>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('admin.orders.create') }}" class="active">
                <span class="nav-icon">＋</span>
                <span>Tambah Pesanan</span>
            </a>

            <a href="#">
                <span class="nav-icon">▣</span>
                <span>Pesanan</span>
            </a>

            <a href="#">
                <span class="nav-icon">♙</span>
                <span>Pelanggan</span>
            </a>

            <a href="#">
                <span class="nav-icon">◆</span>
                <span>Mitra</span>
            </a>

        </nav>
        </div>

        <div class="sidebar-bottom">
            <div class="support-card">
                <div class="support-title">Daylivery Admin</div>
                <div class="support-text">
                    Kelola pesanan, pelanggan, dan operasional delivery dalam satu dashboard.
                </div>
            </div>
        </div>

    </aside>


    {{-- MAIN --}}
    <main class="main">

        {{-- TOPBAR --}}
        <header class="topbar">

            <div class="breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <span>/</span>
                <strong>Tambah Pesanan</strong>
            </div>

            <div class="topbar-right">

                <div class="status-indicator">
                    <span class="status-dot"></span>
                    Sistem Online
                </div>

                <div class="admin-avatar">
                    A
                </div>

            </div>

        </header>


        {{-- CONTENT --}}
        <section class="content">

            <div class="page-header">

                <a
                    class="back-link"
                    href="{{ route('admin.dashboard') }}"
                >
                    ← Kembali ke dashboard
                </a>

                <div class="eyebrow">
                    <span class="live-dot"></span>
                    MANAJEMEN PESANAN
                </div>

                <h1>Tambah pesanan</h1>

                <p>
                    Masukkan detail pesanan baru untuk mencatat transaksi
                    dan memantau proses pengiriman melalui sistem Daylivery.
                </p>

            </div>


            <div class="form-grid">

                {{-- FORM --}}
                <div class="form-card">

                    <div class="card-heading">

                        <div>
                            <h2>Detail Pesanan</h2>

                            <p>
                                Lengkapi informasi pelanggan dan pesanan.
                            </p>
                        </div>

                        <div class="secure-badge">
                            ✓ Data aman
                        </div>

                    </div>


                    <form
                        method="POST"
                        action="{{ route('admin.orders.store') }}"
                    >

                        @csrf

                        <div class="form-fields">

                            {{-- CUSTOMER --}}
                            <div class="field">

                                <label for="customer_name">
                                    Nama pelanggan
                                </label>

                                <div class="input-wrap">

                                    <span class="input-icon">♙</span>

                                    <input
                                        id="customer_name"
                                        name="customer_name"
                                        type="text"
                                        value="{{ old('customer_name') }}"
                                        maxlength="120"
                                        autocomplete="name"
                                        placeholder="Contoh: M. Ikhwan"
                                        class="has-icon"
                                        required
                                        autofocus
                                    >

                                </div>

                                @error('customer_name')
                                    <span class="form-error">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </div>


                            {{-- PHONE --}}
                            <div class="field">

                                <label for="customer_phone">
                                    Nomor telepon
                                    <span class="optional">
                                        (opsional)
                                    </span>
                                </label>

                                <div class="input-wrap">

                                    <span class="input-icon">☎</span>

                                    <input
                                        id="customer_phone"
                                        name="customer_phone"
                                        type="tel"
                                        value="{{ old('customer_phone') }}"
                                        maxlength="30"
                                        autocomplete="tel"
                                        placeholder="08xxxxxxxxxx"
                                        class="has-icon"
                                    >

                                </div>

                                @error('customer_phone')
                                    <span class="form-error">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </div>


                            {{-- RESTAURANT --}}
                            <div class="field full">

                                <label for="restaurant_name">
                                    Nama restoran
                                </label>

                                <div class="input-wrap">

                                    <span class="input-icon">▣</span>

                                    <input
                                        id="restaurant_name"
                                        name="restaurant_name"
                                        type="text"
                                        value="{{ old('restaurant_name') }}"
                                        maxlength="150"
                                        placeholder="Contoh: Rumah Makan Padang"
                                        class="has-icon"
                                        required
                                    >

                                </div>

                                @error('restaurant_name')
                                    <span class="form-error">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </div>


                            {{-- TOTAL --}}
                            <div class="field">

                                <label for="total_amount">
                                    Total pembayaran
                                </label>

                                <div class="input-wrap">

                                    <span class="input-icon">
                                        Rp
                                    </span>

                                    <input
                                        id="total_amount"
                                        name="total_amount"
                                        type="number"
                                        value="{{ old('total_amount') }}"
                                        min="0"
                                        max="1000000000"
                                        step="1"
                                        placeholder="0"
                                        class="has-icon"
                                        required
                                    >

                                </div>

                                @error('total_amount')
                                    <span class="form-error">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </div>


                            {{-- STATUS --}}
                            <div class="field">

                                <label for="status">
                                    Status awal
                                </label>

                                <select
                                    id="status"
                                    name="status"
                                    required
                                >

                                    @foreach ($statusOptions as $key => $label)

                                        <option
                                            value="{{ $key }}"
                                            @selected(old('status', 'menunggu') === $key)
                                        >
                                            {{ $label }}
                                        </option>

                                    @endforeach

                                </select>

                                @error('status')
                                    <span class="form-error">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </div>

                        </div>


                        {{-- FORM FOOTER --}}
                        <div class="form-footer">

                            <a
                                href="{{ route('admin.dashboard') }}"
                                class="button button-secondary"
                            >
                                Batal
                            </a>

                            <button
                                class="button button-primary"
                                type="submit"
                            >
                                <span>✓</span>
                                Simpan Pesanan
                            </button>

                        </div>

                    </form>

                </div>


                {{-- INFO --}}
                <aside class="info-card">

                    <h3>Informasi Pesanan</h3>

                    <div class="info-item">

                        <div class="info-icon">♙</div>

                        <div>
                            <strong>Pelanggan</strong>

                            <span>
                                Gunakan nama pelanggan yang sesuai
                                dengan informasi pemesan.
                            </span>
                        </div>

                    </div>


                    <div class="info-item">

                        <div class="info-icon">☎</div>

                        <div>
                            <strong>Nomor Telepon</strong>

                            <span>
                                Nomor digunakan untuk kebutuhan
                                komunikasi dan pengiriman.
                            </span>
                        </div>

                    </div>


                    <div class="info-item">

                        <div class="info-icon">▣</div>

                        <div>
                            <strong>Restoran</strong>

                            <span>
                                Masukkan nama restoran atau tempat
                                pesanan dibuat.
                            </span>
                        </div>

                    </div>


                    <div class="info-item">

                        <div class="info-icon">◷</div>

                        <div>
                            <strong>Status</strong>

                            <span>
                                Status awal dapat diperbarui kembali
                                melalui dashboard.
                            </span>
                        </div>

                    </div>


                    <div class="amount-preview">

                        <div class="amount-label">
                            TOTAL PESANAN
                        </div>

                        <div
                            class="amount-value"
                            id="amountPreview"
                        >
                            Rp 0
                        </div>

                    </div>

                </aside>

            </div>


            <div class="footer">
                © {{ date('Y') }} Daylivery Admin · Sistem Manajemen Delivery
            </div>

        </section>

    </main>

</div>


<script>
    const amountInput = document.getElementById('total_amount');
    const amountPreview = document.getElementById('amountPreview');

    function formatRupiah(value) {
        const number = Number(value || 0);

        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(number);
    }

    function updateAmountPreview() {
        amountPreview.textContent = formatRupiah(amountInput.value);
    }

    amountInput.addEventListener('input', updateAmountPreview);

    updateAmountPreview();
</script>

</body>
</html>