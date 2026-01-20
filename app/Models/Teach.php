<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teach extends Model
{
    use SoftDeletes;
    protected $appends = ['jenis'];
    protected $hidden  = ['created_at', 'updated_at' . 'deleted_at'];

    public function getjenisAttribute()
    {
        return $this->gender == 1 ? "laki-laki" : "Perempuan";
    }

    public function extra()
    {
        return $this->hasMany(Extracurricular::class, 'guru_id', 'id');
    }

    public function mapel()
    {
        return $this->hasMany(MapelTime::class, 'teacher_id', 'id');
    }


    public function apps()
    {
        return $this->hasOne(App::class, 'id', 'app');
    }
}
