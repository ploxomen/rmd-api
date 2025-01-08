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
        Schema::table('products',function(Blueprint $table){
            $table->dropForeign(['product_substore']);
            $table->dropColumn('product_substore');
            $table->string('product_store')->nullable()->after('product_code');
            $table->string('product_label')->nullable()->after('product_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products',function(Blueprint $table){
            $table->bigInteger('product_substore')->unsigned()->after('product_code');
            $table->foreign('product_substore')->references('id')->on('products');
            $table->dropColumn('product_store')->nullable();
            $table->dropColumn('product_label')->nullable();
        });
    }
};
