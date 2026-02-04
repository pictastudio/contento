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
            $table->string('title');
            $table->string('slug')->unique();
            $table->boolean('active')->default(true);
            $table->text('abstract')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create($faqTable, function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(FaqCategory::class)->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->boolean('active')->default(true);
            $table->timestamp('visible_date_from')->nullable();
            $table->timestamp('visible_date_to')->nullable();
            $table->longText('content')->nullable();
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
