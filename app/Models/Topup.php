<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Topup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'nominal',
        'kode_unik',
        'total_nominal',
        'status',
        'expired_at',
    ];

    protected $casts = [
        'nominal'       => 'decimal:2',
        'total_nominal' => 'decimal:2',
        'kode_unik'     => 'integer',
        'expired_at'    => 'datetime',
    ];

    protected $appends = [
        'formatted_nominal',
        'formatted_total',
        'is_expired',
    ];

    public function student()
    {
        return $this->belongsTo(Students::class, 'student_id');
    }

    public function getFormattedNominalAttribute()
    {
        return 'Rp ' . number_format($this->nominal ?? 0, 0, ',', '.');
    }

    public function getFormattedTotalAttribute()
    {
        return 'Rp ' . number_format($this->total_nominal ?? ($this->nominal + $this->kode_unik), 0, ',', '.');
    }

    public function getIsExpiredAttribute()
    {
        if ($this->status === 'success') {
            return false;
        }

        return $this->expired_at ? Carbon::now()->greaterThan($this->expired_at) : false;
    }
}
