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
        Schema::table('guides_referral', function (Blueprint $table){
            $table->decimal('guide_type_change')->nullable()->default(0)->after('guide_bill_number');
        });
        Schema::table('product_progress_history', function (Blueprint $table){
            $table->decimal('prod_prog_type_change')->nullable()->default(0)->after('product_progress_history_total');
        });
        Schema::table('product_finaly_assembleds', function (Blueprint $table){
            $table->decimal('prod_fina_type_change')->nullable()->default(0)->after('product_finaly_amount');
        });  
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guides_referral', function (Blueprint $table){
            $table->dropColumn('guide_type_change');
        });
        Schema::table('product_progress_history', function (Blueprint $table){
            $table->dropColumn('prod_prog_type_change');
        });
        Schema::table('product_finaly_assembleds', function (Blueprint $table){
            $table->dropColumn('prod_fina_type_change');
        });  
    }
};
