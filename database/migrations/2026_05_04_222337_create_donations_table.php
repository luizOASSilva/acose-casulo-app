<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10, 2);

            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('cpf', 14)->nullable();

            $table->string('phone')->nullable();

            $table->string('zip_code')->nullable();
            $table->string('street')->nullable();
            $table->string('number')->nullable();
            $table->string('complement')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();

            $table->string('payment_id')->nullable()->index();
            $table->string('status')->default('pending');
            $table->timestamp('pix_expires_at')->nullable();

            $table->text('pix_copy_paste')->nullable();
            $table->longText('pix_qr_code')->nullable();

            $table->boolean('has_gift')->default(false);
            $table->string('size', 4)->nullable();
            $table->string('gift_status')->nullable();

            $table->timestamps();

            $table->index(['status', 'pix_expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
