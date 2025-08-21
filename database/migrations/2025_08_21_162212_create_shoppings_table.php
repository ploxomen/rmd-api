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
            $table->unsignedBigInteger('buy_provider');
            $table->string('buy_number_invoice');
            $table->string('buy_number_guide')->nullable();
            $table->string('buy_type');
            $table->decimal('buy_total',16,2);
            $table->foreign('buy_provider')->references('id')->on('provider');
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
        Schema::dropIfExists('shoppings');
    }
};
