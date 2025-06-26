<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class CreateDeteksiTelur extends Migration
{
    public function up(): void
    {
        Schema::create('deteksi_telur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nama_file');
            $table->enum('kategori', ['MUTU 1', 'MUTU 2', 'MUTU 3']);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deteksi_telur');
    }
}