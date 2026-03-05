<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = (string) config('contento.table_names.content_taggables', 'content_taggables');
        $contentTagsTable = (string) config('contento.table_names.content_tags', 'content_tags');

        Schema::create($tableName, function (Blueprint $table) use ($contentTagsTable): void {
            $table->id();
            $table->foreignId('content_tag_id')
                ->constrained($contentTagsTable)
                ->cascadeOnDelete();
            $table->morphs('taggable');
            $table->timestamps();

            $table->unique([
                'content_tag_id',
                'taggable_type',
                'taggable_id',
            ], 'content_taggables_unique_association');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists((string) config('contento.table_names.content_taggables', 'content_taggables'));
    }
};
