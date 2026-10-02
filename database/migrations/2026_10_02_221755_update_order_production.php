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
        Schema::table('product_product_labels', function (Blueprint $table){
            $table->dropColumn('time_origin_minute');
            $table->dropColumn('time_origin_hours');
            $table->decimal('group_work_time_hours', 10, 3)->default(0.000)->after('product_label_id');
            $table->integer('group_work_number')->default(0)->after('product_label_id');
            $table->decimal('work_time_hours', 10, 3)->default(0.000)->after('product_label_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_product_labels', function (Blueprint $table){
            $table->integer('time_origin_minute')->nullable();
            $table->decimal('time_origin_hours', 10, 2)->nullable();
            $table->dropColumn('work_time_hours');
            $table->dropColumn('group_work_number');
            $table->dropColumn('group_work_time_hours');
        });
    }
};
