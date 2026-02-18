<?php if(auth()->user()->role != 3): ?>
<?php if(config('app.school_mode')): ?>
<a href="<?php echo e(route('dashboard.master.kelas.index')); ?>">
    <li
        class="flex items-center px-4 py-3 border-b border-gray-300 hover:bg-green-100 <?php echo e(Route::is('dashboard.master.kelas.*') ? 'bg-green-100' : null); ?>">
        <span class="text-green-500 mr-2">


            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-school-icon lucide-school">
                <path d="M14 22v-4a2 2 0 1 0-4 0v4" />
                <path
                    d="m18 10 3.447 1.724a1 1 0 0 1 .553.894V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-7.382a1 1 0 0 1 .553-.894L6 10" />
                <path d="M18 5v17" />
                <path d="m4 6 7.106-3.553a2 2 0 0 1 1.788 0L20 6" />
                <path d="M6 5v17" />
                <circle cx="12" cy="9" r="2" />
            </svg>
        </span> Kelas
    </li>
</a>
<?php endif; ?>
<?php endif; ?>
<?php if(auth()->user()->role != 3): ?>
<a href="<?php echo e(route('dashboard.master.mapel.index')); ?>">
    <li
        class="flex items-center px-4 py-3 border-b border-gray-300 hover:bg-green-100 <?php echo e(Route::is('dashboard.master.mapel.*') ? 'bg-green-100' : null); ?>">
        <span class="text-green-500 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-book-check-icon lucide-book-check">
                <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20" />
                <path d="m9 9.5 2 2 4-4" />
            </svg>
        </span> <?php echo e(config('app.school_mode') ? 'Mapel' : 'Makul'); ?>

    </li>
</a>



<a href="<?php echo e(route('dashboard.master.murid.index')); ?>">
    <li
        class="flex items-center px-4 py-3 border-b border-gray-300 hover:bg-green-100 cursor-pointer <?php echo e(Route::is('dashboard.master.murid.*') ? 'bg-green-100' : null); ?>">
        <span class="text-green-500 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-graduation-cap-icon lucide-graduation-cap">
                <path
                    d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z" />
                <path d="M22 10v6" />
                <path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5" />
            </svg></span>
        <?php echo e(config('app.school_mode') ? 'Murid' : 'Mahasiswa'); ?>

    </li>
</a>
<a href="<?php echo e(route('dashboard.master.guru.index')); ?>">
    <li
        class="flex items-center px-4 py-3 border-b border-gray-300 hover:bg-green-100 cursor-pointer <?php echo e(Route::is('dashboard.master.guru.*') ? 'bg-green-100' : null); ?>">
        <span class="text-green-500 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-gpu-icon lucide-gpu">
                <path d="M2 21V3" />
                <path d="M2 5h18a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2.26" />
                <path d="M7 17v3a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1v-3" />
                <circle cx="16" cy="11" r="2" />
                <circle cx="8" cy="11" r="2" />
            </svg></span>
        <?php echo e(config('app.school_mode') ? 'Guru' : 'Dosen'); ?>

    </li>
</a>

<a href="<?php echo e(route('dashboard.master.jadwal.index')); ?>">
    <li
        class="flex items-center px-4 py-3 border-b border-gray-300 hover:bg-green-100 <?php echo e(Route::is('dashboard.master.jadwal.*') ? 'bg-green-100' : null); ?>">
        <span class="text-green-500 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-clipboard-list-icon lucide-clipboard-list">
                <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                <path d="M12 11h4" />
                <path d="M12 16h4" />
                <path d="M8 11h.01" />
                <path d="M8 16h.01" />
            </svg>
        </span> Jadwal
    </li>
</a>
<a href="<?php echo e(route('dashboard.master.absensi.index')); ?>">
    <li
        class="flex items-center px-4 py-3 border-b border-gray-300 hover:bg-green-100 <?php echo e(Route::is('dashboard.master.absensi.*') ? 'bg-green-100' : null); ?>">
        <span class="text-green-500 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-clock-icon lucide-clock">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
            </svg>
        </span> Setting Absensi
    </li>
