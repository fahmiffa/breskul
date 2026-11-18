<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{

    protected $appends = ['state', 'roles', 'data', 'jabatan_name'];
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

        if ($this->role == 1) {
            return "Admin App";
        }

        if ($this->role == 2) {
            return "Siswa";
        }

        if ($this->role == 3) {
            return "Guru";
        }

        if ($this->role == 4) {
            return "Petugas";
        }

        if ($this->role == 5) {
            return "Keuangan";
        }

        if ($this->role == 6) {
            return "Akademik";
        }

        return "User";
    }

    public function getJabatanNameAttribute()
    {
        if ($this->jabatan) {
            return $this->jabatan->name;
        }
        if ($this->employeeData && $this->employeeData->jabatan) {
            return $this->employeeData->jabatan->name;
        }
        return null;
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

    public function employeeData()
    {
        return $this->hasOne(Employee::class, 'user_id', 'id');
    }

    public function studentData()
    {
        return $this->hasOne(Students::class, 'user', 'id'); // pastikan foreign key benar
    }

    public function student()
    {
        return $this->hasOne(Students::class, 'user', 'id');
    }

    public function saldo()
    {
        return $this->hasOneThrough(Saldo::class, Students::class, 'user', 'students_id', 'id', 'id');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    // ==========================================
    // RBAC Relations & Methods
    // ==========================================

    /**
     * Relasi many-to-many ke Role model (RBAC)
     */
    public function rolesRelation()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Cek apakah user punya permission tertentu (dengan cache)
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->getCachedPermissions();
        return in_array($permission, $permissions);
    }

    /**
     * Cek apakah user punya salah satu dari beberapa permission
     */
    public function hasAnyPermission(array $permissions): bool
    {
        $userPermissions = $this->getCachedPermissions();
        return !empty(array_intersect($permissions, $userPermissions));
    }

    /**
     * Cek apakah user punya role tertentu (by name)
     */
    public function hasRoleName(string $roleName): bool
    {
        $roleNames = $this->getCachedRoleNames();
        return in_array($roleName, $roleNames);
    }

    /**
     * Ambil semua permissions user (dari semua role) - cached
     */
    public function getCachedPermissions(): array
    {
        return Cache::remember("user-{$this->id}-permissions", 300, function () {
            return $this->rolesRelation()
                ->with('permissions')
                ->get()
                ->pluck('permissions')
                ->flatten()
                ->pluck('name')
                ->unique()
                ->values()
                ->toArray();
        });
    }

    /**
     * Ambil semua role names user - cached
     */
    public function getCachedRoleNames(): array
    {
        return Cache::remember("user-{$this->id}-roles", 300, function () {
            return $this->rolesRelation()->pluck('name')->toArray();
        });
    }

    /**
     * Clear cache permission & role user ini
     */
    public function clearRbacCache(): void
    {
        Cache::forget("user-{$this->id}-permissions");
        Cache::forget("user-{$this->id}-roles");
    }

    // ==========================================
    // Existing Methods
    // ==========================================

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
        'nomor',
        'image',
        'jabatan_id',
        'face_embedding',
        'is_lock',
        'deviceId',
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
            'is_lock'           => 'boolean',
        ];
    }
}
