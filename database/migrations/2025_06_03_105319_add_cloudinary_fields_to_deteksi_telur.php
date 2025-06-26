<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCloudinaryFieldsToDeteksiTelur extends Migration
{
    public function up()
    {
        Schema::table('deteksi_telur', function (Blueprint $table) {
            $table->string('image_url')->nullable()->after('nama_file');
            $table->string('cloudinary_id')->nullable()->after('image_url');
        });
    }

    public function down()
    {
        Schema::table('deteksi_telur', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'cloudinary_id']);
        });
    }
}