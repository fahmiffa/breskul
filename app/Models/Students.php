<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Students extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'boarding' => 'boolean',
    ];

    protected $appends = ['age', 'jenis', 'pesantren'];
    protected $hidden  = ['created_at', 'updated_at', 'deleted_at'];

    public function getPesantrenAttribute()
    {
        return $this->boarding ? 'Pesantren' : 'Bukan Pesantren';
    }

    public function getageAttribute()
    {
        return Carbon::parse($this->birth ?? $this->bith)->age;
    }

    public function head()
    {
        return $this->hasMany(Head::class, 'student_id', 'id');
    }

    public function saldo()
    {
        return $this->hasOne(Saldo::class, 'students_id', 'id');
    }

    public function saldoLogs()
    {
        return $this->hasMany(LogSaldo::class, 'students_id', 'id')->latest();
    }

    public function logSaldo()
    {
        return $this->hasMany(LogSaldo::class, 'students_id', 'id')->latest();
    }

    public function topups()
    {
        return $this->hasMany(Topup::class, 'student_id', 'id')->latest();
    }

    public function users()
    {
        return $this->hasOne(User::class, 'id', 'user');
    }

    public function apps()
    {
        return $this->hasOne(App::class, 'id', 'app');
    }

    public function reg()
    {
        return $this->hasOne(Head::class, 'student_id', 'id')->where('status', 1)->latest();
    }

    public function academics()
    {
        return $this->belongsToMany(
            AcademicYears::class,  // model yang dituju
            'heads',               // nama tabel pivot
            'student_id',          // foreign key di tabel pivot yang menunjuk ke students
            'academic_id'          // foreign key di tabel pivot yang menunjuk ke academic_years
        )->withPivot('status') // jika mau akses kolom tambahan di pivot
            ->withTimestamps();    // jika ingin otomatis created_at dan updated_at dari pivot
    }

    public function activeAcademics()
    {
        return $this->belongsToMany(
            AcademicYears::class,
            'heads',
            'student_id',
            'academic_id'
        )
            ->where('academicyears.status', 1)
            ->withPivot('status')
            ->withTimestamps();
    }

    public function Kelas()
    {
        return $this->belongsToMany(
            Classes::class,        // model yang dituju
            'heads',               // nama tabel pivot
            'student_id',          // foreign key di tabel pivot yang menunjuk ke students
            'class_id'             // foreign key di tabel pivot yang menunjuk ke academic_years
        )->withPivot('status') // jika mau akses kolom tambahan di pivot
            ->withTimestamps();    // jika ingin otomatis created_at dan updated_at dari pivot
    }

    public function Prodi()
    {
        return $this->belongsToMany(
            Prodi::class,
            'heads',
            'student_id',
            'prodi_id'
        )->withPivot('status')->withTimestamps();
    }

    public function getjenisAttribute()
    {
        return $this->gender == 1 ? "laki-laki" : "Perempuan";
    }

    public function halaqahStudents()
    {
        return $this->hasMany(HalaqahStudent::class, 'students_id', 'id');
    }

    public function halaqahs()
    {
        return $this->belongsToMany(
            Halaqah::class,
            'halaqah_students',
            'students_id',
            'halaqah_id'
        )->withPivot('id', 'present_at', 'catatan')->withTimestamps();
    }
}
