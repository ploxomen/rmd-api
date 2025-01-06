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
            $table->bigInteger('material_user')->unsigned()->after('material_hist_status');
            $table->foreign('material_user')->references('id')->on('users');
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
            $table->removeColumn('material_user');
        });
    }
};
