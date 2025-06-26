<?php
// filepath: database/migrations/yyyy_mm_dd_add_egg_counts_to_deteksi_telur.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEggCountsToDeteksiTelur extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('deteksi_telur', function (Blueprint $table) {
            $table->integer('jumlah_mutu1')->default(0)->after('catatan');
            $table->integer('jumlah_mutu2')->default(0)->after('jumlah_mutu1');
            $table->integer('jumlah_mutu3')->default(0)->after('jumlah_mutu2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('deteksi_telur', function (Blueprint $table) {
            $table->dropColumn(['jumlah_mutu1', 'jumlah_mutu2', 'jumlah_mutu3']);
        });
    }
}