@if(auth()->user()->role == 3)
<li class="col-span-full border-b border-gray-200 mt-2 pb-2">
    <h3 class="text-base sm:text-lg font-semibold text-gray-700">Ujian</h3>
</li>
<a href="{{ route('dashboard.penjadwalan-ujian.index') }}">
    <li class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.penjadwalan-ujian.*') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar-check w-5 h-5 sm:w-6 sm:h-6">
                <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                <line x1="16" y1="2" x2="16" y2="6" />
                <line x1="8" y1="2" x2="8" y2="6" />
                <line x1="3" y1="10" x2="21" y2="10" />
                <path d="m9 16 2 2 4-4" />
            </svg>
        </span> Exam
    </li>
</a>
<a href="{{ route('dashboard.panduan') }}">
    <li class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full text-xs sm:text-sm font-medium transition-colors {{ Route::is('dashboard.panduan') ? 'bg-green-100' : null }}">
        <span class="text-green-500 mb-1.5 sm:mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open-text w-5 h-5 sm:w-6 sm:h-6">
                <path d="M12 7v14" />
                <path d="M16 12h2" />
                <path d="M16 8h2" />
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
            </svg>
        </span> Panduan
    </li>
</a>
@endif
