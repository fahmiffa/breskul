@extends('base.layout')
@section('title', 'Login')
@section('content')
    <div class="min-h-screen flex items-center justify-center w-full">
        <div class="bg-white shadow-md md:w-full max-w-md rounded-3xl md:mx-0 mx-5">
            <div class="p-8">
                <div class="flex my-3 gap-2 items-center justify-center">
                    <img src="{{ asset('icon.png') }}"
                     class="w-25 object-contain" alt="Breskul">

                </div>
                <div class="flex my-3">
                    <div class="text-sm md:text-xl font-bold text-green-600">Login</div>
                </div>
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 text-sm font-semibold mb-2">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                            autocomplete="email" autofocus
                            class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                        @error('email')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="password" class="block text-gray-700 text-sm font-semibold mb-2">Password</label>
                        <div class="relative">
                            <input type="password" name="password" required autocomplete="current-password"
                                class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                            <button type="button" onclick="show(this)"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" id="eyeIcon" class="h-5 w-5" fill="currentColor"
                                    viewBox="0 0 24 24">
                                    <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
                                    <path fill-rule="evenodd"
                                        d="M1.32 11.45C2.81 6.98 7.03 3.75 12 3.75c4.97 0 9.19 3.22 10.68 7.69.12.36.12.75 0 1.11C21.19 17.02 16.97 20.25 12 20.25c-4.97 0-9.19-3.22-10.68-7.69a1.76 1.76 0 0 1 0-1.11ZM17.25 12a5.25 5.25 0 1 1-10.5 0 5.25 5.25 0 0 1 10.5 0Z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>



                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}
                                class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                            <label for="remember" class="ml-2 block text-sm text-gray-900">
                                Remember Me
                            </label>
                        </div>

                        @if (Route::has('password.request'))
                            <a class="inline-block align-baseline text-sm text-blue-500 hover:text-blue-800"
                                href="{{ route('password.request') }}">
                                Forgot Your Password?
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center">
                        <button type="submit"
                            class="bg-[#177245] hover:bg-[#013220] text-white font-bold py-2 px-4 rounded-2xl focus:outline-none focus:shadow-outline w-25 cursor-pointer">
                            Login
                        </button>
                    </div>
                </form>
                {{-- <div class="text-xs flex-col mt-10">
                    <div class="font-semibold">PT MUMTAZ CERIA EDUKASI</div>
                    <div class="font-semibold">Jl. Saditan Baru, Saditan, Brebes, Kec. Brebes, Kabupaten Brebes, Jawa
                        Tengah 52212 (Gg. Flamboyan RT 06/RW 05, Kel. Brebes)
                    </div>
                </div> --}}
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        const eyeIcon = `
        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
            <path fill-rule="evenodd" d="M1.32 11.45C2.81 6.98 7.03 3.75 12 3.75c4.97 0 9.19 3.22 10.68 7.69.12.36.12.75 0 1.11C21.19 17.02 16.97 20.25 12 20.25c-4.97 0-9.19-3.22-10.68-7.69a1.76 1.76 0 0 1 0-1.11ZM17.25 12a5.25 5.25 0 1 1-10.5 0 5.25 5.25 0 0 1 10.5 0Z" clip-rule="evenodd" />
        </svg>`;

        const eyeOffIcon = `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
            <path d="M3.53 2.47a.75.75 0 0 0-1.06 1.06l18 18a.75.75 0 1 0 1.06-1.06l-18-18ZM22.676 12.553a11.249 11.249 0 0 1-2.631 4.31l-3.099-3.099a5.25 5.25 0 0 0-6.71-6.71L7.759 4.577a11.217 11.217 0 0 1 4.242-.827c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113Z" />
            <path d="M15.75 12c0 .18-.013.357-.037.53l-4.244-4.243A3.75 3.75 0 0 1 15.75 12ZM12.53 15.713l-4.243-4.244a3.75 3.75 0 0 0 4.244 4.243Z" />
            <path d="M6.75 12c0-.619.107-1.213.304-1.764l-3.1-3.1a11.25 11.25 0 0 0-2.63 4.31c-.12.362-.12.752 0 1.114 1.489 4.467 5.704 7.69 10.675 7.69 1.5 0 2.933-.294 4.242-.827l-2.477-2.477A5.25 5.25 0 0 1 6.75 12Z" />
        </svg>
        `;

        function show(e) {
            const input = e.parentElement.querySelector('input[type="password"], input[type="text"]');
            if (input) {
                input.type = input.type === 'password' ? 'text' : 'password';
                e.innerHTML = input.type === 'password' ? eyeIcon : eyeOffIcon;
            }
        }
    </script>
@endpush
