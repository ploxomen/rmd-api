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
        Schema::create('shopping_details', function (Blueprint $table) {
            $table->id();
            $table->string('shopping_deta_store');
            $table->unsignedBigInteger('shopping_id');
            $table->unsignedBigInteger('shopping_product');
            $table->integer('shopping_deta_ammount');
            $table->decimal('shopping_deta_price',16,2);
            $table->decimal('shopping_deta_subtotal',16,2);
            $table->foreign('shopping_id')->references('id')->on('shopping');
            $table->foreign('shopping_product')->references('id')->on('products');
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
        Schema::dropIfExists('shopping_details');
    }
};
