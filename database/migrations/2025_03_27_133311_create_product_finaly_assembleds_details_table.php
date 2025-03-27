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
        Schema::create('product_finaly_assem_deta', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_assembled_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('product_finaly_stock');
            $table->string('product_finaly_type');
            $table->foreign('product_assembled_id')->references('id')->on('product_finaly_assembleds');
            $table->foreign('product_id')->references('id')->on('products');
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
        Schema::dropIfExists('product_finaly_assembleds_details');
    }
};
