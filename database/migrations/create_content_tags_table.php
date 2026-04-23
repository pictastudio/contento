<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = (string) config('contento.table_names.content_tags', 'content_tags');

        Schema::create($tableName, function (Blueprint $table) use ($tableName): void {
            $table->id();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained($tableName)
                ->nullOnDelete();
            $table->string('path')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('abstract')->nullable();
            $table->longText('description')->nullable();
            $table->json('metadata')->nullable();
            $table->json('images')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('show_in_menu')->default(false);
            $table->boolean('in_evidence')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('visible_from')->nullable()->index();
            $table->timestamp('visible_until')->nullable()->index();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists((string) config('contento.table_names.content_tags', 'content_tags'));
    }
};
