<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UjianStudent extends Model
{
    use HasFactory;

    protected $table = 'ujian_students';

    protected $fillable = [
        'ujian_id',
        'student_id',
        'status',
        'score',
        'answers',
        'pdf',
        'started_at',
        'finished_at',
        'payment_status',
        'unique_code',
        'qris_data',
        'qris_expired_at',
    ];

    protected $casts = [
        'answers'        => 'array',
        'payment_status' => 'integer',
    ];

    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }

    public function student()
    {
        return $this->belongsTo(Students::class, 'student_id');
    }
}
