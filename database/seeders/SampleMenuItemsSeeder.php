<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SampleMenuItemsSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::query()
            ->where('role', 'seller')
            ->orderBy('id')
            ->first();

        if (! $seller) {
            $seller = User::query()->create([
                'name' => 'Penjual Demo Daylivery',
                'email' => 'penjual-demo-'.Str::uuid().'@example.test',
                'password' => Str::random(40),
                'role' => 'seller',
                'status' => 'active',
            ]);
        }

        $items = [
            [
                'name' => 'Nasi Goreng Spesial',
                'description' => 'Nasi goreng dengan telur, ayam, dan sayuran.',
                'price' => 28000,
                'stock' => 25,
                'category' => 'makanan',
            ],
            [
                'name' => 'Ayam Geprek',
                'description' => 'Ayam goreng tepung dengan sambal dan nasi.',
                'price' => 25000,
                'stock' => 18,
                'category' => 'makanan',
            ],
            [
                'name' => 'Es Teh Manis',
                'description' => 'Teh manis dingin.',
                'price' => 8000,
                'stock' => 30,
                'category' => 'minuman',
            ],
        ];

        foreach ($items as $item) {
            $seller->menuItems()->updateOrCreate(
                ['name' => $item['name']],
                [...$item, 'is_active' => true],
            );
        }
    }
}
