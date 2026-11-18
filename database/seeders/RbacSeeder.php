<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        // ========================================
        // 1. BUAT ROLES
        // ========================================
        $rolesData = [
            ['name' => 'admin',     'display_name' => 'Admin'],
            ['name' => 'admin_app', 'display_name' => 'Admin App'],
            ['name' => 'siswa',     'display_name' => 'Siswa'],
            ['name' => 'guru',      'display_name' => 'Guru'],
            ['name' => 'petugas',   'display_name' => 'Petugas'],
            ['name' => 'keuangan',  'display_name' => 'Keuangan'],
            ['name' => 'akademik',  'display_name' => 'Akademik'],
        ];

        $roles = [];
        foreach ($rolesData as $r) {
            $roles[$r['name']] = Role::firstOrCreate(['name' => $r['name']], $r);
        }

        // ========================================
        // 2. BUAT PERMISSIONS
        // ========================================
        $permissionsData = [
            // --- Operasional ---
            ['name' => 'menu.pengumuman',       'display_name' => 'Pengumuman',          'group' => 'Operasional'],
            ['name' => 'menu.absensi',          'display_name' => 'Absensi',             'group' => 'Operasional'],
            ['name' => 'menu.perangkat',        'display_name' => 'Perangkat',           'group' => 'Operasional'],
            ['name' => 'menu.izin',             'display_name' => 'Izin',                'group' => 'Operasional'],
            ['name' => 'menu.murid',            'display_name' => 'Murid/Mahasiswa',     'group' => 'Operasional'],
            ['name' => 'menu.guru',             'display_name' => 'Guru/Dosen',          'group' => 'Operasional'],
            ['name' => 'menu.jadwal',           'display_name' => 'Jadwal',              'group' => 'Operasional'],
            ['name' => 'menu.halaqah',          'display_name' => 'Halaqah',             'group' => 'Operasional'],
            ['name' => 'menu.akademik',         'display_name' => 'Akademik',            'group' => 'Operasional'],
            ['name' => 'menu.fakultas',         'display_name' => 'Fakultas',            'group' => 'Operasional'],
            ['name' => 'menu.prodi',            'display_name' => 'Prodi',               'group' => 'Operasional'],

            // --- Keuangan ---
            ['name' => 'menu.pembayaran',       'display_name' => 'Pembayaran',          'group' => 'Keuangan'],
            ['name' => 'menu.saldo',            'display_name' => 'Saldo',               'group' => 'Keuangan'],
            ['name' => 'menu.topup',            'display_name' => 'Topup',               'group' => 'Keuangan'],
            ['name' => 'menu.kas',              'display_name' => 'Kas',                 'group' => 'Keuangan'],
            ['name' => 'menu.jurnal',           'display_name' => 'Jurnal',              'group' => 'Keuangan'],

            // --- Master ---
            ['name' => 'menu.karyawan',         'display_name' => 'Karyawan',            'group' => 'Master'],
            ['name' => 'menu.akun',             'display_name' => 'Akun',                'group' => 'Master'],
            ['name' => 'menu.bank',             'display_name' => 'Bank',                'group' => 'Master'],
            ['name' => 'menu.master.pembayaran','display_name' => 'Setting Pembayaran',  'group' => 'Master'],
            ['name' => 'menu.absensi.setting',  'display_name' => 'Setting Absensi',     'group' => 'Master'],
            ['name' => 'menu.semester',         'display_name' => 'Semester',            'group' => 'Master'],
            ['name' => 'menu.kelas',            'display_name' => 'Kelas',               'group' => 'Master'],
            ['name' => 'menu.mapel',            'display_name' => 'Mapel/Makul',         'group' => 'Master'],
            ['name' => 'menu.ekstrakurikuler',  'display_name' => 'Ekstrakurikuler',     'group' => 'Master'],
            ['name' => 'menu.panduan',          'display_name' => 'Panduan',             'group' => 'Master'],

            // --- Super Admin ---
            ['name' => 'menu.jabatan',          'display_name' => 'Jabatan',             'group' => 'Super Admin'],
            ['name' => 'menu.api',              'display_name' => 'API Key',             'group' => 'Super Admin'],
            ['name' => 'menu.app',              'display_name' => 'App',                 'group' => 'Super Admin'],
            ['name' => 'menu.fitur',            'display_name' => 'Fitur',               'group' => 'Super Admin'],

            // --- Ujian ---
            ['name' => 'menu.soal',             'display_name' => 'Soal',                'group' => 'Ujian'],
            ['name' => 'menu.ujian',            'display_name' => 'Ujian',               'group' => 'Ujian'],
            ['name' => 'menu.penjadwalan',      'display_name' => 'Penjadwalan Ujian',   'group' => 'Ujian'],
        ];

        $permissions = [];
        foreach ($permissionsData as $p) {
            $permissions[$p['name']] = Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        // ========================================
        // 3. ASSIGN PERMISSIONS KE ROLES
        // ========================================

        // Admin -> semua permission
        $roles['admin']->permissions()->syncWithoutDetaching(
            Permission::pluck('id')->toArray()
        );

        // Admin App -> semua kecuali Super Admin group
        $roles['admin_app']->permissions()->syncWithoutDetaching(
            Permission::where('group', '!=', 'Super Admin')->pluck('id')->toArray()
        );

        // Guru -> Soal, Ujian, Penjadwalan, Panduan
        $roles['guru']->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', [
                'menu.soal', 'menu.ujian', 'menu.penjadwalan', 'menu.panduan',
            ])->pluck('id')->toArray()
        );

        // Siswa -> Penjadwalan (home view), Panduan
        $roles['siswa']->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', [
                'menu.penjadwalan', 'menu.panduan',
            ])->pluck('id')->toArray()
        );

        // Keuangan -> semua keuangan + operasional dasar
        $roles['keuangan']->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', [
                'menu.pembayaran', 'menu.saldo', 'menu.topup', 'menu.kas', 'menu.jurnal',
                'menu.pengumuman', 'menu.absensi', 'menu.izin',
                'menu.murid', 'menu.guru', 'menu.bank',
                'menu.master.pembayaran', 'menu.karyawan', 'menu.akun',
                'menu.panduan',
            ])->pluck('id')->toArray()
        );

        // Akademik -> akademik + operasional
        $roles['akademik']->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', [
                'menu.pengumuman', 'menu.absensi', 'menu.izin',
                'menu.murid', 'menu.guru', 'menu.jadwal', 'menu.akademik',
                'menu.kelas', 'menu.mapel', 'menu.semester', 'menu.halaqah',
                'menu.fakultas', 'menu.prodi', 'menu.ekstrakurikuler',
                'menu.karyawan', 'menu.akun', 'menu.panduan',
            ])->pluck('id')->toArray()
        );

        // Petugas -> absensi + operasional dasar
        $roles['petugas']->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', [
                'menu.pengumuman', 'menu.absensi', 'menu.izin', 'menu.perangkat',
                'menu.murid', 'menu.guru',
                'menu.karyawan', 'menu.akun', 'menu.panduan',
            ])->pluck('id')->toArray()
        );

        // ========================================
        // 4. BUAT MENU ITEMS
        // ========================================
        $this->seedMenus();

        // ========================================
        // 5. MIGRASI USER EXISTING KE RBAC
        // ========================================
        $this->migrateExistingUsers($roles);
    }

    private function seedMenus(): void
    {
        // Hapus menu lama jika ada (untuk re-seed)
        Menu::truncate();

        $sort = 0;

        // =====================
        // SECTION: HOME (untuk siswa/guru - tampil di home sidebar)
        // =====================
        Menu::create([
            'title' => 'Exam', 'route_name' => 'dashboard.penjadwalan-ujian.index',
            'permission' => 'menu.penjadwalan', 'group_label' => 'Ujian', 'section' => 'home',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar-check w-5 h-5 sm:w-6 sm:h-6"><rect width="18" height="18" x="3" y="4" rx="2" ry="2" /><line x1="16" y1="2" x2="16" y2="6" /><line x1="8" y1="2" x2="8" y2="6" /><line x1="3" y1="10" x2="21" y2="10" /><path d="m9 16 2 2 4-4" /></svg>',
        ]);
        Menu::create([
            'title' => 'Panduan', 'route_name' => 'dashboard.panduan',
            'permission' => 'menu.panduan', 'group_label' => 'Ujian', 'section' => 'home',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open-text w-5 h-5 sm:w-6 sm:h-6"><path d="M12 7v14" /><path d="M16 12h2" /><path d="M16 8h2" /><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" /><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" /></svg>',
        ]);

        // =====================
        // SECTION: MASTER - Group Operasional
        // =====================
        $sort = 0;
        Menu::create([
            'title' => 'Pengumuman', 'route_name' => 'dashboard.pengumuman.index',
            'permission' => 'menu.pengumuman', 'group_label' => 'Operasional', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-input w-5 h-5 sm:w-6 sm:h-6"><path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4" /><path d="M14 2v4a2 2 0 0 0 2 2h4" /><path d="M2 15h10" /><path d="m9 18 3-3-3-3" /></svg>',
        ]);
        Menu::create([
            'title' => 'Absensi', 'route_name' => 'dashboard.absensi',
            'permission' => 'menu.absensi', 'group_label' => 'Operasional', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-check w-5 h-5 sm:w-6 sm:h-6"><path d="M2 21a8 8 0 0 1 13.292-6" /><circle cx="10" cy="8" r="5" /><path d="m16 19 2 2 4-4" /></svg>',
        ]);
        Menu::create([
            'title' => 'Perangkat', 'route_name' => 'dashboard.master.perangkat.index',
            'permission' => 'menu.perangkat', 'group_label' => 'Operasional', 'section' => 'master',
            'sort_order' => $sort++,
            'visibility_config' => ['rfid' => true],
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-cpu w-5 h-5 sm:w-6 sm:h-6"><rect width="16" height="16" x="4" y="4" rx="2" /><rect width="6" height="6" x="9" y="9" rx="1" /><path d="M15 2v2" /><path d="M15 20v2" /><path d="M2 15h2" /><path d="M2 9h2" /><path d="M20 15h2" /><path d="M20 9h2" /><path d="M9 2v2" /><path d="M9 20v2" /></svg>',
        ]);
        Menu::create([
            'title' => 'Izin', 'route_name' => 'dashboard.master.izin.index',
            'permission' => 'menu.izin', 'group_label' => 'Operasional', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text w-5 h-5 sm:w-6 sm:h-6"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>',
        ]);
        Menu::create([
            'title' => 'dynamic:murid', 'route_name' => 'dashboard.master.murid.index',
            'permission' => 'menu.murid', 'group_label' => 'Operasional', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-graduation-cap w-5 h-5 sm:w-6 sm:h-6"><path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z" /><path d="M22 10v6" /><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5" /></svg>',
        ]);
        Menu::create([
            'title' => 'dynamic:guru', 'route_name' => 'dashboard.master.guru.index',
            'permission' => 'menu.guru', 'group_label' => 'Operasional', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-gpu w-5 h-5 sm:w-6 sm:h-6"><path d="M2 21V3" /><path d="M2 5h18a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2.26" /><path d="M7 17v3a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1v-3" /><circle cx="16" cy="11" r="2" /><circle cx="8" cy="11" r="2" /></svg>',
        ]);
        Menu::create([
            'title' => 'Jadwal', 'route_name' => 'dashboard.master.jadwal.index',
            'permission' => 'menu.jadwal', 'group_label' => 'Operasional', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-list w-5 h-5 sm:w-6 sm:h-6"><rect width="8" height="4" x="8" y="2" rx="1" ry="1" /><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" /><path d="M12 11h4" /><path d="M12 16h4" /><path d="M8 11h.01" /><path d="M8 16h.01" /></svg>',
        ]);
        Menu::create([
            'title' => 'Halaqah', 'route_name' => 'dashboard.master.halaqah.index',
            'permission' => 'menu.halaqah', 'group_label' => 'Operasional', 'section' => 'master',
            'sort_order' => $sort++,
            'visibility_config' => ['halaqoh' => true],
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open-text w-5 h-5 sm:w-6 sm:h-6"><path d="M12 7v14" /><path d="M16 12h2" /><path d="M16 8h2" /><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" /><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" /></svg>',
        ]);
        Menu::create([
            'title' => 'Akademik', 'route_name' => 'dashboard.master.akademik.index',
            'permission' => 'menu.akademik', 'group_label' => 'Operasional', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar-sync w-5 h-5 sm:w-6 sm:h-6"><path d="M11 10v4h4" /><path d="m11 14 1.535-1.605a5 5 0 0 1 8 1.5" /><path d="M16 2v4" /><path d="m21 18-1.535 1.605a5 5 0 0 1-8-1.5" /><path d="M21 22v-4h-4" /><path d="M21 8.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h4.3" /><path d="M3 10h4" /><path d="M8 2v4" /></svg>',
        ]);
        Menu::create([
            'title' => 'Fakultas', 'route_name' => 'dashboard.master.fakultas.index',
            'permission' => 'menu.fakultas', 'group_label' => 'Operasional', 'section' => 'master',
            'sort_order' => $sort++,
            'visibility_config' => ['school_mode' => false],
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-building w-5 h-5 sm:w-6 sm:h-6"><rect width="16" height="20" x="4" y="2" rx="2" ry="2" /><path d="M9 22v-4h6v4" /><path d="M8 6h.01" /><path d="M16 6h.01" /><path d="M12 6h.01" /><path d="M12 10h.01" /><path d="M12 14h.01" /><path d="M16 10h.01" /><path d="M16 14h.01" /><path d="M8 10h.01" /><path d="M8 14h.01" /></svg>',
        ]);
        Menu::create([
            'title' => 'Prodi', 'route_name' => 'dashboard.master.prodi.index',
            'permission' => 'menu.prodi', 'group_label' => 'Operasional', 'section' => 'master',
            'sort_order' => $sort++,
            'visibility_config' => ['school_mode' => false],
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open w-5 h-5 sm:w-6 sm:h-6"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" /><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" /></svg>',
        ]);

        // =====================
        // SECTION: MASTER - Group Keuangan
        // =====================
        $sort = 100;
        Menu::create([
            'title' => 'Pembayaran', 'route_name' => 'dashboard.pay',
            'permission' => 'menu.pembayaran', 'group_label' => 'Keuangan', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-banknote w-5 h-5 sm:w-6 sm:h-6"><rect width="20" height="12" x="2" y="6" rx="2" /><circle cx="12" cy="12" r="2" /><path d="M6 12h.01M18 12h.01" /></svg>',
        ]);
        Menu::create([
            'title' => 'Saldo', 'route_name' => 'dashboard.saldo.index',
            'permission' => 'menu.saldo', 'group_label' => 'Keuangan', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-wallet-cards w-5 h-5 sm:w-6 sm:h-6"><rect width="18" height="18" x="3" y="3" rx="2" /><path d="M3 9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2" /><path d="M3 11h3c.8 0 1.6.3 2.1.9l1.1 1.2c.5.5 1.2.9 2 .9h7.8" /></svg>',
        ]);
        Menu::create([
            'title' => 'Topup', 'route_name' => 'dashboard.topup.index',
            'permission' => 'menu.topup', 'group_label' => 'Keuangan', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-circle w-5 h-5 sm:w-6 sm:h-6"><circle cx="12" cy="12" r="10"/><path d="m16 12-4-4-4 4"/><path d="M12 16V8"/></svg>',
        ]);
        Menu::create([
            'title' => 'Kas', 'route_name' => 'dashboard.kas.index',
            'permission' => 'menu.kas', 'group_label' => 'Keuangan', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-text w-5 h-5 sm:w-6 sm:h-6"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"/><path d="M8 11h8"/><path d="M8 7h6"/></svg>',
        ]);
        Menu::create([
            'title' => 'Jurnal', 'route_name' => 'dashboard.gnucash.accounts.index',
            'permission' => 'menu.jurnal', 'group_label' => 'Keuangan', 'section' => 'master',
            'sort_order' => $sort++,
            'visibility_config' => ['jurnal' => true],
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open w-5 h-5 sm:w-6 sm:h-6"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>',
        ]);

        // =====================
        // SECTION: MASTER - Group Master
        // =====================
        $sort = 200;
        Menu::create([
            'title' => 'Karyawan', 'route_name' => 'dashboard.master.karyawan.index',
            'permission' => 'menu.karyawan', 'group_label' => 'Master', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-contact w-5 h-5 sm:w-6 sm:h-6"><path d="M16 2v2" /><path d="M7 22v-2a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2" /><path d="M8 2v2" /><circle cx="12" cy="11" r="3" /><rect x="3" y="4" width="18" height="18" rx="2" /></svg>',
        ]);
        Menu::create([
            'title' => 'Akun', 'route_name' => 'dashboard.master.akun.index',
            'permission' => 'menu.akun', 'group_label' => 'Master', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users w-5 h-5 sm:w-6 sm:h-6"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><path d="M16 3.128a4 4 0 0 1 0 7.744" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><circle cx="9" cy="7" r="4" /></svg>',
        ]);
        Menu::create([
            'title' => 'Bank', 'route_name' => 'dashboard.master.bank.index',
            'permission' => 'menu.bank', 'group_label' => 'Master', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-landmark w-5 h-5 sm:w-6 sm:h-6"><line x1="3" y1="22" x2="21" y2="22"/><line x1="6" y1="18" x2="6" y2="11"/><line x1="10" y1="18" x2="10" y2="11"/><line x1="14" y1="18" x2="14" y2="11"/><line x1="18" y1="18" x2="18" y2="11"/><polygon points="12 2 20 7 4 7"/></svg>',
        ]);
        Menu::create([
            'title' => 'Pembayaran', 'route_name' => 'dashboard.master.pembayaran.index',
            'permission' => 'menu.master.pembayaran', 'group_label' => 'Master', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-credit-card w-5 h-5 sm:w-6 sm:h-6"><rect width="20" height="14" x="2" y="5" rx="2" /><line x1="2" x2="22" y1="10" y2="10" /></svg>',
        ]);
        Menu::create([
            'title' => 'Setting Absensi', 'route_name' => 'dashboard.master.absensi.index',
            'permission' => 'menu.absensi.setting', 'group_label' => 'Master', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock w-5 h-5 sm:w-6 sm:h-6"><circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" /></svg>',
        ]);
        Menu::create([
            'title' => 'Semester', 'route_name' => 'dashboard.master.semester.index',
            'permission' => 'menu.semester', 'group_label' => 'Master', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar-cog w-5 h-5 sm:w-6 sm:h-6"><path d="m15.228 16.852-.923-.383" /><path d="m15.228 19.148-.923.383" /><path d="M16 2v4" /><path d="m16.47 14.305.382.923" /><path d="m16.852 20.772-.383.924" /><path d="m19.148 15.228.383-.923" /><path d="m19.53 21.696-.382-.924" /><path d="m20.772 16.852.924-.383" /><path d="m20.772 19.148.924.383" /><path d="M21 10.592V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h6" /><path d="M3 10h18" /><path d="M8 2v4" /><circle cx="18" cy="18" r="3" /></svg>',
        ]);
        Menu::create([
            'title' => 'Panduan', 'route_name' => 'dashboard.panduan',
            'permission' => 'menu.panduan', 'group_label' => 'Master', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open-text w-5 h-5 sm:w-6 sm:h-6"><path d="M12 7v14" /><path d="M16 12h2" /><path d="M16 8h2" /><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" /><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" /></svg>',
        ]);
        Menu::create([
            'title' => 'dynamic:kelas', 'route_name' => 'dashboard.master.kelas.index',
            'permission' => 'menu.kelas', 'group_label' => 'Master', 'section' => 'master',
            'sort_order' => $sort++,
            'visibility_config' => ['school_mode' => true],
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-school w-5 h-5 sm:w-6 sm:h-6"><path d="M14 22v-4a2 2 0 1 0-4 0v4" /><path d="m18 10 3.447 1.724a1 1 0 0 1 .553.894V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-7.382a1 1 0 0 1 .553-.894L6 10" /><path d="M18 5v17" /><path d="m4 6 7.106-3.553a2 2 0 0 1 1.788 0L20 6" /><path d="M6 5v17" /><circle cx="12" cy="9" r="2" /></svg>',
        ]);
        Menu::create([
            'title' => 'dynamic:mapel', 'route_name' => 'dashboard.master.mapel.index',
            'permission' => 'menu.mapel', 'group_label' => 'Master', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-check w-5 h-5 sm:w-6 sm:h-6"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20" /><path d="m9 9.5 2 2 4-4" /></svg>',
        ]);

        // =====================
        // SECTION: MASTER - Group Super Admin
        // =====================
        $sort = 300;
        Menu::create([
            'title' => 'Jabatan', 'route_name' => 'dashboard.master.jabatan.index',
            'permission' => 'menu.jabatan', 'group_label' => 'Super Admin', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-briefcase w-5 h-5 sm:w-6 sm:h-6"><rect width="20" height="14" x="2" y="7" rx="2" ry="2" /><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" /></svg>',
        ]);
        Menu::create([
            'title' => 'Key', 'route_name' => 'dashboard.master.api.index',
            'permission' => 'menu.api', 'group_label' => 'Super Admin', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-key w-5 h-5 sm:w-6 sm:h-6"><path d="m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19 4" /><path d="m21 2-9.6 9.6" /><circle cx="7.5" cy="15.5" r="5.5" /></svg>',
        ]);
        Menu::create([
            'title' => 'App', 'route_name' => 'dashboard.master.app.index',
            'permission' => 'menu.app', 'group_label' => 'Super Admin', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layout-grid w-5 h-5 sm:w-6 sm:h-6"><rect width="7" height="7" x="3" y="3" rx="1" /><rect width="7" height="7" x="14" y="3" rx="1" /><rect width="7" height="7" x="14" y="14" rx="1" /><rect width="7" height="7" x="3" y="14" rx="1" /></svg>',
        ]);
        Menu::create([
            'title' => 'Fitur', 'route_name' => 'dashboard.master.fitur.index',
            'permission' => 'menu.fitur', 'group_label' => 'Super Admin', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layers w-5 h-5 sm:w-6 sm:h-6"><path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/><path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/><path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/></svg>',
        ]);

        // =====================
        // SECTION: MASTER - Group Ujian (untuk Guru)
        // =====================
        $sort = 400;
        Menu::create([
            'title' => 'Soal', 'route_name' => 'dashboard.master.soal.index',
            'permission' => 'menu.soal', 'group_label' => 'Ujian', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-question w-5 h-5 sm:w-6 sm:h-6"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" /><path d="M10 10.3c.2-.4.5-.8.9-1a2.1 2.1 0 0 1 2.6.4c.3.4.5.8.5 1.3 0 1.3-2 2-2 2" /><path d="M12 17h.01" /></svg>',
        ]);
        Menu::create([
            'title' => 'Ujian', 'route_name' => 'dashboard.master.ujian.index',
            'permission' => 'menu.ujian', 'group_label' => 'Ujian', 'section' => 'master',
            'sort_order' => $sort++,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-check w-5 h-5 sm:w-6 sm:h-6"><rect width="8" height="4" x="8" y="2" rx="1" ry="1" /><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" /><path d="m9 14 2 2 4-4" /></svg>',
        ]);
    }

    /**
     * Migrasi user existing: map role integer lama ke role_user pivot
     */
    private function migrateExistingUsers(array $roles): void
    {
        $roleMap = [
            0 => 'admin',
            1 => 'admin_app',
            2 => 'siswa',
            3 => 'guru',
            4 => 'petugas',
            5 => 'keuangan',
            6 => 'akademik',
        ];

        User::chunk(100, function ($users) use ($roles, $roleMap) {
            foreach ($users as $user) {
                $roleName = $roleMap[$user->role] ?? 'siswa';
                if (isset($roles[$roleName])) {
                    $user->rolesRelation()->syncWithoutDetaching([$roles[$roleName]->id]);
                }
            }
        });
    }
}
