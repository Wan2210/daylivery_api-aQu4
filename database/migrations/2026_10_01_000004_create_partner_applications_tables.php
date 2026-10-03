<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('shop_name', 150);
            $table->string('phone', 30);
            $table->text('shop_address');
            $table->string('business_category', 100);
            $table->text('description')->nullable();
            $table->json('document_paths')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->text('admin_note')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('driver_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('full_name', 150);
            $table->string('phone', 30);
            $table->text('address');
            $table->string('identity_number', 50)->unique();
            $table->string('license_number', 50);
            $table->string('vehicle_type', 80);
            $table->string('vehicle_number', 30);
            $table->json('document_paths')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->text('admin_note')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('seller_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->text('seller_note')->nullable();
            $table->index(['seller_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['seller_id', 'status']);
            $table->dropConstrainedForeignId('seller_id');
            $table->dropColumn(['address', 'notes', 'seller_note']);
        });

        Schema::dropIfExists('driver_applications');
        Schema::dropIfExists('seller_applications');
    }
};