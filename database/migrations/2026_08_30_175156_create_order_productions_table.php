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
        Schema::create('order_productions', function (Blueprint $table) {
            $table->id();
            $table->text('order_production_code');
            $table->integer('order_production_number');
            $table->date('order_produc_date_issue');
            $table->date('order_produc_date_delive')->nullable();
            $table->text('order_produc_address')->nullable();
            $table->decimal('order_produc_total', 10, 2)->nullable();
            $table->timestamps();
        });
        Schema::create('order_productions_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_production_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('quotation_detail_id');
            $table->unsignedBigInteger('product_label_id')->nullable();
            $table->integer('amount');
            $table->decimal('product_label_hr', 10, 2)->nullable();
            $table->decimal('product_label_total', 10, 2)->nullable();
            $table->timestamps();
            $table->foreign('order_production_id')->references('id')->on('order_productions');
            $table->foreign('quotation_detail_id')->references('id')->on('quotations_details');
            $table->foreign('order_id')->references('id')->on('orders');
            $table->foreign('product_label_id')->references('id')->on('product_labels');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_productions_details', function (Blueprint $table){
            $table->dropForeign(['order_production_id']);
        });
        Schema::dropIfExists('order_productions');
        Schema::dropIfExists('order_productions_details');
    }
};
