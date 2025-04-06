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
        Schema::create('module_groups', function (Blueprint $table) {
            $table->id();
            $table->string('module_group_title');
            $table->string('module_group_description')->nullable();
            $table->string('module_group_icon');
            $table->timestamps();
        });
        Schema::table('modules',function(Blueprint $table){
            $table->bigInteger('id_module_group')->unsigned()->nullable()->after('id');
            $table->foreign('id_module_group')->references('id')->on('module_groups');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('modules',function(Blueprint $table){
            $table->dropColumn('id_module_group');
        });
        Schema::dropIfExists('module_groups');
    }
};
