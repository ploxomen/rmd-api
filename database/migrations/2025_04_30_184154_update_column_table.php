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
            $table->dropForeign(['quotation_detail_id']);
            $table->dropColumn('quotation_detail_id');
        });
        Schema::table('product_finaly_assembleds', function (Blueprint $table) {
            $table->dropForeign(['quotation_detail_id']);
            $table->dropColumn('quotation_detail_id');
        });
        Schema::table('product_finaly_imported', function (Blueprint $table) {
            $table->dropForeign(['quotation_detail_id']);
            $table->dropColumn('quotation_detail_id');
        });
        Schema::table('raw_materials_history', function (Blueprint $table) {
            $table->unsignedBigInteger('guide_refer_id')->nullable()->after('product_progres_hist_id');
            $table->foreign('guide_refer_id')->references('id')->on('guides_referral_details');
        });
        Schema::table('product_finaly_assembleds', function (Blueprint $table) {
            $table->unsignedBigInteger('guide_refer_id')->nullable()->after('product_finaly_id');
            $table->foreign('guide_refer_id')->references('id')->on('guides_referral_details');
        });
        Schema::table('product_finaly_imported', function (Blueprint $table) {
            $table->unsignedBigInteger('guide_refer_id')->nullable()->after('product_finaly_id');
            $table->foreign('guide_refer_id')->references('id')->on('guides_referral_details');
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
            $table->dropForeign(['guide_refer_id']);
            $table->dropColumn('guide_refer_id');
        });
        Schema::table('product_finaly_assembleds', function (Blueprint $table) {
            $table->dropForeign(['guide_refer_id']);
            $table->dropColumn('guide_refer_id');
        });
        Schema::table('product_finaly_imported', function (Blueprint $table) {
            $table->dropForeign(['guide_refer_id']);
            $table->dropColumn('guide_refer_id');
        });
        Schema::table('raw_materials_history', function (Blueprint $table) {
            $table->unsignedBigInteger('quotation_detail_id')->nullable()->after('product_progres_hist_id');
            $table->foreign('quotation_detail_id')->references('id')->on('quotations_details');
        });
        Schema::table('product_finaly_assembleds', function (Blueprint $table) {
            $table->unsignedBigInteger('quotation_detail_id')->nullable()->after('product_finaly_id');
            $table->foreign('quotation_detail_id')->references('id')->on('quotations_details');
        });
        Schema::table('product_finaly_imported', function (Blueprint $table) {
            $table->unsignedBigInteger('quotation_detail_id')->nullable()->after('product_finaly_id');
            $table->foreign('quotation_detail_id')->references('id')->on('quotations_details');
        });
    }
};
