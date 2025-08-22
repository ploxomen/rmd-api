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
        Schema::table('raw_materials_history',function(Blueprint $table){
            $table->unsignedBigInteger('shopping_detail_id')->nullable()->after('guide_refer_id');
            $table->foreign('shopping_detail_id')->references('id')->on('shopping_details');
        });
        Schema::table('commodity_histories',function(Blueprint $table){
            $table->unsignedBigInteger('shopping_detail_id')->nullable()->after('guide_refer_id');
            $table->foreign('shopping_detail_id')->references('id')->on('shopping_details');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('raw_materials_history',function(Blueprint $table){
            $table->dropForeign(['shopping_detail_id']);
        });
        Schema::table('commodity_histories',function(Blueprint $table){
            $table->dropForeign(['shopping_detail_id']);
        });
        Schema::table('raw_materials_history',function(Blueprint $table){
            $table->dropColumn(['shopping_detail_id']);
        });
        Schema::table('commodity_histories',function(Blueprint $table){
            $table->dropColumn(['shopping_detail_id']);
        });
    }
};
