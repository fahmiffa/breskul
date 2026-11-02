<?php

namespace App\Http\Controllers;

use App\Models\App;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
    /**
     * Resolve the app_id based on authenticated user and user role.
     */
    protected function resolveAppId(Request $request): ?int
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        // Role 1: Admin App (linked to App via user_id)
        if ($user->role == 1 && $user->app) {
            return (int) $user->app->id;
        }

        // Check if user has direct app relation
        if ($user->app) {
            return (int) $user->app->id;
        }

        // Role 0: Super Admin (can view specific app if filtered, or null for all)
        if ($user->role == 0) {
            return $request->filled('app_id') ? (int) $request->get('app_id') : null;
        }

        // Role 3: Guru
        if ($user->role == 3 && $user->teacherData) {
            return (int) ($user->teacherData->app ?? null);
        }

        return null;
    }

    /**
     * Display a listing of devices.
     */
    public function index(Request $request)
    {
        $appId = $this->resolveAppId($request);

        $query = Device::query()
            ->when($appId, function ($q) use ($appId) {
                $q->where('app_id', $appId);
            })
            ->with('app')
            ->orderByDesc('updated_at');

        if ($request->ajax() || $request->wantsJson()) {
            return $this->formatJsonResponse($query->get());
        }

        $devices = $query->get();
        $totalCount   = $devices->count();
        $standbyCount = $devices->where('status', 1)->count();
        $startupCount = $devices->where('status', 0)->count();
        $apps         = Auth::user()->role == 0 ? App::all() : collect();

        $deviceList = $devices->map(function ($device) {
            $isRecent = $device->updated_at && $device->updated_at->diffInSeconds(now()) <= 120;
            return [
                'id'            => $device->id,
                'device_name'   => $device->device_name,
                'app_id'        => $device->app_id,
                'app_name'      => $device->app?->name ?? ('App #' . $device->app_id),
                'status'        => (int) $device->status,
                'status_label'  => (int) $device->status === 1 ? 'Stand by' : 'Startup',
                'status_color'  => (int) $device->status === 1 ? 'green' : 'amber',
                'ip_address'    => $device->ip_address ?? '-',
                'last_seen'     => $device->updated_at ? $device->updated_at->diffForHumans() : '-',
                'updated_at'    => $device->updated_at ? $device->updated_at->format('d/m/Y H:i:s') : '-',
                'is_active'     => $isRecent,
            ];
        });

        return view('master.perangkat.index', compact(
            'devices',
            'deviceList',
            'totalCount',
            'standbyCount',
            'startupCount',
            'appId',
            'apps'
        ));
    }

    /**
     * Dedicated JSON endpoint for polling data every 15 seconds.
     */
    public function data(Request $request)
    {
        $appId = $this->resolveAppId($request);

        $query = Device::query()
            ->when($appId, function ($q) use ($appId) {
                $q->where('app_id', $appId);
            })
            ->with('app')
            ->orderByDesc('updated_at');

        return $this->formatJsonResponse($query->get());
    }

    /**
     * Format device collection into JSON structure.
     */
    protected function formatJsonResponse($devices)
    {
        $items = $devices->map(function ($device) {
            $isRecent = $device->updated_at && $device->updated_at->diffInSeconds(now()) <= 120;
            return [
                'id'            => $device->id,
                'device_name'   => $device->device_name,
                'app_id'        => $device->app_id,
                'app_name'      => $device->app?->name ?? ('App #' . $device->app_id),
                'status'        => (int) $device->status,
                'status_label'  => (int) $device->status === 1 ? 'Stand by' : 'Startup',
                'status_color'  => (int) $device->status === 1 ? 'green' : 'amber',
                'ip_address'    => $device->ip_address ?? '-',
                'last_seen'     => $device->updated_at ? $device->updated_at->diffForHumans() : '-',
                'updated_at'    => $device->updated_at ? $device->updated_at->format('d/m/Y H:i:s') : '-',
                'is_active'     => $isRecent,
            ];
        });

        return response()->json([
            'success'       => true,
            'devices'       => $items,
            'total'         => $items->count(),
            'standby_count' => $items->where('status', 1)->count(),
            'startup_count' => $items->where('status', 0)->count(),
            'server_time'   => now()->format('H:i:s'),
            'timestamp'     => now()->timestamp,
        ]);
    }

    /**
     * Remove the specified device.
     */
    public function destroy(Request $request, string $id)
    {
        $appId = $this->resolveAppId($request);
        $device = Device::when($appId, function ($q) use ($appId) {
            $q->where('app_id', $appId);
        })->findOrFail($id);

        $device->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Perangkat berhasil dihapus.',
            ]);
        }

        return back()->with('success', 'Perangkat berhasil dihapus.');
    }
}
