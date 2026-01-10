@extends('base.layout')
@section('title', $action)
@section('content')
    <div class="flex flex-col bg-white rounded-lg shadow-md p-6" x-data="extraForm({{ json_encode($students) }}, {{ json_encode($extras) }})">
        <div class="font-semibold mb-3 text-xl">{{ $action }}</div>
        
        <form method="POST" action="{{ route('dashboard.ekstrakurikuler.store') }}" class="grid grid-cols-1">
            @csrf
            
            <div class="mb-4 relative">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Pilih Murid (Bisa pilih lebih dari satu)</label>
                
                {{-- Container Selected Students --}}
                <div class="flex flex-wrap gap-2 mb-3 w-full md:w-1/2">
                    <template x-for="s in selectedStudents" :key="s.id">
                        <div class="bg-green-100 text-green-700 px-3 py-1 rounded-full flex items-center gap-2 text-sm border border-green-200">
                            <span x-text="s.name"></span>
                            <button type="button" @click="removeStudent(s.id)" class="text-green-900 hover:text-red-500 font-bold">&times;</button>
                            <input type="hidden" name="student_ids[]" :value="s.id">
                        </div>
                    </template>
                </div>

                <div class="relative w-full md:w-1/2">
                    <input type="text" x-model="searchStudent" @focus="showDropdown = true" @click.away="showDropdown = false"
                        placeholder="Ketik nama atau NIS murid..."
                        class="border border-gray-300 ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                    
                    <div x-show="showDropdown" class="absolute z-10 w-full bg-white border border-gray-300 rounded-xl mt-1 max-h-60 overflow-y-auto shadow-lg">
                        <template x-for="student in filteredStudents" :key="student.id">
                            <div @click="selectStudent(student)" 
                                class="px-4 py-2 hover:bg-green-100 cursor-pointer border-b border-gray-100 last:border-0">
                                <span class="block font-semibold" x-text="student.name"></span>
                                <span class="block text-xs text-gray-500" x-text="student.nis"></span>
                            </div>
                        </template>
                        <div x-show="filteredStudents.length === 0" class="px-4 py-2 text-gray-500 text-sm">
                            Murid tidak ditemukan atau sudah dipilih.
                        </div>
                    </div>
                </div>
                @error('student_ids')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Pilih Ekstrakurikuler (Bisa pilih lebih dari satu)</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-2">
                    @foreach ($extras as $extra)
                        <label class="flex items-center space-x-2 border rounded-xl px-3 py-2 hover:bg-green-50 cursor-pointer">
                            <input type="checkbox" name="extracurricular_ids[]" value="{{ $extra->id }}" class="text-green-600 focus:ring-green-500">
                            <div>
                                <span class="block font-medium text-gray-800">{{ $extra->nama }}</span>
                                <span class="block text-xs text-gray-500">{{ \Illuminate\Support\Carbon::parse($extra->waktu)->isoFormat('dddd, D MMMM Y Jam HH:mm') }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('extracurricular_ids')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center mt-6">
                <button type="submit" :disabled="selectedStudents.length === 0"
                    class="cursor-pointer bg-green-500 text-sm hover:bg-green-700 text-white font-bold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline disabled:opacity-50">
                    Daftarkan
                </button>
                <a href="{{ route('dashboard.ekstrakurikuler.index') }}" class="ml-2 text-gray-600 hover:text-gray-800">Kembali</a>
            </div>
        </form>
    </div>
@endsection
