<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('menu_items', 'stock')) {
            Schema::table('menu_items', function (Blueprint $table): void {
                $table->unsignedInteger('stock')->default(0)->after('price');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('menu_items', 'stock')) {
            Schema::table('menu_items', function (Blueprint $table): void {
                $table->dropColumn('stock');
            });
        }
    }
};
