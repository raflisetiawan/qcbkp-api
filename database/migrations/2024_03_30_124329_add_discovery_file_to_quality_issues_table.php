<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('quality_issues', function (Blueprint $table) {
            $table->string('discovery_file')->nullable()->after('quality_control_verification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('quality_issues', function (Blueprint $table) {
            $table->dropColumn('discovery_file');
        });
    }
};
