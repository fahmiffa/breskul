<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{

    protected $appends = ['state', 'roles', 'data'];
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public function getstateAttribute()
    {
        return $this->status == 0 ? 'TIdak Aktif' : 'Aktif';
    }

    public function getrolesAttribute()
    {
        if ($this->role == 0) {
            return "Admin";
        }

        if ($this->role == 3) {
            return "Guru";
        }

        if ($this->role == 2) {
            return "Siswa";
        }
    }

    public function app()
    {
        return $this->belongsTo(App::class, 'id', 'user_id');
    }

    public function getDataAttribute()
    {
        if ($this->role == 3) {
            return $this->teacherData;
        }
        return $this->studentData;
    }

    public function teacherData()
    {
        return $this->hasOne(Teach::class, 'user_id', 'id');
    }

    public function studentData()
    {
        return $this->hasOne(Students::class, 'user', 'id'); // pastikan foreign key benar
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }
}
