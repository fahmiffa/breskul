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

    /**
     * Get unique devices grouped by device_name (taking the latest record for each device_name).
     */
    protected function getUniqueDevices(?int $appId)
    {
        $latestIds = Device::selectRaw('MAX(id) as id')
            ->when($appId, function ($q) use ($appId) {
                $q->where('app_id', $appId);
            })
            ->groupBy('device_name');

        return Device::whereIn('id', $latestIds)
            ->with('app')
            ->orderByDesc('updated_at')
            ->get()
            ->unique('device_name')
            ->values();
    }

    /**
     * Display a listing of devices.
     */
    public function index(Request $request)
    {
        $appId = Auth::user()->id;
        $devices = $this->getUniqueDevices($appId);

        if ($request->ajax() || $request->wantsJson()) {
            return $this->formatJsonResponse($devices);
        }

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
        $appId = Auth::user()->id;
        $devices = $this->getUniqueDevices($appId);
        return $this->formatJsonResponse($devices);
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
        $appId = Auth::user()->id;
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
