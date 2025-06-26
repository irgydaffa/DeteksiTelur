<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeteksiTelur extends Model
{
    use HasFactory;

    protected $table = 'deteksi_telur';

    protected $fillable = [
        'user_id',
        'nama_file',
        'image_url',
        'kategori',
        'jumlah_mutu1',
        'jumlah_mutu2',
        'jumlah_mutu3',
        'catatan'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalEggsAttribute()
    {
        return $this->jumlah_mutu1 + $this->jumlah_mutu2 + $this->jumlah_mutu3;
    }
}