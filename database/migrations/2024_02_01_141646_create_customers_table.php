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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('customer_type_document')->unsigned()->nullable();
            $table->foreign('customer_type_document')->references('id')->on('type_documents');
            $table->bigInteger('customer_contrie')->unsigned();
            $table->foreign('customer_contrie')->references('id')->on('contries');
            $table->string('customer_number_document',30)->nullable();
            $table->string('customer_name',250);
            $table->string('customer_email',250)->nullable();
            $table->string('customer_phone',250)->nullable();
            $table->string('customer_cell_phone',250)->nullable();
            $table->bigInteger('customer_district')->unsigned()->nullable();
            $table->foreign('customer_district')->references('id')->on('districts');
            $table->string('customer_address',500)->nullable();
            $table->tinyInteger('customer_status');
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
        Schema::dropIfExists('customers');
    }
};
