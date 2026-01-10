<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Extracurricular extends Model
{
    use SoftDeletes;

    protected $fillable = ['nama', 'guru_id', 'waktu', 'app'];

    public function guru()
    {
        return $this->belongsTo(Teach::class, 'guru_id');
    }
}
