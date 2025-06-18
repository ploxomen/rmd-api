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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('customer_id')->unsigned();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->longText('order_details')->nullable();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('order_money');
            $table->decimal('order_mount')->nullable();
            $table->decimal('order_total')->nullable();
            $table->decimal('order_mount_igv')->nullable();
            $table->tinyInteger('order_igv');
            $table->tinyInteger('order_status');
            $table->timestamps();
        });
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreign('order_id')->references('id')->on('orders');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign('order_id');
        });
        Schema::dropIfExists('orders');
    }
};
