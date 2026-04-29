<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('contento.table_names.pages');

        if (Schema::hasColumn($tableName, 'metadata')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        $tableName = config('contento.table_names.pages');

        if (!Schema::hasColumn($tableName, 'metadata')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn('metadata');
        });
    }
};
