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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('quotation_customer')->unsigned();
            $table->foreign('quotation_customer')->references('id')->on('customers');
            $table->bigInteger('order_id')->unsigned();
            $table->foreign('order_id')->references('id')->on('orders');
            $table->bigInteger('quotation_customer_contact')->unsigned()->nullable();
            $table->foreign('quotation_customer_contact')->references('id')->on('contacts');
            $table->date('quotation_date_issue');
            $table->string('quotation_project');
            $table->string('quotation_type_money',5);
            $table->decimal('quotation_change_money')->nullable();
            $table->tinyInteger('quotation_include_igv');
            $table->string('quotation_customer_address')->nullable();
            $table->decimal('quotation_amount')->nullable();
            $table->decimal('quotation_discount')->nullable();
            $table->decimal('quotation_igv')->nullable();
            $table->decimal('quotation_total')->nullable();
            $table->bigInteger('quotation_quoter')->unsigned();
            $table->foreign('quotation_quoter')->references('id')->on('users');
            $table->longText('quotation_observations')->nullable();
            $table->longText('quotation_conditions')->nullable();
            $table->tinyInteger('quotation_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotations');
    }
};
