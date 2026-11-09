<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class App extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'status',
        'alamat',
        'hp',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function devices()
    {
        return $this->hasMany(Device::class, 'app_id', 'id');
    }

    public function featureApps()
    {
        return $this->hasMany(FeatureApp::class, 'app_id', 'id');
    }

    public function feature_apps()
    {
        return $this->hasMany(FeatureApp::class, 'app_id', 'id');
    }

    public function features()
    {
        return $this->belongsToMany(Feature::class, 'feature_apps', 'app_id', 'feature_id');
    }
}