</a>
<a href="<?php echo e(route('dashboard.master.pembayaran.index')); ?>">
    <li
        class="flex items-center px-4 py-3 border-b border-gray-300 hover:bg-green-100 cursor-pointer  <?php echo e(Route::is('dashboard.master.pembayaran.*') ? 'bg-green-100' : null); ?>">
        <span class="text-green-500 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-credit-card-icon lucide-credit-card">
                <rect width="20" height="14" x="2" y="5" rx="2" />
                <line x1="2" x2="22" y1="10" y2="10" />
            </svg></span>
        Pembayaran
    </li>
</a>
<a href="<?php echo e(route('dashboard.master.semester.index')); ?>">
    <li
        class="flex items-center px-4 py-3 border-b border-gray-300 hover:bg-green-100 cursor-pointer  <?php echo e(Route::is('dashboard.master.semester.*') ? 'bg-green-100' : null); ?>">
        <span class="text-green-500 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-calendar-cog-icon lucide-calendar-cog">
                <path d="m15.228 16.852-.923-.383" />
                <path d="m15.228 19.148-.923.383" />
                <path d="M16 2v4" />
                <path d="m16.47 14.305.382.923" />
                <path d="m16.852 20.772-.383.924" />
                <path d="m19.148 15.228.383-.923" />
                <path d="m19.53 21.696-.382-.924" />
                <path d="m20.772 16.852.924-.383" />
                <path d="m20.772 19.148.924.383" />
                <path d="M21 10.592V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h6" />
                <path d="M3 10h18" />
                <path d="M8 2v4" />
                <circle cx="18" cy="18" r="3" />
            </svg></span>
        Semester
    </li>
</a>
<a href="<?php echo e(route('dashboard.master.akademik.index')); ?>">
    <li
        class="flex items-center px-4 py-3 border-b border-gray-300 hover:bg-green-100 cursor-pointer  <?php echo e(Route::is('dashboard.master.akademik.*') ? 'bg-green-100' : null); ?>">
        <span class="text-green-500 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" class="lucide lucide-calendar-sync-icon lucide-calendar-sync">
                <path d="M11 10v4h4" />
                <path d="m11 14 1.535-1.605a5 5 0 0 1 8 1.5" />
                <path d="M16 2v4" />
                <path d="m21 18-1.535 1.605a5 5 0 0 1-8-1.5" />
                <path d="M21 22v-4h-4" />
                <path d="M21 8.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h4.3" />
                <path d="M3 10h4" />
                <path d="M8 2v4" />
            </svg></span>
        Akademik
    </li>
</a>
<a href="<?php echo e(route('dashboard.master.ekstrakurikuler.index')); ?>">
    <li
        class="flex items-center px-4 py-3 border-b border-gray-300 hover:bg-green-100 cursor-pointer  <?php echo e(Route::is('dashboard.master.ekstrakurikuler.*') ? 'bg-green-100' : null); ?>">
        <span class="text-green-500 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-rocket-icon lucide-rocket">
                <path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z" />
                <path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z" />
                <path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0" />
                <path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5" />
            </svg></span>
        <?php echo e(config('app.school_mode') ? 'Ekstrakurikuler' : 'UKM'); ?>

    </li>
</a>
<?php endif; ?> <?php if(auth()->user()->role == 3): ?>
<a href="<?php echo e(route('dashboard.master.soal.index')); ?>">
    <li
        class="flex items-center px-4 py-3 border-b border-gray-300 hover:bg-green-100 cursor-pointer  <?php echo e(Route::is('dashboard.master.soal.*') ? 'bg-green-100' : null); ?>">
        <span class="text-green-500 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-file-question-icon lucide-file-question">
                <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
                <path d="M10 10.3c.2-.4.5-.8.9-1a2.1 2.1 0 0 1 2.6.4c.3.4.5.8.5 1.3 0 1.3-2 2-2 2" />
                <path d="M12 17h.01" />
            </svg></span>
        Soal
    </li>
