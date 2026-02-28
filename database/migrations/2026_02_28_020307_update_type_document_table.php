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
        Schema::table('type_documents',function(Blueprint $table){
            $table->integer('document_minimun')->nullable()->after('document_value')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('type_documents',function(Blueprint $table){
            $table->dropColumn('document_minimun');
        });
    }
};
