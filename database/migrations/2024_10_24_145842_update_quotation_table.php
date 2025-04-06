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
        Schema::table('quotations',function(Blueprint $table){
            $table->longText('quotation_warranty_1')->after('quotation_conditions');
            $table->longText('quotation_warranty_2')->after('quotation_warranty_1');
            $table->string('quotation_view_pdf')->after('quotation_warranty_2')->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quotations',function(Blueprint $table){
            $table->dropColumn('quotation_warranty_1');
            $table->dropColumn('quotation_warranty_2');
            $table->dropColumn('quotation_view_pdf');
        });
    }
};
