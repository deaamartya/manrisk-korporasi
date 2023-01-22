<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableStatusProsesAddDivisiIdCol extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('status_proses', function (Blueprint $table) {
            $table->unsignedInteger('divisi_id')->after('tahun');
            $table->foreign('divisi_id')->references('divisi_id')->on('divisi');
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
            $table->dropColumn('divisi_id');
        });
    }
}
