<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="{{ asset('icon.png') }}" type="image/png" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="bg-gray-100" x-data="layout()" x-init="init()">
    @if (session('status'))
        <div x-data="{ show: false, message: '' }" x-init="@if (session('status')) message = '{{ session('status') }}';
            show = true;
            setTimeout(() => show = false, 3000); @endif" class="fixed top-4 right-10 z-[100]">
            <div x-show="show" x-transition class="bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg">
                <span x-text="message"></span>
            </div>
        </div>
        <div x-data="{ show: false, message: '' }" x-init="@if (session('err')) message = '{{ session('err') }}';
            show = true;
            setTimeout(() => show = false, 3000); @endif" class="fixed top-50 right-4 z-[100]">
            <div x-show="show" x-transition class="bg-red-600 text-white px-4 py-2 rounded-lg shadow-lg">
                <span x-text="message"></span>
            </div>
        </div>
    @endif

    @if (Route::is('dashboard.*'))
        <!-- HEADER -->
        @include('base.header')

        <!-- MAIN CONTENT -->
        <main class="max-w-7xl mx-auto mt-6 px-6 grid grid-cols-1 md:grid-cols-5 gap-6">

            <!-- SIDEBAR -->
            @include('base.side')

            <!-- CONTENT -->
            <section class="col-span-4">

                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show"
                        class="flex items-center justify-between p-4 mb-3 text-white bg-green-400 rounded-lg shadow"
                        role="alert">
                        <div class="flex gap-2 font-semibold">
                            <span class="font-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-check-icon lucide-check">
                                    <path d="M20 6 9 17l-5-5" />
                                </svg>
                            </span> {{ session('success') }}
                        </div>

                        <button @click="show = false"
                            class="ml-4 text-white hover:text-gray-200 focus:outline-none text-xl leading-none">
                            &times;
                        </button>
                    </div>
                @elseif (session('err'))
                    <div x-data="{ show: true }" x-show="show"
                        class="flex items-center justify-between p-4 mb-3 text-white bg-red-400 rounded-lg shadow"
                        role="alert">
                        <div class="flex gap-2 font-semibold">
                            <span class="font-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-circle-alert-icon lucide-circle-alert">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="12" x2="12" y1="8" y2="12" />
                                    <line x1="12" x2="12.01" y1="16" y2="16" />
                                </svg>
                            </span> {{ session('err') }}
                        </div>

                        <button @click="show = false"
                            class="ml-4 text-white hover:text-gray-200 focus:outline-none text-xl leading-none">
                            &times;
                        </button>
                    </div>
                @elseif(akademik())
                    <div x-data="{ show: true }" x-show="show"
                        class="flex items-center justify-between p-4 mb-3 text-white bg-red-500 rounded-lg shadow"
                        role="alert">
                        <div class="flex gap-2 font-semibold">
                            <span class="font-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-circle-alert-icon lucide-circle-alert">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="12" x2="12" y1="8" y2="12" />
                                    <line x1="12" x2="12.01" y1="16" y2="16" />
                                </svg>
                            </span> {{ akademik() }}
                        </div>

                        <button @click="show = false"
                            class="ml-4 text-white hover:text-gray-200 focus:outline-none text-xl leading-none">
                            &times;
                        </button>
                    </div>
                @endif
                @yield('content')
            </section>

        </main>
    @else
        @yield('content')
    @endif
</body>
@stack('script')

</html>
