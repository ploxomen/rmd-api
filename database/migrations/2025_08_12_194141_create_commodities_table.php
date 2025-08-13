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
        Schema::create('commodities', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id')->unsigned();
            $table->integer('commodi_stock');
            $table->text('commodi_money')->nullable();
            $table->decimal('commodi_price_buy');
            $table->decimal('commodi_bala_amou');
            $table->decimal('commodi_bala_cost',16,2);
            $table->decimal('commodi_prom_weig',16,2);
            $table->tinyInteger('commodi_status')->default(1);
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
        Schema::dropIfExists('commodities');
    }
};
