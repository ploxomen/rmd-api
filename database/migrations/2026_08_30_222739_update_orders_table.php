<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table){
            $table->text('order_details')->after('order_project')->nullable();
        });
        Schema::table('order_productions', function (Blueprint $table){
            $table->text('order_production_detail')->after('order_produc_address')->nullable();
            $table->unsignedBigInteger('order_production_customer')->after('order_produc_address');
            $table->foreign('order_production_customer')->references('id')->on('customers');
        });
    }
    public function down()
    {
        Schema::table('orders', function (Blueprint $table){
            $table->dropColumn('order_details');
        });
        Schema::table('order_productions', function (Blueprint $table){
            $table->dropForeign(['order_production_customer']);
        });
        Schema::table('order_productions', function (Blueprint $table){
            $table->dropColumn('order_production_detail');
            $table->dropColumn('order_production_customer');
        });
    }
};
