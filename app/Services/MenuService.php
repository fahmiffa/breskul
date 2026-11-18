<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MenuService
{
    /**
     * Ambil menu yang boleh diakses oleh user, dikelompokkan berdasarkan group_label.
     *
     * @param User   $user
     * @param string $section  'home' atau 'master'
     * @return Collection  grouped by group_label
     */
    public function getMenusForUser(User $user, string $section = 'master'): Collection
    {
        $menus = Cache::remember("menus-{$section}", 600, function () use ($section) {
            return Menu::where('is_active', true)
                ->where('section', $section)
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->get();
        });

        $userPermissions = $user->getCachedPermissions();
        $isAdmin = $user->hasRoleName('admin');

        return $menus->filter(function ($menu) use ($user, $userPermissions, $isAdmin) {
            // Admin selalu bisa akses semua
            if ($isAdmin) {
                return $menu->checkVisibility();
            }

            // Jika menu tidak punya permission requirement, tampilkan (tapi tetap cek visibility)
            if (!$menu->permission) {
                return $menu->checkVisibility();
            }

            // Cek permission
            if (!in_array($menu->permission, $userPermissions)) {
                return false;
            }

            // Cek visibility config (feature flags)
            return $menu->checkVisibility();
        })->groupBy('group_label');
    }

    /**
     * Clear cache menus
     */
    public function clearCache(): void
    {
        Cache::forget('menus-home');
        Cache::forget('menus-master');
    }
}
