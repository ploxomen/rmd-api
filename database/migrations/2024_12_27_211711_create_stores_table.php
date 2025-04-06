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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('store_name');
            $table->text('store_description')->nullable();
            $table->tinyInteger('store_status')->default(1);
            $table->timestamps();
        });
        Schema::create('stores_sub', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('store_id')->unsigned();
            $table->string('store_sub_name');
            $table->tinyInteger('store_sub_status')->default(1);
            $table->timestamps();
            $table->foreign('store_id')->references('id')->on('stores');
        });
        Schema::table('products',function(Blueprint $table){
            $table->bigInteger('product_substore')->nullable()->unsigned()->after('product_code');
            $table->text('product_unit_measurement')->after('product_name');
            $table->foreign('product_substore')->references('id')->on('stores_sub');
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
            $table->dropColumn('product_substore');
        });
        Schema::dropIfExists('stores_sub');
        Schema::dropIfExists('stores');
    }
};
