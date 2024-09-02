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
        Schema::table("orders",function (Blueprint $table){
            $table->dropColumn("order_details");
            $table->bigInteger("order_district")->unsigned();
            $table->string("order_conditions_pay");
            $table->integer("order_number");
            $table->string("order_code",15);
            $table->string("order_conditions_delivery");
            $table->string("order_address");
            $table->string("order_project");
            $table->string("order_contact_email");
            $table->string("order_contact_telephone");
            $table->string("order_contact_name");
            $table->string("order_file_name");
            $table->string("order_file_url");
            $table->foreign('order_district')->references('id')->on('districts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("orders",function (Blueprint $table){
            $table->longText('order_details')->nullable();
            $table->dropColumn("order_district");
            $table->dropColumn("order_conditions_pay");
            $table->dropColumn("order_conditions_delivery");
            $table->dropColumn("order_address");
            $table->dropColumn("order_project");
            $table->dropColumn("order_contact_email");
            $table->dropColumn("order_contact_telephone");
            $table->dropColumn("order_contact_name");
            $table->dropColumn("order_file_name");
            $table->dropColumn("order_file_url");
        });
    }
};
