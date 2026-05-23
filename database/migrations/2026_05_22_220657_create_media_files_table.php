<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_files', function (Blueprint $table) {
            $table->id();

            $table->string('collection', 60);
            $table->string('disk', 60)->default('public');

            $table->string('original_name');
            $table->string('filename');
            $table->string('path');
            $table->string('url');

            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size')->default(0);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('collection');
            $table->index(['collection', 'created_at']);
            $table->index('path');
            $table->index('url');
            $table->unique(['disk', 'path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};
