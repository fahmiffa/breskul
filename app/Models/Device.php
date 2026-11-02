<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'app_id',
        'device_name',
        'status',
        'ip_address',
    ];

    protected $appends = [
        'status_label',
        'last_seen',
    ];

    protected $casts = [
        'status' => 'integer',
        'app_id' => 'integer',
    ];

    public function app()
    {
        return $this->belongsTo(App::class, 'app_id', 'id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ((int)$this->status) {
            1 => 'Stand by',
            default => 'Startup',
        };
    }

    public function getLastSeenAttribute(): string
    {
        return $this->updated_at ? $this->updated_at->diffForHumans() : '-';
    }
}
