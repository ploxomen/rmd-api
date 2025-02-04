<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('provider_type_document')->unsigned()->nullable();
            $table->foreign('provider_type_document')->references('id')->on('type_documents');
            $table->bigInteger('provider_contrie')->unsigned();
            $table->foreign('provider_contrie')->references('id')->on('contries');
            $table->string('provider_number_document',30)->nullable();
            $table->string('provider_name',250);
            $table->string('provider_email',250)->nullable();
            $table->string('provider_phone',250)->nullable();
            $table->string('provider_cell_phone',250)->nullable();
            $table->bigInteger('provider_district')->unsigned()->nullable();
            $table->foreign('provider_district')->references('id')->on('districts');
            $table->bigInteger('user_create')->unsigned()->nullable();
            $table->foreign('user_create')->references('id')->on('users');
            $table->string('provider_address',500)->nullable();
            $table->tinyInteger('provider_status');
            $table->timestamps();
        });
        Schema::create('provider_contacts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('provider_id')->unsigned();
            $table->foreign('provider_id')->references('id')->on('provider');
            $table->string("provider_name",250);
            $table->string("provider_position",250)->nullable();
            $table->string("provider_number",30)->nullable();
            $table->string("provider_email",30)->nullable();
            $table->tinyInteger('provider_status')->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider');
        Schema::dropIfExists('provider_contacts');

    }
};
