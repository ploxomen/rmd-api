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
        Schema::table('quotations_details', function (Blueprint $table) {
            $table->string('detail_type_price')->after('detail_price_buy');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quotations_details', function (Blueprint $table) {
            $table->dropColumn('detail_type_price');
        });
    }
};
