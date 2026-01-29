<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $tableName = config('contento.table_names.modals');

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->boolean('active')->default(true);
            $table->timestamp('visible_date_from')->nullable();
            $table->timestamp('visible_date_to')->nullable();
            $table->string('template')->default('default');
            $table->longText('content')->nullable();
            $table->string('cta_button_text')->nullable();
            $table->string('cta_button_url')->nullable();
            $table->string('cta_button_color')->nullable();
            $table->string('image')->nullable();
            $table->integer('timeout')->default(0);
            $table->string('popup_time')->default('always');
            $table->boolean('show_on_all_pages')->default(false);
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(config('contento.table_names.modals'));
    }
};
