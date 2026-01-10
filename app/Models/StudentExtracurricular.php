<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentExtracurricular extends Model
{
    use SoftDeletes;

    protected $fillable = ['student_id', 'extracurricular_id', 'app'];

    public function student()
    {
        return $this->belongsTo(Students::class, 'student_id');
    }

    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class, 'extracurricular_id');
    }
}
