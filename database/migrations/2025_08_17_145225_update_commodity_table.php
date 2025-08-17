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
            $table->unsignedBigInteger('commodity_provider')->nullable()->after('product_id');
            $table->date('commodi_hist_date')->after('product_id');
            $table->unsignedBigInteger('commodi_hist_user')->after('product_id');
            $table->decimal('commodi_hist_type_change',16,2)->after('commodi_hist_total_buy_usd');
            $table->foreign('commodity_provider')->references('id')->on('provider');
            $table->foreign('commodi_hist_user')->references('id')->on('users');

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
            $table->dropForeign(['commodity_provider']);
            $table->dropForeign(['commodi_hist_user']);
        });
        Schema::table('commodity_histories', function (Blueprint $table) {
            $table->dropColumn('commodity_provider');
            $table->dropColumn('commodi_hist_date');
            $table->dropColumn('commodi_hist_type_change');
            $table->dropColumn('commodi_hist_user');
        });
    }
};
