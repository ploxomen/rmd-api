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
        Schema::create('product_finaly_imported', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_finaly_id');
            $table->unsignedBigInteger('product_finaly_provider');
            $table->string('product_finaly_money');
            $table->date('product_finaly_created');
            $table->string('product_finaly_hist_bill');
            $table->string('product_finaly_hist_guide')->nullable();
            $table->string('product_finaly_type_change');
            $table->integer('product_finaly_amount');
            $table->decimal('product_finaly_price_buy',14,2);
            $table->decimal('product_finaly_total_buy',14,2);
            $table->unsignedBigInteger('product_finaly_user');
            $table->foreign('product_finaly_id')->references('id')->on('product_finalies');
            $table->foreign('product_finaly_provider')->references('id')->on('provider');
            $table->foreign('product_finaly_user')->references('id')->on('users');
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
        Schema::dropIfExists('product_finaly_imported');
    }
};
