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
        Schema::create('quotations_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('quotation_id')->unsigned();
            $table->foreign('quotation_id')->references('id')->on('quotations');
            $table->bigInteger('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on('products');
            $table->decimal('detail_price_buy')->nullable();
            $table->longText('quotation_description')->nullable();
            $table->integer('detail_quantity');
            $table->decimal('detail_price_unit');
            $table->decimal('detail_price_additional');
            $table->decimal('detail_total');
            $table->tinyInteger('detail_status');
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
        Schema::dropIfExists('quotations_details');
    }
};
