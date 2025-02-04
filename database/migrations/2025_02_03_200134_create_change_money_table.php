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
        Schema::create('change_money', function (Blueprint $table) {
            $table->id();
            $table->date('change_day');
            $table->decimal('change_soles');
            $table->integer('change_attempts')->default(0);
            $table->bigInteger('change_user')->unsigned();
            $table->timestamps();
            $table->foreign('change_user')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('change_money');
    }
};
