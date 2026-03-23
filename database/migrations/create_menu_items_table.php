<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = (string) config('contento.table_names.menu_items', 'menu_items');
        $menuTable = (string) config('contento.table_names.menus', 'menus');

        Schema::create($tableName, function (Blueprint $table) use ($menuTable, $tableName): void {
            $table->id();
            $table->foreignId('menu_id')
                ->constrained($menuTable)
                ->cascadeOnDelete();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained($tableName)
                ->nullOnDelete();
            $table->string('path')->nullable()->index()->comment('path of the menu item in the tree');
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('link')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('visible_date_from')->nullable();
            $table->timestamp('visible_date_to')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists((string) config('contento.table_names.menu_items', 'menu_items'));
    }
};
