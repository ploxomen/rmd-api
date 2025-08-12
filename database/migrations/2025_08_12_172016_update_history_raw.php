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
        Schema::table('raw_materials', function (Blueprint $table) {
            $table->decimal('raw_hist_bala_amou');
            $table->decimal('raw_hist_bala_cost',16,2);
            $table->decimal('raw_hist_prom_weig',16,2);
        });
        Schema::table('raw_materials_history', function (Blueprint $table) {
            $table->string('raw_hist_type');
            $table->decimal('raw_hist_bala_amou');
            $table->decimal('raw_hist_bala_cost',16,2);
            $table->decimal('raw_hist_prom_weig',16,2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('raw_materials', function (Blueprint $table) {
            $table->dropColumn('raw_hist_bala_amou');
            $table->dropColumn('raw_hist_bala_cost');
            $table->dropColumn('raw_hist_prom_weig');
        });
        Schema::table('raw_materials_history', function (Blueprint $table) {
            $table->dropColumn('raw_hist_type');
            $table->dropColumn('raw_hist_bala_amou');
            $table->dropColumn('raw_hist_bala_cost');
            $table->dropColumn('raw_hist_prom_weig');
        });
    }
};
