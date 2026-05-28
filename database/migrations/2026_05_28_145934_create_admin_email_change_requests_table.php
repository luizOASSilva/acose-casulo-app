<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_email_change_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('target_admin_id')
                ->constrained('admins')
                ->cascadeOnDelete();

            $table->foreignId('requested_by_admin_id')
                ->constrained('admins')
                ->cascadeOnDelete();

            $table->string('old_email');
            $table->string('new_email');

            $table->string('token_hash', 64)->unique();

            $table->timestamp('expires_at');
            $table->timestamp('confirmed_at')->nullable();

            $table->timestamps();

            $table->index(
                ['target_admin_id', 'confirmed_at'],
                'admin_email_change_target_idx'
            );

            $table->index(
                ['requested_by_admin_id', 'confirmed_at'],
                'admin_email_change_requester_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_email_change_requests');
    }
};
