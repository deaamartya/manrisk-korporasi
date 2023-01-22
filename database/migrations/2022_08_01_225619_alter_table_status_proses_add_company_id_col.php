<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableStatusProsesAddCompanyIdCol extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('status_proses', function (Blueprint $table) {
            $table->unsignedInteger('company_id')->after('tahun');
            $table->foreign('company_id')->references('company_id')->on('perusahaan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('status_proses', function (Blueprint $table) {
            $table->dropColumn('company_id');
        });
    }
}
