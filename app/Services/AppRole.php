<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class AppRole
{
    public static function isApp()
    {
        $role = Auth::user()->role;
        if ($role === 0) {
            return 0;
        }
        else
        {
            return $app = auth()->user()->role == 1 ? auth()->user()->app->id : auth()->user()->employeeData->app_id;
        }
    }
}