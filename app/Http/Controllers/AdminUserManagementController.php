<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class AdminUserManagementController extends Controller
{
    public const ROLE_OPTIONS = [
        'customer' => 'Pelanggan',
        'driver' => 'Driver',
        'seller' => 'Penjual / Restoran',
    ];

    public const STATUS_OPTIONS = [
        'active' => 'Aktif',
        'suspended' => 'Ditangguhkan',
        'blocked' => 'Diblokir',
    ];

    public const WARNING_OPTIONS = [
        'normal' => 'Normal',
        'warning' => 'Peringatan',
        'suspended' => 'Suspensi sementara',
        'blocked' => 'Blokir akun',
    ];

    public function index(Request $request): View
    {
        $query = User::query()
            ->with(['sellerApplication:id,user_id,status', 'driverApplication:id,user_id,status'])
            ->whereIn('role', array_keys(self::ROLE_OPTIONS))
            ->orderByDesc('created_at');

        if ($request->filled('role') && array_key_exists($request->role, self::ROLE_OPTIONS)) {
            $role = $request->role;
            $query->where(function ($builder) use ($role): void {
                $builder->where('role', $role);

                if ($role === 'seller') {
                    $builder->orWhereHas('sellerApplication', fn ($application) => $application->where('status', 'approved'));
                } elseif ($role === 'driver') {
                    $builder->orWhereHas('driverApplication', fn ($application) => $application->where('status', 'approved'));
                }
            });
        }

        if ($request->filled('status') && array_key_exists($request->status, self::STATUS_OPTIONS)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->appends($request->query());

        return view('admin.users.index', [
            'users' => $users,
            'roleOptions' => self::ROLE_OPTIONS,
            'statusOptions' => self::STATUS_OPTIONS,
            'selectedRole' => (string) $request->query('role', ''),
            'selectedStatus' => (string) $request->query('status', ''),
            'search' => (string) $request->query('q', ''),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User,
            'mode' => 'create',
            'roleOptions' => self::ROLE_OPTIONS,
            'statusOptions' => self::STATUS_OPTIONS,
            'warningOptions' => self::WARNING_OPTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in(array_keys(self::ROLE_OPTIONS))],
            'status' => ['required', 'string', Rule::in(array_keys(self::STATUS_OPTIONS))],
            'rating' => ['nullable', 'numeric', 'between:0,5'],
            'warning_level' => ['nullable', 'string', Rule::in(array_keys(self::WARNING_OPTIONS))],
            'warning_note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($data['role'] === 'customer' && User::customerLimitReached()) {
            return back()->withErrors([
                'role' => 'Jumlah pelanggan sudah mencapai batas 2.000 akun.',
            ])->withInput();
        }

        if ($data['role'] === 'driver' && User::driverLimitReached()) {
            return back()->withErrors([
                'role' => 'Jumlah driver sudah mencapai batas 15 akun.',
            ])->withInput();
        }

        $data['password'] = Hash::make($data['password']);
        $data['warning_level'] = $data['warning_level'] ?? 'normal';
        $data['warning_count'] = $data['warning_level'] === 'normal' ? 0 : 1;
        $data['is_blocked'] = $data['status'] === 'blocked';

        User::query()->create($data);

        return redirect()->route('admin.users.index')->with('success', 'Akun mitra berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'user' => $user,
            'mode' => 'edit',
            'roleOptions' => self::ROLE_OPTIONS,
            'statusOptions' => self::STATUS_OPTIONS,
            'warningOptions' => self::WARNING_OPTIONS,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'string', Rule::in(array_keys(self::ROLE_OPTIONS))],
            'status' => ['required', 'string', Rule::in(array_keys(self::STATUS_OPTIONS))],
            'rating' => ['nullable', 'numeric', 'between:0,5'],
            'warning_level' => ['nullable', 'string', Rule::in(array_keys(self::WARNING_OPTIONS))],
            'warning_note' => ['nullable', 'string', 'max:500'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($data['role'] === 'customer' && $user->role !== 'customer' && User::customerLimitReached()) {
            return back()->withErrors([
                'role' => 'Jumlah pelanggan sudah mencapai batas 2.000 akun.',
            ])->withInput();
        }

        if ($data['role'] === 'driver' && $user->role !== 'driver' && User::driverLimitReached()) {
            return back()->withErrors([
                'role' => 'Jumlah driver sudah mencapai batas 15 akun.',
            ])->withInput();
        }

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'role' => $data['role'],
            'status' => $data['status'],
            'rating' => (float) ($data['rating'] ?? 0),
            'warning_level' => $data['warning_level'] ?? 'normal',
            'warning_note' => $data['warning_note'],
            'is_blocked' => $data['status'] === 'blocked',
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Data akun mitra berhasil diperbarui.');
    }
}
