<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration
{
    public function up(): void
    {
        $this->changeAbstractNullable((string) config('contento.table_names.pages', 'pages'), true);
        $this->changeAbstractNullable((string) config('contento.table_names.faq_categories', 'faq_categories'), true);
    }

    public function down(): void
    {
        $pageTable = (string) config('contento.table_names.pages', 'pages');
        $faqCategoryTable = (string) config('contento.table_names.faq_categories', 'faq_categories');

        $this->replaceNullAbstracts($pageTable);
        $this->replaceNullAbstracts($faqCategoryTable);

        $this->changeAbstractNullable($pageTable, false);
        $this->changeAbstractNullable($faqCategoryTable, false);
    }

    private function changeAbstractNullable(string $tableName, bool $nullable): void
    {
        if (
            !Schema::hasTable($tableName)
            || !Schema::hasColumn($tableName, 'abstract')
            || Schema::getConnection()->getDriverName() === 'sqlite'
        ) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($nullable): void {
            $column = $table->string('abstract');

            if ($nullable) {
                $column->nullable();
            }

            $column->change();
        });
    }

    private function replaceNullAbstracts(string $tableName): void
    {
        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'abstract')) {
            return;
        }

        DB::table($tableName)
            ->whereNull('abstract')
            ->update(['abstract' => '']);
    }
};
