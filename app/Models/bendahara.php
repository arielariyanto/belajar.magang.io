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
        'jurusan',
        'jumlah'
    ];

    // ✅ GANTI ini ↓
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function pengeluarans()
    {
        return $this->hasMany(\App\Models\Pengeluaran::class);
    }
}
