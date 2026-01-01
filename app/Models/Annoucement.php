<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Annoucement extends Model
{
    protected $appends = ['gambar', 'url'];

    public function getgambarAttribute()
    {
        return asset('storage/' . $this->img);
    }

    public function geturlAttribute()
    {
        return route('pengumuman',['id'=>$this->id]);
    }

    protected $hidden = [
        'created_at',
        'updated_at',
        'app',
        'img',
    ];
}
