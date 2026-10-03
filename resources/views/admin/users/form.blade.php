<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $mode === 'create' ? 'Tambah Mitra' : 'Edit Mitra' }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        :root { --bg:#f4f7fb; --card:#fff; --line:#e7edf6; --text:#182333; --muted:#697d96; --primary:#635bff; --danger:#de5b5b; --warning:#c88b15; --success:#159a65; }
        *{box-sizing:border-box} body{margin:0;font-family:Inter,system-ui,-apple-system,sans-serif;background:linear-gradient(180deg,#f8faff,#edf3fb);color:var(--text)}
        a{text-decoration:none;color:inherit}.wrap{max-width:920px;margin:0 auto;padding:32px 18px 50px}.top{display:flex;justify-content:space-between;align-items:center;gap:14px;margin-bottom:24px}.top h1{margin:0;font-size:clamp(1.7rem,2.8vw,2.4rem);letter-spacing:-.04em}.btn{border:1px solid var(--line);background:#fff;border-radius:12px;padding:10px 16px;font-weight:700;cursor:pointer}.btn-primary{background:var(--primary);border-color:var(--primary);color:#fff;box-shadow:0 12px 25px rgba(99,91,255,.2)}.card{background:var(--card);border:1px solid var(--line);border-radius:18px;box-shadow:0 18px 40px rgba(16,24,40,.06)}.form{padding:22px}.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.field{display:flex;flex-direction:column;gap:8px}.field.full{grid-column:1/-1}.field label{font-size:.74rem;font-weight:700;color:var(--muted)}.field input,.field select,.field textarea{width:100%;height:44px;border:1px solid var(--line);background:#fafbfd;border-radius:10px;padding:0 12px;color:var(--text);font:inherit}.field textarea{min-height:120px;padding:12px}.field input:focus,.field select:focus,.field textarea:focus{outline:none;border-color:rgba(99,91,255,.45);box-shadow:0 0 0 4px rgba(99,91,255,.08)}.hint{color:var(--muted);font-size:.73rem}.errors{margin-bottom:16px;padding:12px 14px;border-radius:12px;border:1px solid #f3d6d6;background:#fff5f5;color:#a63d3d;font-size:.78rem}.actions{display:flex;gap:12px;justify-content:flex-end;margin-top:22px}.status-box{padding:16px 18px;background:#fafbff;border:1px solid var(--line);border-radius:14px}.status-box h3{margin:0 0 10px;font-size:1rem}.status-box p{margin:0;color:var(--muted);font-size:.75rem;line-height:1.5} @media (max-width:620px){ .grid{grid-template-columns:1fr}. .top{flex-direction:column;align-items:flex-start}. .actions{flex-direction:column-reverse}. .actions .btn{width:100%} }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="top">
            <h1>{{ $mode === 'create' ? 'Tambah Mitra Baru' : 'Edit Data Mitra' }}</h1>
            <a href="{{ route('admin.users.index') }}" class="btn">Kembali</a>
        </div>

        @if ($errors->any())
            <div class="errors">
                <ul style="margin:0;padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $mode === 'create' ? route('admin.users.store') : route('admin.users.update', $user) }}" class="card form">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <div class="grid">
                <div class="field">
                    <label for="name">Nama lengkap</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="field">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        @foreach ($roleOptions as $key => $label)
                            <option value="{{ $key }}" @selected(old('role', $user->role) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                </div>

                <div class="field">
                    <label for="phone">Nomor telepon</label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}">
                </div>

                @if ($mode === 'create')
                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" required>
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Konfirmasi password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required>
                    </div>
                @else
                    <div class="field">
                        <label for="password">Password baru (opsional)</label>
                        <input id="password" type="password" name="password">
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Konfirmasi password baru</label>
                        <input id="password_confirmation" type="password" name="password_confirmation">
                    </div>
                @endif

                <div class="field">
                    <label for="status">Status akun</label>
                    <select id="status" name="status" required>
                        @foreach ($statusOptions as $key => $label)
                            <option value="{{ $key }}" @selected(old('status', $user->status) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="rating">Rating</label>
                    <input id="rating" type="number" min="0" max="5" step="0.1" name="rating" value="{{ old('rating', (float) $user->rating) }}">
                </div>

                <div class="field full">
                    <label for="warning_level">Peringatan / tindakan akun</label>
                    <select id="warning_level" name="warning_level">
                        @foreach ($warningOptions as $key => $label)
                            <option value="{{ $key }}" @selected(old('warning_level', $user->warning_level ?? 'normal') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field full">
                    <label for="warning_note">Catatan admin</label>
                    <textarea id="warning_note" name="warning_note" placeholder="Contoh: rating rendah karena keterlambatan dan komplain pelanggan">{{ old('warning_note', $user->warning_note) }}</textarea>
                </div>
            </div>

            <div class="status-box" style="margin-top:20px;">
                <h3>Status akun</h3>
                <p>
                    Status yang dipilih berlaku langsung. Rating dan level peringatan dicatat terpisah dan tidak menimpa status akun.
                </p>
            </div>

            <div class="actions">
                <a href="{{ route('admin.users.index') }}" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary">{{ $mode === 'create' ? 'Simpan mitra' : 'Update mitra' }}</button>
            </div>
        </form>
    </div>
</body>
</html>
