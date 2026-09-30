{{-- === AKADEMIK === --}}
@if(auth()->user()->role != 3)
<li class="col-span-full border-b border-gray-200 mt-2 pb-2">
    <h3 class="text-base sm:text-lg font-semibold text-gray-700">Operasional</h3>
</li>
<a href="{{ route('dashboard.pengumuman.index') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.pengumuman.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-file-input-icon lucide-file-input w-5 h-5 sm:w-6 sm:h-6">
                <path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4" />
                <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                <path d="M2 15h10" />
                <path d="m9 18 3-3-3-3" />
            </svg>
        </span> Pengumuman
    </li>
</a>
<a href="{{ route('dashboard.absensi') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.absensi') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-user-round-check-icon lucide-user-round-check w-5 h-5 sm:w-6 sm:h-6">
                <path d="M2 21a8 8 0 0 1 13.292-6" />
                <circle cx="10" cy="8" r="5" />
                <path d="m16 19 2 2 4-4" />
            </svg>
        </span> Absensi
    </li>
</a>
<a href="{{ route('dashboard.master.murid.index') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full cursor-pointer text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.master.murid.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-graduation-cap-icon lucide-graduation-cap w-5 h-5 sm:w-6 sm:h-6">
                <path
                    d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z" />
                <path d="M22 10v6" />
                <path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5" />
            </svg></span>
        {{ config('app.school_mode') ? 'Murid' : 'Mahasiswa' }}
    </li>
</a>
<a href="{{ route('dashboard.master.guru.index') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full cursor-pointer text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.master.guru.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-gpu-icon lucide-gpu w-5 h-5 sm:w-6 sm:h-6">
                <path d="M2 21V3" />
                <path d="M2 5h18a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2.26" />
                <path d="M7 17v3a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1v-3" />
                <circle cx="16" cy="11" r="2" />
                <circle cx="8" cy="11" r="2" />
            </svg></span>
        {{ config('app.school_mode') ? 'Guru' : 'Dosen' }}
    </li>
</a>
<a href="{{ route('dashboard.master.jadwal.index') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.master.jadwal.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-clipboard-list-icon lucide-clipboard-list w-5 h-5 sm:w-6 sm:h-6">
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
<a href="{{ route('dashboard.master.halaqah.index') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.master.halaqah.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-book-open-text-icon lucide-book-open-text w-5 h-5 sm:w-6 sm:h-6">
                <path d="M12 7v14" />
                <path d="M16 12h2" />
                <path d="M16 8h2" />
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
            </svg>
        </span> Halaqah
    </li>
</a>
<a href="{{ route('dashboard.master.akademik.index') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full cursor-pointer text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.master.akademik.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" class="lucide lucide-calendar-sync-icon lucide-calendar-sync w-5 h-5 sm:w-6 sm:h-6">
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
@if(!config('app.school_mode'))
<a href="{{ route('dashboard.master.fakultas.index') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full cursor-pointer text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.master.fakultas.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-building-icon lucide-building w-5 h-5 sm:w-6 sm:h-6">
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
<a href="{{ route('dashboard.master.prodi.index') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full cursor-pointer text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.master.prodi.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-book-open-icon lucide-book-open w-5 h-5 sm:w-6 sm:h-6">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
            </svg>
        </span> Prodi
    </li>
</a>
@endif

{{-- === KEUANGAN === --}}
<li class="col-span-full border-b border-gray-200 mt-2 pb-2">
    <h3 class="text-base sm:text-lg font-semibold text-gray-700">Keuangan</h3>
</li>


<a href="{{ route('dashboard.pay') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.pay') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-banknote-icon lucide-banknote w-5 h-5 sm:w-6 sm:h-6">
                <rect width="20" height="12" x="2" y="6" rx="2" />
                <circle cx="12" cy="12" r="2" />
                <path d="M6 12h.01M18 12h.01" />
            </svg>
        </span> Pembayaran
    </li>
</a>
<a href="{{ route('dashboard.saldo.index') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.saldo.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-wallet-cards w-5 h-5 sm:w-6 sm:h-6">
                <rect width="18" height="18" x="3" y="3" rx="2" />
                <path d="M3 9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2" />
                <path d="M3 11h3c.8 0 1.6.3 2.1.9l1.1 1.2c.5.5 1.2.9 2 .9h7.8" />
            </svg>
        </span> Saldo
    </li>
</a>
<a href="{{ route('dashboard.topup.index') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.topup.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-arrow-up-circle w-5 h-5 sm:w-6 sm:h-6">
                <circle cx="12" cy="12" r="10"/>
                <path d="m16 12-4-4-4 4"/>
                <path d="M12 16V8"/>
            </svg>
        </span> Topup
    </li>
</a>
<a href="{{ route('dashboard.kas.index') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.kas.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-book-text w-5 h-5 sm:w-6 sm:h-6">
                <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"/>
                <path d="M8 11h8"/>
                <path d="M8 7h6"/>
            </svg>
        </span> Kas
    </li>
