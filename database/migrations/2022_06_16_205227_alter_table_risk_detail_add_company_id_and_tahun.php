<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableRiskDetailAddDivisiIdAndTahun extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('risk_detail', function (Blueprint $table) {
            $table->UnsignedInteger('divisi_id')->after('id_s_risiko')->nullable();
            $table->integer('tahun')->after('divisi_id')->nullable();
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
        Schema::table('risk_detail', function (Blueprint $table) {
            $table->dropForeign('risk_detail_divisi_id_foreign');
            $table->dropColumn('divisi_id')->after('id_s_risiko')->nullable();
            $table->dropColumn('tahun')->after('divisi_id')->nullable();
        });
    }
}
