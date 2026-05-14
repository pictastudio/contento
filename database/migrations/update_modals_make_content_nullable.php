<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration
{
    public function up(): void
    {
        $this->changeContentNullable(true);
    }

    public function down(): void
    {
        $tableName = (string) config('contento.table_names.modals', 'modals');

        if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'content')) {
            DB::table($tableName)
                ->whereNull('content')
                ->update(['content' => '']);
        }

        $this->changeContentNullable(false);
    }

    private function changeContentNullable(bool $nullable): void
    {
        $tableName = (string) config('contento.table_names.modals', 'modals');

        if (
            !Schema::hasTable($tableName)
            || !Schema::hasColumn($tableName, 'content')
            || Schema::getConnection()->getDriverName() === 'sqlite'
        ) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($nullable): void {
            $column = $table->text('content');

            if ($nullable) {
                $column->nullable();
            }

            $column->change();
        });
    }
};
