<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HalaqahStudent extends Model
{
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'present_at' => 'datetime',
    ];

    public function halaqah()
    {
        return $this->belongsTo(Halaqah::class, 'halaqah_id');
    }

    public function student()
    {
        return $this->belongsTo(Students::class, 'students_id');
    }
}
