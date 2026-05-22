<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            $table->string('group', 80)->index();
            $table->string('key', 120)->unique();

            $table->string('label', 160);
            $table->text('description')->nullable();

            $table->string('type', 40)->default('text');
            $table->longText('value')->nullable();

            $table->boolean('is_public')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
