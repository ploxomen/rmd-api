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
        Schema::create('shopping', function (Blueprint $table) {
            $table->id();
            $table->date('buy_date');
            $table->date('buy_date_invoice')->nullable();
            $table->unsignedBigInteger(column: 'buy_provider');
            $table->unsignedBigInteger(column: 'buy_user');
            $table->string('buy_number_invoice');
            $table->string('buy_number_guide')->nullable();
            $table->string('buy_type');
            $table->decimal('buy_type_change')->default(0);
            $table->string('buy_type_money');
            $table->decimal('buy_total',16,2);
            $table->decimal('buy_total_usd',16,2);
            $table->foreign('buy_provider')->references('id')->on('provider');
            $table->foreign('buy_user')->references('id')->on(table: 'users');
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
        Schema::dropIfExists('shopping');
    }
};
