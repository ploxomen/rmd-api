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
        Schema::create('product_progress', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id')->unsigned();
            $table->integer('product_progress_stock');
            $table->tinyInteger('product_progress_status')->default(1);
            $table->foreign('product_id')->references('id')->on('products');
            $table->timestamps();
        });
        Schema::create('product_progress_history', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id')->unsigned();
            $table->bigInteger('product_progress_id')->unsigned();
            $table->date('product_progress_history_date');
            $table->integer('product_progress_history_stock');
            $table->text('product_progress_history_description')->nullable();
            $table->tinyInteger('product_progress_history_status')->default(1);
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('product_progress_id')->references('id')->on('product_progress');
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
        Schema::dropIfExists('product_progress');
    }
};
