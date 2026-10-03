<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');

        if (! is_string($email) || $email === '' || ! is_string($password) || strlen($password) < 12 || strlen($password) > 72) {
            throw new InvalidArgumentException('Set ADMIN_EMAIL and ADMIN_PASSWORD (12–72 characters) before seeding the admin account.');
        }

        $admin = User::query()->firstOrNew(['email' => $email]);
        $admin->fill([
            'name' => 'Admin Daylivery',
            'password' => Hash::make($password),
        ]);
        $admin->forceFill([
            'role' => 'admin',
            'status' => 'active',
        ])->save();

        $this->call(SampleMenuItemsSeeder::class);
    }
}
