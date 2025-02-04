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
            $table->renameColumn('material_hist_total_buy', 'material_hist_total_buy_pen');
            $table->decimal('material_hist_total_buy', 16, 2)->change();
            $table->decimal('material_hist_igv')->change();
            $table->decimal('material_hist_total_buy_usd',16,2);
            $table->decimal('material_hist_total_type_change');
            $table->tinyInteger('material_hist_total_include_type_change');

        });
        Schema::table('raw_materials', function (Blueprint $table) {
            $table->decimal('raw_material_price_buy', 16, 2)->change();
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
            $table->renameColumn('material_hist_total_buy_pen', 'material_hist_total_buy');
            $table->dropColumn('material_hist_total_buy_usd');
            $table->dropColumn('material_hist_total_type_change');
        });
    }
};
