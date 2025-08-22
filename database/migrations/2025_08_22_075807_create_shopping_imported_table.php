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
        Schema::create('shopping_imported', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shopping_id');
            $table->string('imported_nro_dam')->nullable();
            $table->decimal('imported_expenses_cost',16,2)->nullable();
            $table->decimal('imported_flete_cost',16,2)->nullable();
            $table->decimal('imported_insurance_cost',16,2)->nullable();
            $table->decimal('imported_destination_cost',16,2)->nullable();
            $table->decimal('imported_coefficient',16,2)->nullable();
            $table->foreign('shopping_id')->references('id')->on('shopping');
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
        Schema::dropIfExists('shopping_imported');
    }
};
