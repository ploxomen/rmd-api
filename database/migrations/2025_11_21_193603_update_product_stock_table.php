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
        Schema::table('product_stock_initial',function(Blueprint $table){            
            $table->decimal('price_unit_pen',16,2)->nullable()->after('type_change_money');
            $table->decimal('price_unit_usd',16,2)->nullable()->after('type_change_money');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_stock_initial',function(Blueprint $table){            
            $table->dropColumn('price_unit_pen');
            $table->dropColumn('price_unit_usd');
        });
    }
};
