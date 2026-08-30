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
        Schema::create('product_product_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('product_label_id')
                ->constrained('product_labels')
                ->cascadeOnDelete();

            $table->unsignedInteger('time_origin_minute')
                ->default(0);

            $table->decimal('time_origin_hours', 10, 2)
                ->default(0);

            $table->timestamps();

            $table->unique([
                'product_id',
                'product_label_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_product_labels');
    }
};
