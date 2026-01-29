<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $tableName = config('contento.table_names.mail_forms');

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email_to')->nullable();
            $table->string('email_cc')->nullable();
            $table->string('email_bcc')->nullable();
            $table->json('custom_fields')->nullable();
            $table->string('redirect_url')->nullable();
            $table->text('custom_data')->nullable();
            $table->text('options')->nullable();
            $table->boolean('newsletter')->default(false);
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(config('contento.table_names.mail_forms'));
    }
};
