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
        Schema::table('shopping_details',function (Blueprint $table){
            $table->string('type_motion')->nullable();
        });
        Schema::table('raw_materials_history',function (Blueprint $table){
            $table->string('type_motion')->nullable();
        });
        Schema::table('commodity_histories',function (Blueprint $table){
            $table->string('type_motion')->nullable();
        });
        Schema::table('product_finaly_assembleds',function (Blueprint $table){
            $table->string('type_motion')->nullable();
        });
        Schema::table('product_progress_history',function (Blueprint $table){
            $table->string('type_motion')->nullable();
        });
        Schema::table('products',function (Blueprint $table){
            $table->dropColumn('stock');
        });
        Schema::table('products',function (Blueprint $table){
            $table->decimal('type_change_initial')->default(0)->nullable();
        });
        Schema::dropIfExists('product_stock_initial');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopping_details',function (Blueprint $table){
            $table->dropColumn('type_motion');
        });
        Schema::table('raw_materials_history',function (Blueprint $table){
            $table->dropColumn('type_motion');
        });
        Schema::table('commodity_histories',function (Blueprint $table){
            $table->dropColumn('type_motion');
        });
        Schema::table('product_finaly_assembleds',function (Blueprint $table){
            $table->dropColumn('type_motion');
        });
        Schema::table('product_progress_history',function (Blueprint $table){
            $table->dropColumn('type_motion');
        });
        Schema::table('products',function (Blueprint $table){
            $table->decimal('stock')->nullable();
        });
        Schema::table('products',function (Blueprint $table){
            $table->dropColumn('type_change_initial');
        });
    }
};
