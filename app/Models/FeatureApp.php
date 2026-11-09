<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureApp extends Model
{
    use HasFactory;

    protected $table = 'feature_apps';

    protected $fillable = [
        'app_id',
        'feature_id',
    ];

    public function app()
    {
        return $this->belongsTo(App::class, 'app_id', 'id');
    }

    public function feature()
    {
        return $this->belongsTo(Feature::class, 'feature_id', 'id');
    }
}
