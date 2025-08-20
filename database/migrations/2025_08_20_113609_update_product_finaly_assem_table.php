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
        Schema::table('product_finaly_assem_deta', function (Blueprint $table) {
            $table->decimal('product_finaly_price_unit',16,2)->nullable()->after('product_finaly_stock');
            $table->decimal('product_finaly_subtotal',16,2)->nullable()->after('product_finaly_stock');
        });
        Schema::table('product_finaly_assembleds', function (Blueprint $table) {
            $table->decimal('product_finaly_total',16,2)->nullable()->default(0)->after('product_finaly_description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_finaly_assembleds', function (Blueprint $table) {
            $table->dropColumn('product_finaly_total');
        });
        Schema::table('product_finaly_assem_deta', function (Blueprint $table) {
            $table->dropColumn('product_finaly_price_unit');
            $table->dropColumn('product_finaly_subtotal');
        });
    }
};
