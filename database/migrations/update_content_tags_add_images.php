<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = (string) config('contento.table_names.content_tags', 'content_tags');

        if (Schema::hasColumn($tableName, 'images')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->json('images')->nullable()->after('metadata');
        });
    }

    public function down(): void
    {
        $tableName = (string) config('contento.table_names.content_tags', 'content_tags');

        if (!Schema::hasColumn($tableName, 'images')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->dropColumn('images');
        });
    }
};
