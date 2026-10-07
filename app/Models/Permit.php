<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permit extends Model
{
    protected $fillable = ['employee_id', 'tanggal', 'keterangan', 'status'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
