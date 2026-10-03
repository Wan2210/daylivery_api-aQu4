<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminMenuController extends Controller
{
    public function index(): View
    {
        $menuItems = MenuItem::query()->with('user')->latest()->paginate(10);

        return view('admin.menus.index', [
            'menuItems' => $menuItems,
            'sellerOptions' => User::query()->where('status', 'active')->where(function ($query): void {
                $query->where('role', 'seller')
                    ->orWhereHas('sellerApplication', fn ($application) => $application->where('status', 'approved'));
            })->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.menus.index', [
            'menuItems' => MenuItem::query()->with('user')->latest()->paginate(10),
            'sellerOptions' => User::query()->where('status', 'active')->where(function ($query): void {
                $query->where('role', 'seller')
                    ->orWhereHas('sellerApplication', fn ($application) => $application->where('status', 'approved'));
            })->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'category' => ['required', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $seller = User::query()->findOrFail($validated['user_id']);
        abort_unless($seller->status === 'active' && $seller->hasSellerAccess(), 422, 'Akun tersebut belum memiliki akses seller aktif.');

        $validated['is_active'] = $request->boolean('is_active');

        MenuItem::query()->create($validated);

        return redirect()->route('admin.menus.index')->with('success', 'Menu belanja berhasil ditambahkan.');
    }

    public function updateSponsor(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $data = $request->validate([
            'is_sponsored' => ['required', 'boolean'],
        ]);

        $menuItem->update($data);

        $message = $menuItem->is_sponsored
            ? 'Menu ditandai sebagai sponsor.'
            : 'Status sponsor menu dinonaktifkan.';

        return redirect()->route('admin.menus.index')->with('success', $message);
    }
}
