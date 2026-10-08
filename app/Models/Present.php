<?php
namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Present extends Model
{
    protected $hidden = ['created_at', 'updated_at', 'deleted_at', 'waktu'];
    protected $appends = ['time', 'name', 'tipe', 'late_time'];   

    public function gettimeAttribute()
    {
        $date = Carbon::parse($this->waktu)
            ->locale('id');
        return $date->translatedFormat('l, d F Y H:i:s');
    }

    public function getNameAttribute()
    {
        return $this->murid?->name ?? $this->employee?->name ?? '-';
    }

    public function getTipeAttribute()
    {
        return $this->employee_id ? 'Karyawan' : 'Murid';
    }

    public function getLateTimeAttribute()
    {
        if (empty($this->status) || strtolower($this->status) !== 'masuk') {
            return null;
        }

        $config = null;
        if ($this->employee_id) {
            $config = \App\Models\AttendanceConfig::where('employee_id', $this->employee_id)->first();
            if (!$config && $this->employee) {
                $config = \App\Models\AttendanceConfig::whereNull('employee_id')->where('app', $this->employee->app_id)->first();
            }
        } elseif ($this->student_id) {
            if ($this->murid) {
                $config = \App\Models\AttendanceConfig::whereNull('employee_id')->where('app', $this->murid->app)->first();
            }
        } else {
            $config = \App\Models\AttendanceConfig::whereNull('employee_id')->where('app', $this->app)->first();
        }

        if (!$config || !$config->clock_in_end) {
            return null;
        }

        try {
            $waktu = Carbon::parse($this->waktu);
            
            // clock_in_end bisa berupa 'H:i:s' atau 'H:i'
            $timeParts = explode(':', $config->clock_in_end);
            $hour = isset($timeParts[0]) ? (int)$timeParts[0] : 0;
            $minute = isset($timeParts[1]) ? (int)$timeParts[1] : 0;
            $second = isset($timeParts[2]) ? (int)$timeParts[2] : 0;
            
            $clockInEnd = Carbon::create($waktu->year, $waktu->month, $waktu->day, $hour, $minute, $second, $waktu->timezone);

            if ($waktu->greaterThan($clockInEnd)) {
                $diffInMinutes = $clockInEnd->diffInMinutes($waktu);
                if ($diffInMinutes >= 60) {
                    $hours = floor($diffInMinutes / 60);
                    $minutes = $diffInMinutes % 60;
                    return $minutes > 0 ? "Telat {$hours} jam {$minutes} mnt" : "Telat {$hours} jam";
                }
                return $diffInMinutes > 0 ? "Telat {$diffInMinutes} mnt" : null;
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    public function murid()
    {
        return $this->belongsTo(Students::class, 'student_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
