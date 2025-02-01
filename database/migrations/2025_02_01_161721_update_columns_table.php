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
        Schema::table('quotations', function (Blueprint $table) {
            $table->decimal('quotation_amount', 16, 2)->change();
            $table->decimal('quotation_total', 16, 2)->change();
        });
        Schema::table('quotations_details', function (Blueprint $table) {
            $table->decimal('detail_quantity', 16, 2)->change();
            $table->decimal('detail_price_additional', 16, 2)->change();
            $table->decimal('detail_total', 16, 2)->change();
            $table->decimal('detail_price_unit', 16, 2)->change();
            $table->decimal('detail_price_buy', 16, 2)->change();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('order_mount', 16, 2)->change();
            $table->decimal('order_total', 16, 2)->change();
            $table->decimal('order_mount_igv', 16, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->decimal('quotation_amount', 8, 2)->change();
            $table->decimal('quotation_total', 8, 2)->change();
        });
        Schema::table('quotations_details', function (Blueprint $table) {
            $table->decimal('detail_quantity', 8, 2)->change();
            $table->decimal('detail_price_additional', 8, 2)->change();
            $table->decimal('detail_total', 8, 2)->change();
            $table->decimal('detail_price_unit', 8, 2)->change();
            $table->decimal('detail_price_buy', 8, 2)->change();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('order_mount', 8, 2)->change();
            $table->decimal('order_total', 8, 2)->change();
            $table->decimal('order_mount_igv', 8, 2)->change();
        });
    }
};
