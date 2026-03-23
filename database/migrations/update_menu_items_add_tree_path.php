<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = (string) config('contento.table_names.menu_items', 'menu_items');

        if (Schema::hasColumn($tableName, 'path')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->string('path')->nullable()->index()->comment('path of the menu item in the tree')->after('parent_id');
        });
    }

    public function down(): void
    {
        $tableName = (string) config('contento.table_names.menu_items', 'menu_items');

        if (!Schema::hasColumn($tableName, 'path')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->dropColumn('path');
        });
    }
};
