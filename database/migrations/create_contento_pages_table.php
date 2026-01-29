<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $tableName = config('contento.table_names.pages');

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type')->default('page'); // news | page
            $table->boolean('active')->default(true);
            $table->timestamp('visible_date_from')->nullable();
            $table->timestamp('visible_date_to')->nullable();
            $table->boolean('important')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->string('author')->nullable();
            $table->text('abstract')->nullable();
            $table->json('content')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(config('contento.table_names.pages'));
    }
};
