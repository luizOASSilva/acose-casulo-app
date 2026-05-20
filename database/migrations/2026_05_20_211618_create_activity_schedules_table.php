<?php

use App\Models\Activity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Activity::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->string('weekday', 20);
            $table->time('start_time');
            $table->time('end_time');

            $table->timestamps();

            $table->index(['weekday', 'start_time', 'end_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_schedules');
    }
};
