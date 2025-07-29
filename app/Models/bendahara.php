<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bendahara extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_bendahara', 
        'kelas',
        'siswa_id',
        'jurusan_id',
        'jumlah'
    ];

    // ✅ GANTI ini ↓
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pengeluarans()
    {
        return $this->hasMany(\App\Models\Pengeluaran::class);
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id', 'kode_jurusan');
    }

}
