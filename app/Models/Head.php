<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Head extends Model
{
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function bill()
    {
        return $this->hasMany(Bill::class, 'head_id', 'id');
    }

    public function murid()
    {
        return $this->belongsTo(Students::class, 'student_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function akademik()
    {
        return $this->belongsTo(AcademicYears::class, 'academic_id');
    }
}
