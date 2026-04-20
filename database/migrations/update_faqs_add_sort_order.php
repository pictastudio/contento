<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = (string) config('contento.table_names.faqs', 'faqs');

        if (Schema::hasColumn($tableName, 'sort_order')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->unsignedSmallInteger('sort_order')->default(0)->after('active');
        });
    }

    public function down(): void
    {
        $tableName = (string) config('contento.table_names.faqs', 'faqs');

        if (!Schema::hasColumn($tableName, 'sort_order')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->dropColumn('sort_order');
        });
    }
};
