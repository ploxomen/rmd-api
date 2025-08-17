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
            $table->unsignedBigInteger('commodity_provider')->after('product_id');
            $table->decimal('commodi_hist_type_change',16,2)->after('commodi_hist_total_buy_usd');
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
            $table->dropColumn('commodity_provider');
            $table->dropColumn('commodi_hist_type_change');
        });
    }
};
