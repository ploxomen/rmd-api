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
        Schema::table('order_productions_details', function (Blueprint $table){
            $table->dropColumn('product_label_hr');
            $table->dropColumn('product_label_total');
            $table->decimal('pro_escandallo_total', 10, 3)->default(0.000)->after('amount');
            $table->decimal('pro_group_work_time_hours', 10, 3)->default(0.000)->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_productions_details', function (Blueprint $table){
            $table->dropColumn('pro_escandallo_total');
            $table->dropColumn('pro_group_work_time_hours');
            $table->decimal('product_label_hr', 10, 2)->default(0.000)->after('amount');
            $table->decimal('product_label_total', 10, 2)->default(0.000)->after('amount');
        });
    }
};
