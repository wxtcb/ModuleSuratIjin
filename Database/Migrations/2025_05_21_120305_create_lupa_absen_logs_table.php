<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLupaAbsenLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lupa_absen_logs', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->unsignedBigInteger('lupa_absen_id');
            $table->unsignedBigInteger('updated_by');
            $table->text('catatan')->nullable();
            $table->foreign('lupa_absen_id')->references('id')->on('lupa_absen')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('pegawais')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('lupa_absen_logs');
    }
}
