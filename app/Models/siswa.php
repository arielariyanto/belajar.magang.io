<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Siswa extends Model // ✅ Awalan huruf besar!
{
    use HasFactory;
    protected $table = 'siswas'; // ✅ Nama tabel sesuai migration

    protected $fillable = ['nisn', 'nama', 'kelas', 'jurusan_id'];

    public function getLabelAttribute()
    {
        return "{$this->nisn} - {$this->nama}";
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id', 'kode_jurusan');
    }
}