</a>
<a href="<?php echo e(route('dashboard.master.ujian.index')); ?>">
    <li
        class="flex items-center px-4 py-3 border-b border-gray-300 hover:bg-green-100 cursor-pointer  <?php echo e(Route::is('dashboard.master.ujian.*') ? 'bg-green-100' : null); ?>">
        <span class="text-green-500 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-clipboard-check-icon lucide-clipboard-check">
                <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                <path d="m9 14 2 2 4-4" />
            </svg></span>
        Ujian
    </li>
</a>
<?php endif; ?>
<?php if(auth()->user()->role != 3): ?>
<a href="<?php echo e(route('dashboard.master.akun.index')); ?>">
    <li
        class="flex items-center px-4 py-3 border-b border-gray-300 hover:bg-green-100 cursor-pointer  <?php echo e(Route::is('dashboard.master.akun.*') ? 'bg-green-100' : null); ?>">
        <span class="text-green-500 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" class="lucide lucide-users-icon lucide-users">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                <path d="M16 3.128a4 4 0 0 1 0 7.744" />
                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                <circle cx="9" cy="7" r="4" />
            </svg></span>
        Akun
    </li>
</a>
<?php if(!config('app.school_mode')): ?>
<a href="<?php echo e(route('dashboard.master.fakultas.index')); ?>">
    <li
        class="flex items-center px-4 py-3 border-b border-gray-300 hover:bg-green-100 cursor-pointer <?php echo e(Route::is('dashboard.master.fakultas.*') ? 'bg-green-100' : null); ?>">
        <span class="text-green-500 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-building-icon lucide-building">
                <rect width="16" height="20" x="4" y="2" rx="2" ry="2" />
                <path d="M9 22v-4h6v4" />
                <path d="M8 6h.01" />
                <path d="M16 6h.01" />
                <path d="M12 6h.01" />
                <path d="M12 10h.01" />
                <path d="M12 14h.01" />
                <path d="M16 10h.01" />
                <path d="M16 14h.01" />
                <path d="M8 10h.01" />
                <path d="M8 14h.01" />
            </svg>
        </span> Fakultas
    </li>
</a>
<a href="<?php echo e(route('dashboard.master.prodi.index')); ?>">
    <li
        class="flex items-center px-4 py-3 border-b border-gray-300 hover:bg-green-100 cursor-pointer <?php echo e(Route::is('dashboard.master.prodi.*') ? 'bg-green-100' : null); ?>">
        <span class="text-green-500 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-book-open-icon lucide-book-open">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
            </svg>
        </span> Prodi
    </li>
</a>
<?php endif; ?>
<?php if(auth()->user()->role == 0): ?>
<a href="<?php echo e(route('dashboard.master.api.index')); ?>">
    <li
        class="flex items-center px-4 py-3 border-b border-gray-300 hover:bg-green-100 <?php echo e(Route::is('dashboard.api.kelas.*') ? 'bg-green-100' : null); ?>">
        <span class="text-green-500 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" class="lucide lucide-key-icon lucide-key">
                <path d="m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19 4" />
                <path d="m21 2-9.6 9.6" />
                <circle cx="7.5" cy="15.5" r="5.5" />
            </svg>
        </span> Key
    </li>
</a>
<a href="<?php echo e(route('dashboard.master.app.index')); ?>">
    <li
        class="flex items-center px-4 py-3 border-b border-gray-300 hover:bg-green-100 <?php echo e(Route::is('dashboard.master.app.*') ? 'bg-green-100' : null); ?>">
        <span class="text-green-500 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" class="lucide lucide-layout-grid-icon lucide-layout-grid">
                <rect width="7" height="7" x="3" y="3" rx="1" />
                <rect width="7" height="7" x="14" y="3" rx="1" />
                <rect width="7" height="7" x="14" y="14" rx="1" />
                <rect width="7" height="7" x="3" y="14" rx="1" />
            </svg>
        </span> App
    </li>
</a>
<?php endif; ?>
<?php endif; ?><?php /**PATH E:\project\breskul\web\resources\views/base/master.blade.php ENDPATH**/ ?>