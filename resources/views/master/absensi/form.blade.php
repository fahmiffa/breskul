@extends('base.layout')
@section('title', $title)
@section('content')
    <div class="flex flex-col bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">{{ $title }}</h2>

        <form action="{{ isset($item) ? route('dashboard.master.absensi.update', $item->id) : route('dashboard.master.absensi.store') }}" method="POST">
            @csrf
            @if(isset($item))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Role Selection -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Role</label>
                    <select name="role" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">Pilih Role</option>
                        <option value="3" {{ (isset($item) && $item->role == 3) ? 'selected' : '' }}>Guru</option>
                        <option value="2" {{ (isset($item) && $item->role == 2) ? 'selected' : '' }}>Murid</option>
                    </select>
                    @error('role') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
                </div>



                <!-- Clock In -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Jam Masuk (Awal) <span class="text-green-600">WIB</span></label>
                    <input type="time" name="clock_in_start" value="{{ isset($item) ? $item->clock_in_start : '' }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Jam Masuk (Akhir) <span class="text-green-600">WIB</span></label>
                    <input type="time" name="clock_in_end" value="{{ isset($item) ? $item->clock_in_end : '' }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <!-- Clock Out -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Jam Pulang (Awal) <span class="text-green-600">WIB</span></label>
                    <input type="time" name="clock_out_start" value="{{ isset($item) ? $item->clock_out_start : '' }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Jam Pulang (Akhir) <span class="text-green-600">WIB</span></label>
                    <input type="time" name="clock_out_end" value="{{ isset($item) ? $item->clock_out_end : '' }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <hr class="md:col-span-2 my-4">

                <!-- Coordinates -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Latitude</label>
                    <input type="text" name="lat" value="{{ isset($item) ? $item->lat : '' }}" placeholder="-6.xxxx" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Longitude</label>
                    <input type="text" name="lng" value="{{ isset($item) ? $item->lng : '' }}" placeholder="106.xxxx" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Radius (Meter)</label>
                    <input type="number" name="radius" value="{{ isset($item) ? $item->radius : '100' }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
            </div>

            <div class="flex items-center justify-end mt-4">
                <a href="{{ route('dashboard.master.absensi.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2 focus:outline-none focus:shadow-outline">Batal</a>
                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Simpan
                </button>
            </div>
        </form>
    </div>

    <script>
        document.querySelectorAll('input[type="time"]').forEach(input => {
            input.addEventListener('change', function() {
                const label = this.previousElementSibling;
                const value = this.value;
                if (value) {
                    const previewId = 'preview-' + this.name;
                    let preview = document.getElementById(previewId);
                    if (!preview) {
                        preview = document.createElement('span');
                        preview.id = previewId;
                        preview.className = 'ml-2 text-xs font-normal text-blue-600 italic';
                        label.appendChild(preview);
                    }
                    preview.textContent = '(Set ke ' + value + ' WIB)';
                }
            });
        });

        // Trigger on load for edited items
        window.addEventListener('load', () => {
            document.querySelectorAll('input[type="time"]').forEach(input => {
                if (input.value) {
                    input.dispatchEvent(new Event('change'));
                }
            });
        });
    </script>
@endsection
