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
        Schema::create('guides_referral', function (Blueprint $table) {
            $table->id();
            $table->date('guide_issue_date');
            $table->unsignedBigInteger('guide_customer_id');
            $table->string('guide_issue_year');
            $table->integer('guide_issue_number');
            $table->string('guide_address_destination');
            $table->string('guide_justification');
            $table->decimal('guite_total',14,2)->default(0);
            $table->unsignedBigInteger('guide_user_id');
            $table->foreign('guide_customer_id')->references('id')->on('customers');
            $table->foreign('guide_user_id')->references('id')->on('users');
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
        Schema::dropIfExists('guides_referral');
    }
};
