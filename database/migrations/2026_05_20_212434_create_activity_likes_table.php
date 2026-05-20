<?php

use App\Models\Activity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_likes', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Activity::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->string('visitor_id', 64);
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->unique(['activity_id', 'visitor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_likes');
    }
};
