<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::create('product_finaly_assembleds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_finaly_id');
            $table->date('product_finaly_created');
            $table->integer('product_finaly_amount');
            $table->text('product_finaly_description')->nullable();
            $table->unsignedBigInteger('product_finaly_user');
            $table->foreign('product_finaly_user')->references('id')->on('users');
            $table->foreign('product_finaly_id')->references('id')->on('product_finalies');
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
        Schema::dropIfExists('product_finaly_assembleds');
    }
};
