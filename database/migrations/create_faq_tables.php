<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PictaStudio\Contento\Models\FaqCategory;

return new class extends Migration
{
    public function up()
    {
        $categoryTable = config('contento.table_names.faq_categories');
        $faqTable = config('contento.table_names.faqs');

        Schema::create($categoryTable, function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('abstract');
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create($faqTable, function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(FaqCategory::class)->nullable();
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

    public function down()
    {
        Schema::dropIfExists(config('contento.table_names.faqs'));
        Schema::dropIfExists(config('contento.table_names.faq_categories'));
    }
};
