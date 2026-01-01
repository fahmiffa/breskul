@extends('base.layout')
@section('title', 'Dashboard')
@section('content')
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4">
        {{-- <div class="bg-white rounded-lg shadow-md p-4 text-center">
            <div class="w-10 h-10 mx-auto mb-2 bg-orange-500 text-white rounded-lg flex items-center justify-center"
             x-data="countUp(100)"
             x-init="start()"
            >
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
            </div>
            <p class="text-gray-500 tex-sm font-semibold">Kelas</p>
            <p class="text-blue-900 font-bold text-lg">100</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-4 text-center">
            <div class="w-10 h-10 mx-auto mb-2 bg-orange-500 text-white rounded-lg flex items-center justify-center">
                <!-- User Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-layout-grid-icon lucide-layout-grid">
                    <rect width="7" height="7" x="3" y="3" rx="1" />
                    <rect width="7" height="7" x="14" y="3" rx="1" />
                    <rect width="7" height="7" x="14" y="14" rx="1" />
                    <rect width="7" height="7" x="3" y="14" rx="1" />
                </svg>
            </div>
            <p class="text-gray-500 text-sm">Unit</p>
            <p class="text-blue-900 font-bold text-lg">183.000</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-4 text-center">
            <div class="w-10 h-10 mx-auto mb-2 bg-orange-500 text-white rounded-lg flex items-center justify-center">
                <!-- User Plus Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-graduation-cap-icon lucide-graduation-cap">
                    <path
                        d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z" />
                    <path d="M22 10v6" />
                    <path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5" />
                </svg>
            </div>
            <p class="text-gray-500 text-sm">Murid</p>
            <p class="text-blue-900 font-bold text-lg">80.000</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-4 text-center">
            <div class="w-10 h-10 mx-auto mb-2 bg-orange-500 text-white rounded-lg flex items-center justify-center">
                <!-- Bookmark Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-gpu-icon lucide-gpu">
                    <path d="M2 21V3" />
                    <path d="M2 5h18a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2.26" />
                    <path d="M7 17v3a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1v-3" />
                    <circle cx="16" cy="11" r="2" />
                    <circle cx="8" cy="11" r="2" />
                </svg>
            </div>
            <p class="text-gray-500 text-sm">Guru</p>
            <p class="text-blue-900 font-bold text-lg">112</p>
        </div> --}}
    </div>
@endsection
