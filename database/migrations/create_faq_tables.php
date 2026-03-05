<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $categoryTable = (string) config('contento.table_names.faq_categories', 'faq_categories');
        $faqTable = (string) config('contento.table_names.faqs', 'faqs');

        Schema::create($categoryTable, function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('abstract');
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create($faqTable, function (Blueprint $table) use ($categoryTable): void {
            $table->id();
            $table->foreignId('faq_category_id')
                ->nullable()
                ->constrained($categoryTable)
                ->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('content');
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
        Schema::dropIfExists((string) config('contento.table_names.faqs', 'faqs'));
        Schema::dropIfExists((string) config('contento.table_names.faq_categories', 'faq_categories'));
    }
};
