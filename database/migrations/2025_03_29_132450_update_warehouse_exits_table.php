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
            $table->dropColumn('material_hist_status');
        });
        Schema::table('product_progress_history', function (Blueprint $table) {
            $table->dropColumn('product_progress_history_status');
        });
        Schema::table('quotations_details', function (Blueprint $table) {
            $table->dropColumn('detail_status');
        });
        Schema::table('raw_materials_history', function (Blueprint $table) {
            $table->unsignedBigInteger('product_final_assem_id')->nullable()->after('raw_provider');
            $table->unsignedBigInteger('quotation_detail_id')->nullable()->after('raw_provider');
            $table->unsignedBigInteger('product_progres_hist_id')->nullable()->after('raw_provider');
            $table->foreign('product_final_assem_id')->references('id')->on('product_finaly_assem_deta');
            $table->foreign('quotation_detail_id')->references('id')->on('quotations_details');
            $table->foreign('product_progres_hist_id')->references('id')->on('product_progress_history');
        });
        Schema::table('product_progress_history', function (Blueprint $table) {
            $table->unsignedBigInteger('product_final_assem_id')->nullable()->after('product_progress_id');
            $table->foreign('product_final_assem_id')->references('id')->on('product_finaly_assem_deta');
        });
        Schema::table('product_finaly_assembleds', function (Blueprint $table) {
            $table->unsignedBigInteger('quotation_detail_id')->nullable()->after('product_finaly_id');
            $table->foreign( 'quotation_detail_id')->references('id')->on('quotations_details');
        });
        Schema::table('product_finaly_imported', function (Blueprint $table) {
            $table->unsignedBigInteger('quotation_detail_id')->nullable()->after('product_finaly_id');
            $table->foreign('quotation_detail_id')->references('id')->on('quotations_details');
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
            $table->tinyInteger('material_hist_status')->default(1);
        });
        Schema::table('product_progress_history', function (Blueprint $table) {
            $table->tinyInteger('product_progress_history_status')->default(1);
        });
        Schema::table('quotations_details', function (Blueprint $table) {
            $table->tinyInteger('detail_status')->default(1);
        });
        Schema::table('raw_materials_history', function (Blueprint $table) {
            $table->dropForeign(['product_final_assem_id','quotation_detail_id','product_progres_hist_id']);
            $table->dropColumn('product_final_assem_id');
            $table->dropColumn('quotation_detail_id');
            $table->dropColumn('product_progres_hist_id');
        });
        Schema::table('product_progress_history', function (Blueprint $table) {
            $table->dropForeign(['product_final_assem_id']);
            $table->dropColumn('product_final_assem_id');
        });
        Schema::table('product_finaly_assembleds', function (Blueprint $table) {
            $table->dropForeign(['quotation_detail_id']);
            $table->dropColumn('quotation_detail_id');
        });
        Schema::table('product_finaly_imported', function (Blueprint $table) {
            $table->dropForeign(['quotation_detail_id']);
            $table->dropColumn('quotation_detail_id');
        });
    }
};
