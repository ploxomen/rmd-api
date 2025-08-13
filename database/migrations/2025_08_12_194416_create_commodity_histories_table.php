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
        Schema::create('commodity_histories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('commodi_id')->unsigned();
            $table->bigInteger('product_id')->unsigned();
            $table->string('commodi_hist_bill');
            $table->string('commodi_hist_guide')->nullable();
            $table->integer('commodi_hist_amount');
            $table->decimal('commodi_hist_price_buy',16,2);
            $table->text('commodi_hist_money');
            $table->decimal('commodi_hist_total_buy',16,2);
            $table->decimal('commodi_hist_total_buy_usd',16,2);
            $table->string('commodi_hist_type');
            $table->decimal('commodi_hist_bala_amou');
            $table->decimal('commodi_hist_bala_cost',16,2);
            $table->decimal('commodi_hist_prom_weig',16,2);
            $table->tinyInteger('commodi_hist_status')->default(1);
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('commodi_id')->references('id')->on('commodities');
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
        Schema::dropIfExists('commodity_histories');
    }
};
