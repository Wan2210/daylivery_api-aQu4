<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'rating')) {
                $table->decimal('rating', 3, 2)->default(0)->after('status');
            }

            if (! Schema::hasColumn('users', 'warning_level')) {
                $table->string('warning_level', 25)->default('normal')->after('rating');
            }

            if (! Schema::hasColumn('users', 'warning_count')) {
                $table->unsignedTinyInteger('warning_count')->default(0)->after('warning_level');
            }

            if (! Schema::hasColumn('users', 'warning_note')) {
                $table->text('warning_note')->nullable()->after('warning_count');
            }

            if (! Schema::hasColumn('users', 'suspended_until')) {
                $table->timestamp('suspended_until')->nullable()->after('warning_note');
            }

            if (! Schema::hasColumn('users', 'is_blocked')) {
                $table->boolean('is_blocked')->default(false)->after('suspended_until');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $columns = ['rating', 'warning_level', 'warning_count', 'warning_note', 'suspended_until', 'is_blocked'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
