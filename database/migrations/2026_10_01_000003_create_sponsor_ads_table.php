<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsor_ads', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 160);
            $table->text('description')->nullable();
            $table->string('image_path');
            $table->text('target_url')->nullable();
            $table->string('placement', 40)->default('home_banner');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsor_ads');
    }
};