</a>

{{-- === PENGATURAN === --}}
<li class="col-span-full border-b border-gray-200 mt-2 pb-2">
    <h3 class="text-base sm:text-lg font-semibold text-gray-700">Master</h3>
</li>
<a href="{{ route('dashboard.master.karyawan.index') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full cursor-pointer text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.master.karyawan.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-contact-icon lucide-contact w-5 h-5 sm:w-6 sm:h-6">
                <path d="M16 2v2" />
                <path d="M7 22v-2a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2" />
                <path d="M8 2v2" />
                <circle cx="12" cy="11" r="3" />
                <rect x="3" y="4" width="18" height="18" rx="2" />
            </svg></span>
        Karyawan
    </li>
</a>
<a href="{{ route('dashboard.master.akun.index') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full cursor-pointer text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.master.akun.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" class="lucide lucide-users-icon lucide-users w-5 h-5 sm:w-6 sm:h-6">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                <path d="M16 3.128a4 4 0 0 1 0 7.744" />
                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                <circle cx="9" cy="7" r="4" />
            </svg></span>
        Akun
    </li>
</a>
<a href="{{ route('dashboard.master.pembayaran.index') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full cursor-pointer text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.master.pembayaran.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-credit-card-icon lucide-credit-card w-5 h-5 sm:w-6 sm:h-6">
                <rect width="20" height="14" x="2" y="5" rx="2" />
                <line x1="2" x2="22" y1="10" y2="10" />
            </svg></span>
        Pembayaran
    </li>
</a>
<a href="{{ route('dashboard.master.absensi.index') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.master.absensi.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-clock-icon lucide-clock w-5 h-5 sm:w-6 sm:h-6">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
            </svg>
        </span> Setting Absensi
    </li>
</a>
<a href="{{ route('dashboard.master.semester.index') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full cursor-pointer text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.master.semester.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-calendar-cog-icon lucide-calendar-cog w-5 h-5 sm:w-6 sm:h-6">
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
@if(config('app.school_mode'))
<a href="{{ route('dashboard.master.kelas.index') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.master.kelas.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-school-icon lucide-school w-5 h-5 sm:w-6 sm:h-6">
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
@endif
<a href="{{ route('dashboard.master.mapel.index') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.master.mapel.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-book-check-icon lucide-book-check w-5 h-5 sm:w-6 sm:h-6">
                <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20" />
                <path d="m9 9.5 2 2 4-4" />
            </svg>
        </span> {{ config('app.school_mode') ? 'Mapel' : 'Makul' }}
    </li>
</a>
@if (auth()->user()->role == 0)
<a href="{{ route('dashboard.master.jabatan.index') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.master.jabatan.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" class="lucide lucide-briefcase-icon lucide-briefcase w-5 h-5 sm:w-6 sm:h-6">
                <rect width="20" height="14" x="2" y="7" rx="2" ry="2" />
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
            </svg>
        </span> Jabatan
    </li>
</a>
<a href="{{ route('dashboard.master.api.index') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.api.kelas.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" class="lucide lucide-key-icon lucide-key w-5 h-5 sm:w-6 sm:h-6">
                <path d="m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19 4" />
                <path d="m21 2-9.6 9.6" />
                <circle cx="7.5" cy="15.5" r="5.5" />
            </svg>
        </span> Key
    </li>
</a>
<a href="{{ route('dashboard.master.app.index') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.master.app.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" class="lucide lucide-layout-grid-icon lucide-layout-grid w-5 h-5 sm:w-6 sm:h-6">
                <rect width="7" height="7" x="3" y="3" rx="1" />
                <rect width="7" height="7" x="14" y="3" rx="1" />
                <rect width="7" height="7" x="14" y="14" rx="1" />
                <rect width="7" height="7" x="3" y="14" rx="1" />
            </svg>
        </span> App
    </li>
</a>
@endif
@endif

{{-- === UJIAN (Guru) === --}}
@if (auth()->user()->role == 3)
<li class="col-span-full border-b border-gray-200 mt-2 pb-2">
    <h3 class="text-base sm:text-lg font-semibold text-gray-700">Ujian</h3>
</li>
<a href="{{ route('dashboard.master.soal.index') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full cursor-pointer text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.master.soal.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-file-question-icon lucide-file-question w-5 h-5 sm:w-6 sm:h-6">
                <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
                <path d="M10 10.3c.2-.4.5-.8.9-1a2.1 2.1 0 0 1 2.6.4c.3.4.5.8.5 1.3 0 1.3-2 2-2 2" />
                <path d="M12 17h.01" />
            </svg></span>
        Soal
    </li>
</a>
<a href="{{ route('dashboard.master.ujian.index') }}">
    <li
        class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full cursor-pointer text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.master.ujian.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-clipboard-check-icon lucide-clipboard-check w-5 h-5 sm:w-6 sm:h-6">
                <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                <path d="m9 14 2 2 4-4" />
            </svg></span>
        Ujian
    </li>
</a>
@endif
