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
        Schema::create('raw_materials', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id')->unsigned()->unique();
            $table->integer('raw_material_stock');
            $table->text('raw_material_money');
            $table->decimal('raw_material_price_buy');
            $table->tinyInteger('raw_material_status')->default(1);
            $table->foreign('product_id')->references('id')->on('products');
            $table->timestamps();
        });
        Schema::create('raw_materials_history', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('raw_material_id')->unsigned();
            $table->bigInteger('product_id')->unsigned();
            $table->string('material_hist_bill');
            $table->string('material_hist_guide')->nullable();
            $table->integer('material_hist_amount');
            $table->decimal('material_hist_price_buy');
            $table->text('material_hist_igv');
            $table->text('material_hist_money');
            $table->decimal('material_hist_total_buy');
            $table->tinyInteger('material_hist_status')->default(1);
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('raw_material_id')->references('id')->on('raw_materials');
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
        Schema::dropIfExists('raw_materials');
        Schema::dropIfExists('raw_materials_history');
    }
};
