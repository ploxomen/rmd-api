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
        Schema::create('guides_referral_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guide_referral_id');
            $table->unsignedBigInteger('guide_product_id');
            $table->integer('guide_product_quantity');
            $table->string('guide_product_type');
            $table->foreign('guide_referral_id')->references('id')->on('guides_referral');
            $table->foreign('guide_product_id')->references('id')->on('products');
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
        Schema::dropIfExists('guides_referral_details');
    }
};
