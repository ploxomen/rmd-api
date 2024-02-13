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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name',250);
            $table->string('product_description',2000)->nullable();
            $table->decimal('product_buy')->nullable();
            $table->decimal('product_sale');
            $table->bigInteger('sub_categorie')->unsigned();
            $table->foreign('sub_categorie')->references('id')->on('sub_categories');
            $table->string('product_img')->nullable();
            $table->tinyInteger('product_status');
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
        Schema::dropIfExists('products');
    }
};
