<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceConfig extends Model
{
    protected $fillable = [
        'app',
        'name',
        'employee_id',
        'clock_in_start',
        'clock_in_end',
        'clock_out_start',
        'clock_out_end',
        'lat',
        'lng',
        'radius',
        'students'
    ];

    public function employee()
    {
        return $this->belongsTo(\App\Models\Employee::class);
    }

    public function appData()
    {
        return $this->belongsTo(\App\Models\App::class, 'app', 'id');
    }

    public function times()
    {
        return $this->hasMany(\App\Models\AttendanceConfigTime::class);
    }

    /**
     * Ambil konfigurasi waktu untuk hari tertentu.
     * Default: hari ini (nama hari Indonesia).
     */
    public function getTimeForDay(?string $day = null): ?AttendanceConfigTime
    {
        if (!$day) {
            $day = self::todayDayName();
        }

        if ($this->relationLoaded('times')) {
            return $this->times->firstWhere('day', $day);
        }

        return $this->times()->where('day', $day)->first();
    }

    /**
     * Nama hari Indonesia berdasarkan tanggal (Carbon/string/null).
     */
    public static function dayNameFromDate($date = null): string
    {
        $carbon = $date ? ($date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date)) : now();

        $map = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu',
        ];

        return $map[$carbon->format('l')] ?? 'Senin';
    }

    /**
     * Nama hari Indonesia berdasarkan Carbon now().
     */
    public static function todayDayName(): string
    {
        return self::dayNameFromDate(now());
    }
}
