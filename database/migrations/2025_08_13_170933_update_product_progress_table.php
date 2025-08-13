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
        Schema::table('product_progress', function (Blueprint $table) {
            $table->decimal('prod_prog_bala_amou')->after('product_progress_status');
            $table->decimal('prod_prog_bala_cost',16,2)->after('product_progress_status');
            $table->decimal('prod_prog_prom_weig',16,2)->after('product_progress_status');
        });
        Schema::table('product_progress_history', function (Blueprint $table) {
            $table->string('prod_prog_hist_type')->after('product_progress_history_description');
            $table->decimal('product_progress_history_total',16,2)->after('product_progress_history_description');
            $table->decimal('product_progress_history_pu',16,2)->after('product_progress_history_description');
            $table->decimal('prod_prog_hist_bala_amou')->after('product_progress_history_description');
            $table->decimal('prod_prog_hist_bala_cost',16,2)->after('product_progress_history_description');
            $table->decimal('prod_prog_hist_prom_weig',16,2)->after('product_progress_history_description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_progress', function (Blueprint $table) {
            $table->dropColumn('prod_prog_bala_amou');
            $table->dropColumn('prod_prog_bala_cost');
            $table->dropColumn('prod_prog_prom_weig');
        });
        Schema::table('product_progress_history', function (Blueprint $table) {
            $table->dropColumn('prod_prog_hist_type');
            $table->dropColumn('product_progress_history_pu');
            $table->dropColumn('product_progress_history_total');
            $table->dropColumn('prod_prog_hist_bala_amou');
            $table->dropColumn('prod_prog_hist_bala_cost');
            $table->dropColumn('prod_prog_hist_prom_weig');
        });
    }
};
