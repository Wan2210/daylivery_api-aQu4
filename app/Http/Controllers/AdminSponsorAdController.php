<?php

namespace App\Http\Controllers;

use App\Models\SponsorAd;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminSponsorAdController extends Controller
{
    private const PLACEMENTS = [
        'home_banner' => 'Banner beranda',
        'category_banner' => 'Banner kategori',
    ];

    public function index(Request $request): View
    {
        $editingAd = $request->filled('edit')
            ? SponsorAd::query()->findOrFail($request->integer('edit'))
            : null;

        return view('admin.ads.index', [
            'ads' => SponsorAd::query()->latest()->paginate(12),
            'editingAd' => $editingAd,
            'placements' => self::PLACEMENTS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['image_path'] = $request->file('image')->store('sponsor-ads', 'public');
        $data['is_active'] = $request->boolean('is_active');

        SponsorAd::query()->create($data);

        return redirect()->route('admin.ads.index')->with('success', 'Iklan berhasil ditambahkan.');
    }

    public function update(Request $request, SponsorAd $sponsorAd): RedirectResponse
    {
        $data = $request->validate($this->rules(false));
        $oldImagePath = $sponsorAd->image_path;

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('sponsor-ads', 'public');
        }

        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        $sponsorAd->update($data);

        if (isset($data['image_path']) && $oldImagePath !== $data['image_path']) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return redirect()->route('admin.ads.index')->with('success', 'Iklan berhasil diperbarui.');
    }

    public function destroy(SponsorAd $sponsorAd): RedirectResponse
    {
        $sponsorAd->delete();

        return redirect()->route('admin.ads.index')->with('success', 'Iklan diarsipkan.');
    }

    private function rules(bool $creating = true): array
    {
        $required = $creating ? 'required' : 'sometimes';

        return [
            'title' => [$required, 'string', 'max:160'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'image' => [$creating ? 'required' : 'sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'target_url' => ['sometimes', 'nullable', 'url:http,https', 'max:2048'],
            'placement' => [$required, 'in:'.implode(',', array_keys(self::PLACEMENTS))],
            'sort_order' => [$required, 'integer', 'min:0', 'max:100000'],
            'is_active' => [$required, 'boolean'],
        ];
    }
}
