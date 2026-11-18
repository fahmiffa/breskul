<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class AddRolePermissionMenuSeeder extends Seeder
{
    public function run(): void
    {
        $exists = Menu::where('route_name', 'dashboard.master.role-permission.index')->first();

        if (!$exists) {
            Menu::create([
                'title'        => 'Hak Akses',
                'route_name'   => 'dashboard.master.role-permission.index',
                'permission'   => 'menu.fitur',
                'group_label'  => 'Super Admin',
                'section'      => 'master',
                'sort_order'   => 350,
                'icon'         => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield-check w-5 h-5 sm:w-6 sm:h-6"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>',
            ]);
            $this->command->info('Menu Hak Akses created successfully.');
        } else {
            $this->command->info('Menu Hak Akses already exists.');
        }
    }
}
