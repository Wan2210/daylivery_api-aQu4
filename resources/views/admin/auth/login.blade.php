<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="theme-color" content="#080b16">
    <meta name="description" content="Daylivery Admin Console">

    <title>Masuk Admin — Daylivery</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --bg: #070a13;
            --card: rgba(17, 24, 39, .78);
            --card-border: rgba(255, 255, 255, .09);

            --primary: #8b5cf6;
            --primary-2: #6366f1;
            --primary-dark: #6d28d9;

            --text: #f8fafc;
            --muted: #94a3b8;
            --input: rgba(255,255,255,.045);

            --danger: #fb7185;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
        }

        body {
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
                radial-gradient(
                    circle at 15% 20%,
                    rgba(139, 92, 246, .18),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 85% 80%,
                    rgba(99, 102, 241, .14),
                    transparent 30%
                ),
                linear-gradient(
                    135deg,
                    #050711 0%,
                    #090d1a 45%,
                    #070a13 100%
                );

            overflow-x: hidden;
        }

        /* =========================
           BACKGROUND
        ========================= */

        .background {
            position: fixed;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .orb {
            position: absolute;
            border-radius: 999px;
            filter: blur(80px);
            opacity: .25;
            animation: float 10s ease-in-out infinite;
        }

        .orb.one {
            width: 280px;
            height: 280px;
            background: #7c3aed;
            top: -80px;
            left: -70px;
        }

        .orb.two {
            width: 260px;
            height: 260px;
            background: #4f46e5;
            right: -70px;
            bottom: -80px;
            animation-delay: -4s;
        }

        .grid {
            position: absolute;
            inset: 0;

            background-image:
                linear-gradient(
                    rgba(255,255,255,.025) 1px,
                    transparent 1px
                ),
                linear-gradient(
                    90deg,
                    rgba(255,255,255,.025) 1px,
                    transparent 1px
                );

            background-size: 50px 50px;

            mask-image:
                linear-gradient(
                    to bottom,
                    black,
                    transparent 85%
                );
        }

        @keyframes float {
            0%, 100% {
                transform: translate(0, 0);
            }

            50% {
                transform: translate(25px, -20px);
            }
        }

        /* =========================
           PAGE
        ========================= */

        .login-page {
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 30px 18px;

            position: relative;
            z-index: 2;
        }

        /* =========================
           CARD
        ========================= */

        .login-card {
            width: min(100%, 460px);

            position: relative;

            padding: 38px;

            border-radius: 26px;

            background:
                linear-gradient(
                    145deg,
                    rgba(20, 27, 45, .92),
                    rgba(9, 13, 26, .82)
                );

            border: 1px solid var(--card-border);

            box-shadow:
                0 40px 100px rgba(0,0,0,.55),
                0 0 0 1px rgba(255,255,255,.015) inset;

            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);

            animation: cardIn .7s ease forwards;
        }

        .login-card::before {
            content: "";

            position: absolute;
            inset: 0;

            border-radius: inherit;

            background:
                linear-gradient(
                    135deg,
                    rgba(255,255,255,.08),
                    transparent 35%
                );

            pointer-events: none;
        }

        @keyframes cardIn {
            from {
                opacity: 0;
                transform: translateY(20px) scale(.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* =========================
           BRAND
        ========================= */

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;

            margin-bottom: 28px;
        }

        .brand-mark {
            width: 52px;
            height: 52px;

            display: grid;
            place-items: center;

            border-radius: 16px;

            background:
                linear-gradient(
                    135deg,
                    #a78bfa,
                    #7c3aed 55%,
                    #4f46e5
                );

            color: white;

            font-size: 22px;
            font-weight: 900;

            box-shadow:
                0 12px 30px rgba(124,58,237,.35),
                inset 0 1px 1px rgba(255,255,255,.3);

            position: relative;
        }

        .brand-mark::after {
            content: "";

            position: absolute;
            inset: 1px;

            border-radius: 15px;

            border: 1px solid rgba(255,255,255,.15);
        }

        .brand-name {
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: -.02em;
        }

        .brand-label {
            margin-top: 3px;

            color: var(--muted);

            font-size: .78rem;
            font-weight: 500;
        }

        /* =========================
           HEADER
        ========================= */

        .heading {
            margin-bottom: 28px;
        }

        .heading h1 {
            margin: 0 0 9px;

            font-size: clamp(1.75rem, 4vw, 2.1rem);

            letter-spacing: -.04em;

            line-height: 1.15;
        }

        .heading h1 span {
            background:
                linear-gradient(
                    90deg,
                    #c4b5fd,
                    #8b5cf6,
                    #818cf8
                );

            -webkit-background-clip: text;
            background-clip: text;

            color: transparent;
        }

        .heading p {
            margin: 0;

            color: var(--muted);

            line-height: 1.65;
            font-size: .93rem;
        }

        /* =========================
           ALERT
        ========================= */

        .alert {
            display: flex;
            gap: 10px;
            align-items: flex-start;

            padding: 13px 14px;

            margin-bottom: 20px;

            border-radius: 13px;

            background: rgba(244,63,94,.08);

            border: 1px solid rgba(244,63,94,.22);

            color: #fecdd3;

            font-size: .88rem;
            line-height: 1.5;
        }

        .alert-icon {
            flex-shrink: 0;

            width: 20px;
            height: 20px;

            display: grid;
            place-items: center;

            border-radius: 50%;

            background: rgba(244,63,94,.15);

            font-size: 12px;
        }

        /* =========================
           FORM
        ========================= */

        .field {
            margin-bottom: 19px;
        }

        .field-label {
            display: flex;
            justify-content: space-between;
            align-items: center;

            margin-bottom: 8px;
        }

        .field-label label {
            color: #dbe4f3;

            font-size: .87rem;
            font-weight: 600;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;

            transform: translateY(-50%);

            color: #64748b;

            display: flex;
            align-items: center;

            pointer-events: none;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;

            height: 50px;

            border: 1px solid rgba(255,255,255,.08);

            border-radius: 13px;

            background: var(--input);

            color: #f8fafc;

            padding: 0 45px;

            font-size: .94rem;

            transition:
                border-color .2s ease,
                background .2s ease,
                box-shadow .2s ease,
                transform .2s ease;
        }

        input::placeholder {
            color: #475569;
        }

        input:hover {
            background: rgba(255,255,255,.06);
        }

        input:focus {
            outline: none;

            background: rgba(139,92,246,.055);

            border-color: rgba(139,92,246,.75);

            box-shadow:
                0 0 0 4px rgba(139,92,246,.10),
                0 10px 30px rgba(0,0,0,.15);
        }

        .password-toggle {
            position: absolute;

            right: 10px;
            top: 50%;

            transform: translateY(-50%);

            width: 34px;
            height: 34px;

            border: 0;
            border-radius: 9px;

            display: grid;
            place-items: center;

            color: #64748b;

            background: transparent;

            cursor: pointer;

            transition: .2s ease;
        }

        .password-toggle:hover {
            color: #c4b5fd;
            background: rgba(139,92,246,.10);
        }

        /* =========================
           REMEMBER
        ========================= */

        .options {
            display: flex;
            align-items: center;
            justify-content: space-between;

            margin: 3px 0 22px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 9px;

            color: var(--muted);

            font-size: .85rem;

            cursor: pointer;
        }

        .remember input {
            appearance: none;

            width: 17px;
            height: 17px;

            margin: 0;

            border-radius: 5px;

            border: 1px solid rgba(255,255,255,.15);

            background: rgba(255,255,255,.04);

            cursor: pointer;

            position: relative;
        }

        .remember input:checked {
            background: var(--primary);
            border-color: var(--primary);
        }

        .remember input:checked::after {
            content: "✓";

            position: absolute;

            inset: 0;

            display: grid;
            place-items: center;

            color: white;

            font-size: 11px;
            font-weight: 900;
        }

        /* =========================
           BUTTON
        ========================= */

        .btn {
            width: 100%;
            height: 52px;

            border: 0;
            border-radius: 13px;

            position: relative;
            overflow: hidden;

            background:
                linear-gradient(
                    100deg,
                    #7c3aed,
                    #8b5cf6,
                    #6366f1
                );

            color: white;

            font-size: .94rem;
            font-weight: 750;

            cursor: pointer;

            box-shadow:
                0 14px 30px rgba(124,58,237,.25);

            transition:
                transform .2s ease,
                box-shadow .2s ease;
        }

        .btn::before {
            content: "";

            position: absolute;

            top: 0;
            left: -100%;

            width: 60%;
            height: 100%;

            background:
                linear-gradient(
                    90deg,
                    transparent,
                    rgba(255,255,255,.20),
                    transparent
                );

            transform: skewX(-20deg);

            transition: left .6s ease;
        }

        .btn:hover {
            transform: translateY(-2px);

            box-shadow:
                0 18px 38px rgba(124,58,237,.38);
        }

        .btn:hover::before {
            left: 130%;
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;

            position: relative;
            z-index: 2;
        }

        /* =========================
           FOOTER
        ========================= */

        .footer {
            margin-top: 27px;

            padding-top: 20px;

            border-top: 1px solid rgba(255,255,255,.06);

            text-align: center;

            color: #64748b;

            font-size: .78rem;
        }

        .footer strong {
            color: #94a3b8;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 6px;

            margin-top: 8px;
        }

        .status-dot {
            width: 6px;
            height: 6px;

            border-radius: 50%;

            background: #22c55e;

            box-shadow:
                0 0 10px rgba(34,197,94,.7);
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 520px) {
            .login-page {
                padding: 18px;
            }

            .login-card {
                padding: 28px 22px;
                border-radius: 22px;
            }

            .brand-mark {
                width: 46px;
                height: 46px;
                border-radius: 14px;
            }

            .heading h1 {
                font-size: 1.65rem;
            }
        }

        @media (max-width: 360px) {
            .login-card {
                padding: 24px 18px;
            }

            .options {
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>

    <!-- BACKGROUND -->
    <div class="background">
        <div class="orb one"></div>
        <div class="orb two"></div>
        <div class="grid"></div>
    </div>

    <!-- LOGIN -->
    <main class="login-page">

        <section class="login-card">

            <!-- BRAND -->
            <div class="brand">

                <div class="brand-mark">
                    D
                </div>

                <div>
                    <div class="brand-name">
                        Daylivery
                    </div>

                    <div class="brand-label">
                        Admin Console
                    </div>
                </div>

            </div>

            <!-- HEADING -->
            <div class="heading">

                <h1>
                        Selamat datang kembali
                </h1>

                <p>
                    Masuk ke dashboard admin untuk mengelola
                    pesanan, pengiriman, pengguna, dan operasional Daylivery.
                </p>

            </div>

            <!-- ERROR -->
            @if ($errors->any())

                <div class="alert">

                    <div class="alert-icon">
                        !
                    </div>

                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>

                </div>

            @endif

            <!-- FORM -->
            <form method="POST" action="{{ route('admin.login') }}">

                @csrf

                <!-- EMAIL -->
                <div class="field">

                    <div class="field-label">
                        <label for="email">
                                Email admin
                        </label>
                    </div>

                    <div class="input-wrap">

                        <span class="input-icon">
                            <svg width="18" height="18"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <rect x="3" y="5" width="18" height="14"
                                      rx="3"></rect>
                                <path d="m3 7 9 6 9-6"></path>
                            </svg>
                        </span>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="admin@daylivery.com"
                            autocomplete="email"
                            required
                            autofocus
                        >

                    </div>

                </div>

                <!-- PASSWORD -->
                <div class="field">

                    <div class="field-label">
                        <label for="password">
                                Kata sandi
                        </label>
                    </div>

                    <div class="input-wrap">

                        <span class="input-icon">
                            <svg width="18" height="18"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <rect x="4" y="10" width="16" height="10"
                                      rx="2"></rect>
                                <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                                <circle cx="12" cy="15" r="1"></circle>
                            </svg>
                        </span>

                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            data-password-toggle
                            onclick="togglePassword()"
                            aria-label="Tampilkan password"
                        >
                            <svg id="eyeIcon"
                                 width="18"
                                 height="18"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>

                    </div>

                </div>

                <!-- OPTIONS -->
                <div class="options">

                    <label class="remember">

                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                        >

                        <span>
                            Ingat saya
                        </span>

                    </label>

                </div>

                <!-- BUTTON -->
                <button
                    type="submit"
                    class="btn"
                >

                    <span class="btn-content">

                        <span>
                            Masuk ke Dashboard
                        </span>

                        <svg width="18"
                             height="18"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2">
                            <path d="M5 12h14"></path>
                            <path d="m13 6 6 6-6 6"></path>
                        </svg>

                    </span>

                </button>

            </form>

            <!-- FOOTER -->
            <div class="footer">

                <div>
                    © {{ date('Y') }}
                    <strong>Daylivery</strong>.
                    Admin Console.
                </div>

                <div class="status">
                    <span class="status-dot"></span>
                    Sistem Operasional
                </div>

            </div>

        </section>

    </main>

    <script>
        function togglePassword() {

            const password = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');

            if (password.type === 'password') {

                password.type = 'text';

                icon.innerHTML = `
                    <path d="M3 3l18 18"></path>
                    <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
                    <path d="M9.9 4.3A10.8 10.8 0 0 1 12 4c6.5 0 10 8 10 8a17.4 17.4 0 0 1-3.2 4.5"></path>
                    <path d="M6.2 6.2C3.7 8.1 2 12 2 12s3.5 8 10 8a10.7 10.7 0 0 0 4.1-.8"></path>
                `;

            } else {

                password.type = 'password';

                icon.innerHTML = `
                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                `;
            }
        }
    </script>

</body>
</html>
