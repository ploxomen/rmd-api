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
            $table->date('material_hist_date')->nullable()->after('product_id');
            $table->dropColumn('material_hist_total_include_type_change');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->text('product_label_2')->nullable();
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
            $table->dropColumn('material_hist_date');
            $table->tinyInteger('material_hist_total_include_type_change')->nullable();
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('product_label_2');
        });
    }
};
