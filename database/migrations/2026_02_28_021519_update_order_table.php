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
        Schema::table('orders',function(Blueprint $table){
            $table->date('order_date_issue')->after('order_retaining_customer')->nullable();
        });
        Schema::table('quotations',function(Blueprint $table){
            $table->string('quotation_code')->after('quotation_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders',function(Blueprint $table){
            $table->dropColumn('order_date_issue');
        });
        Schema::table('quotations',function(Blueprint $table){
            $table->dropColumn('quotation_code');
        });
    }
};
