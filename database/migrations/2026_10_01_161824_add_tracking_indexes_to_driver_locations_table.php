<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('driver_locations', function (Blueprint $table) {
            $table->index(
                ['user_id', 'recorded_at'],
                'driver_locations_user_recorded_index'
            );

            $table->index(
                ['is_active', 'recorded_at'],
                'driver_locations_active_recorded_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_locations', function (Blueprint $table) {
            $table->dropIndex('driver_locations_user_recorded_index');
            $table->dropIndex('driver_locations_active_recorded_index');
        });
    }
};