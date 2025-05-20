<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTerlambatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('terlambat', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_ijin');
            $table->string('jam');
            $table->string('hari');
            $table->date('tanggal');
            $table->string('alasan');
            $table->string('status');
            $table->string('access_token')->nullable();
            $table->unsignedBigInteger('pegawai_id');
            $table->unsignedBigInteger('pejabat_id');
            $table->timestamp('tanggal_disetujui_pejabat')->nullable();
            $table->unsignedBigInteger('tim_kerja_id');
            $table->foreign('pegawai_id')->references('id')->on('pegawais')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('pejabat_id')->references('id')->on('pejabats')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('tim_kerja_id')->references('id')->on('tim_kerja')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('terlambat');
    }
}
