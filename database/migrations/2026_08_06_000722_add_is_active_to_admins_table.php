<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('admins') &&
            ! Schema::hasColumn('admins', 'is_active')
        ) {
            Schema::table('admins', function (Blueprint $table): void {
                $table
                    ->boolean('is_active')
                    ->default(true)
                    ->index()
                    ->after('role');
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('admins') &&
            Schema::hasColumn('admins', 'is_active')
        ) {
            Schema::table('admins', function (Blueprint $table): void {
                $table->dropIndex(['is_active']);
                $table->dropColumn('is_active');
            });
        }
    }
};
