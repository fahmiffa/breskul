<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class App extends Model
{
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function devices()
    {
        return $this->hasMany(Device::class, 'app_id', 'id');
    }
}
