<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicYears extends Model
{
    use SoftDeletes;
    protected $table = 'academicyears';

    public function head()
    {
        return $this->belongsTo(Head::class,'id', 'academic_id');
    }

    public function students()
    {
        return $this->belongsToMany(
            Students::class,
            'heads',
            'academic_id',
            'student_id'
        )->withPivot('status')
            ->withTimestamps();
    }

    public function kelas()
    {
        return $this->belongsToMany(
            Classes::class,
            'heads',
            'academic_id',
            'class_id'
        )->withPivot('status')
            ->withTimestamps();
    }

}
