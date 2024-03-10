<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->boolean('closed')->default(1)->after('issue_date');
            $table->date('closed_date')->default(DB::raw('CURRENT_DATE'))->nullable()->after('closed');
            $table->text('todos')->nullable()->after('closed_date');
            $table->text('quality_control_verification')->nullable()->after('todos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->dropColumn('closed');
            $table->dropColumn('todos');
            $table->dropColumn('quality_control_verification');
        });
    }
};
