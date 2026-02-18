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
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'answers' => 'array',
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
