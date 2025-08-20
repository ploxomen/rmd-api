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
        Schema::table('guides_referral',function(Blueprint $table){
            $table->string('guide_bill_number')->nullable()->after('guide_issue_number');
            $table->string('guide_observations')->nullable()->after('guide_issue_number');
            $table->date('guide_transfer_date')->nullable()->after('guide_issue_number');
            $table->string('guide_type_motion')->nullable()->after('guide_issue_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guides_referral',function(Blueprint $table){
            $table->dropColumn('guide_bill_number');
            $table->dropColumn('guide_observations');
            $table->dropColumn('guide_transfer_date');
            $table->dropColumn('guide_type_motion');
        });
    }
};
