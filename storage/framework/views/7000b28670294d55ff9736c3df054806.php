<a href="<?php echo e(route('dashboard.pengumuman.index')); ?>">
    <li
        class="flex items-center px-4 py-3 border-b border-green-300 hover:bg-green-100 <?php echo e(Route::is('dashboard.pengumuman.*') ? 'bg-green-100' : null); ?>">
        <span class="text-green-500 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-file-input-icon lucide-file-input">
                <path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4" />
                <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                <path d="M2 15h10" />
                <path d="m9 18 3-3-3-3" />
            </svg>
        </span> Pengumuman
    </li>
</a>
<a href="<?php echo e(route('dashboard.pay')); ?>">
    <li
        class="flex items-center px-4 py-3 border-b border-green-300 hover:bg-green-100 <?php echo e(Route::is('dashboard.pay') ? 'bg-green-100' : null); ?>">
        <span class="text-green-500 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-banknote-icon lucide-banknote">
                <rect width="20" height="12" x="2" y="6" rx="2" />
                <circle cx="12" cy="12" r="2" />
                <path d="M6 12h.01M18 12h.01" />
            </svg>
        </span> Pembayaran
    </li>
</a>
<a href="<?php echo e(route('dashboard.absensi')); ?>">
    <li
        class="flex items-center px-4 py-3 border-b border-green-300 hover:bg-green-100 <?php echo e(Route::is('dashboard.absensi') ? 'bg-green-100' : null); ?>">
        <span class="text-green-500 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-user-round-check-icon lucide-user-round-check">
                <path d="M2 21a8 8 0 0 1 13.292-6" />
                <circle cx="10" cy="8" r="5" />
                <path d="m16 19 2 2 4-4" />
            </svg>
        </span> Absensi
    </li>
</a>
<a href="<?php echo e(route('dashboard.ekstrakurikuler.index')); ?>">
    <li
        class="flex items-center px-4 py-3 border-b border-green-300 hover:bg-green-100 <?php echo e(Route::is('dashboard.ekstrakurikuler.*') ? 'bg-green-100' : null); ?>">
        <span class="text-green-500 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-users-round-icon lucide-users-round">
                <path d="M18 20a6 6 0 0 0-12 0" />
                <circle cx="12" cy="10" r="4" />
                <circle cx="6" cy="10" r="1" />
                <circle cx="18" cy="10" r="1" />
            </svg>
        </span> <?php echo e(config('app.school_mode') ? 'Ekstrakurikuler' : 'UKM'); ?>

    </li>
</a><?php /**PATH /home/franken/breskull/resources/views/base/home.blade.php ENDPATH**/ ?>