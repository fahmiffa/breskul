<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasFactory;

    protected $table = 'features';

    protected $fillable = [
        'name',
    ];

    public function featureApps()
    {
        return $this->hasMany(FeatureApp::class, 'feature_id', 'id');
    }

    public function feature_apps()
    {
        return $this->hasMany(FeatureApp::class, 'feature_id', 'id');
    }

    public function apps()
    {
        return $this->belongsToMany(App::class, 'feature_apps', 'feature_id', 'app_id');
    }
}
