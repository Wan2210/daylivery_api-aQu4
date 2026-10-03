<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('menu_items', 'is_sponsored')) {
            Schema::table('menu_items', function (Blueprint $table): void {
                $table->boolean('is_sponsored')->default(false)->after('is_active');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('menu_items', 'is_sponsored')) {
            Schema::table('menu_items', function (Blueprint $table): void {
                $table->dropColumn('is_sponsored');
            });
        }
    }
};
