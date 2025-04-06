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
        Schema::table('raw_materials_history', function (Blueprint $table) {
            $table->bigInteger('raw_provider')->unsigned()->nullable()->after('product_id');
            $table->foreign('raw_provider')->references('id')->on('provider');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('raw_materials_history', function (Blueprint $table) {
            $table->dropForeign('raw_provider');
            $table->dropColumn('raw_provider');

        });
    }
};
