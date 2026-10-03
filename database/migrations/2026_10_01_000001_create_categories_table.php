<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 255)->unique();
            $table->timestamps();
        });

        $now = now();
        $categories = DB::table('menu_items')
            ->whereNotNull('category')
            ->where('category', '<>', '')
            ->distinct()
            ->pluck('category')
            ->map(fn (string $name): array => [
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        if ($categories !== []) {
            DB::table('categories')->insertOrIgnore($categories);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
