<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $galleriesTable = (string) config('contento.table_names.galleries', 'galleries');

        Schema::create((string) config('contento.table_names.gallery_items', 'gallery_items'), function (Blueprint $table) use ($galleriesTable): void {
            $table->id();
            $table->foreignId('gallery_id')
                ->constrained($galleriesTable)
                ->cascadeOnDelete();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->longText('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->boolean('active')->default(true)->index();
            $table->timestamp('visible_from')->nullable()->index();
            $table->timestamp('visible_until')->nullable()->index();
            $table->json('links')->nullable();
            $table->json('img')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();

            $table->index(['gallery_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists((string) config('contento.table_names.gallery_items', 'gallery_items'));
    }
};
