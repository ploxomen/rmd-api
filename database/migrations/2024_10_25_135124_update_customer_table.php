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
        Schema::table('customers', function (Blueprint $table) {
            $table->bigInteger('customer_contrie')->unsigned();
            $table->foreign('customer_contrie')->references('id')->on('contries');
        });
        Schema::table('orders',function(Blueprint $table){
            $table->string('order_retaining_customer',2)->after('customer_id');
        });
        Schema::table('customers',function(Blueprint $table){
            $table->string('customer_retaining',2)->after('customer_contrie');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign('customer_contrie');
            $table->dropColumn('customer_contrie');
        });
        Schema::table('orders',function(Blueprint $table){
            $table->dropColumn('order_retaining_customer');
        });
        Schema::table('customers',function(Blueprint $table){
            $table->dropColumn('customer_retaining',2);
        });
    }
};
