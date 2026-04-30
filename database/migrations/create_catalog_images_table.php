<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create((string) config('contento.table_names.catalog_images', 'catalog_images'), function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable()->index();
            $table->string('title')->nullable()->index();
            $table->string('alt')->nullable()->index();
            $table->text('caption')->nullable();
            $table->string('disk');
            $table->string('path');
            $table->string('mime_type')->nullable()->index();
            $table->unsignedBigInteger('size')->nullable()->index();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['disk', 'path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists((string) config('contento.table_names.catalog_images', 'catalog_images'));
    }
};
