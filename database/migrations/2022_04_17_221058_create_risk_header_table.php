<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiskHeaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('risk_header', function (Blueprint $table) {
            $table->integer('id_riskh', true);
            $table->integer('id_user');
            $table->unsignedInteger('divisi_id')->default(1);
            $table->foreign('divisi_id')->references('divisi_id')->on('divisi');
            $table->string('tahun', 5);
            $table->date('tanggal')->useCurrent();
            $table->text('target')->nullable();
            $table->string('penyusun', 100)->nullable();
            $table->string('pemeriksa', 100)->nullable();
            $table->string('lampiran', 200)->nullable();
            $table->integer('status_h')->default(0);
            // $table->integer('deleted')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('risk_header');
    }
}
