<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Halaqah extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function teach()
    {
        return $this->belongsTo(Teach::class, 'teach_id');
    }

    public function students()
    {
        return $this->hasMany(HalaqahStudent::class, 'halaqah_id');
    }
}
