<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $tableName = config('contento.table_names.settings');

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('group')->index();
            $table->string('name')->index();
            $table->longText('value')->nullable();
            $table->timestamps();

            $table->unique(['group', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists(config('contento.table_names.settings'));
    }
};
