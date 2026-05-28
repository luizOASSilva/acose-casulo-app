<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_creation_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('requested_by_admin_id')
                ->constrained('admins')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('email');
            $table->string('role')->default('admin');
            $table->boolean('is_active')->default(true);

            $table->string('token_hash', 64)->unique();

            $table->timestamp('expires_at');
            $table->timestamp('confirmed_at')->nullable();

            $table->timestamps();

            $table->index(['email', 'confirmed_at']);
            $table->index(['requested_by_admin_id', 'confirmed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_creation_requests');
    }
};
