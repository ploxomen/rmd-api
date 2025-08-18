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
        Schema::table('commodity_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('guide_refer_id')->nullable()->after('product_id');
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
        Schema::table('commodity_histories', function (Blueprint $table) {
            $table->dropForeign(['guide_refer_id']);
        });
        Schema::table('commodity_histories', function (Blueprint $table) {
            $table->dropColumn('guide_refer_id');
        });
    }
};
