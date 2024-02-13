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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_type_document')->unsigned()->nullable();
            $table->foreign('user_type_document')->references('id')->on('type_documents');
            $table->string('user_number_document',30)->nullable();
            $table->string('user_name',250);
            $table->string('user_last_name',250);
            $table->string('user_email',250)->unique();
            $table->string('password');
            $table->string('user_phone',20)->nullable();
            $table->string('user_cell_phone',20)->nullable();
            $table->date('user_birthdate')->nullable();
            $table->string('user_address',250)->nullable();
            $table->string('user_gender',2)->nullable();
            $table->tinyInteger('user_status');
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
        Schema::dropIfExists('users');
    }
};
