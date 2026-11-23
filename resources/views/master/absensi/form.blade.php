@extends('base.layout')
@section('title', $title)
@section('content')
    <div class="flex flex-col bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">{{ $title }}</h2>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ isset($item) ? route('dashboard.master.absensi.update', $item->id) : route('dashboard.master.absensi.store') }}" method="POST">
            @csrf
            @if(isset($item))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if(auth()->user()->role == 0)
                <!-- Aplikasi (Khusus Role 0 Admin) -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Aplikasi <span class="text-red-500">*</span></label>
                    <select name="app" id="select_app" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required onchange="filterJabatanByApp(this.value)">
                        <option value="">-- Pilih Aplikasi --</option>
                        @foreach($apps as $app)
                            <option value="{{ $app->id }}" {{ old('app', $item->app ?? '') == $app->id ? 'selected' : '' }}>
                                {{ $app->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('app') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
                </div>
                @endif

                <!-- Nama -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $item->name ?? '') }}" placeholder="Nama konfigurasi absensi" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    @error('name') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
                </div>

                <!-- Copy Konfigurasi -->
                @if(isset($existingConfigs) && count($existingConfigs) > 0)
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Konfigurasi (Copy) <span class="text-xs font-normal text-gray-500">- Opsional</span></label>
                    <select id="copy_config" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" onchange="copyConfig(this)">
                        <option value="">-- Copy Konfigurasi yang Sudah Ada --</option>
                        @foreach($existingConfigs as $config)
                            <option value="{{ $config->id }}" data-times="{{ json_encode($config->times) }}">
                                {{ $config->name }} {{ $config->employee ? '('.$config->employee->name.')' : '(Murid)' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Role Selection -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Role Target</label>
                    <select name="role_target" id="role_target" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" onchange="toggleRoleTarget(this.value)" {{ isset($item) ? 'disabled' : 'required' }}>
                        <option value="">Pilih Role Target</option>
                        <option value="karyawan" {{ old('role_target') == 'karyawan' || (isset($item) && $item->employee_id !== null) ? 'selected' : '' }}>Karyawan</option>
                        <option value="murid" {{ old('role_target') == 'murid' || (isset($item) && $item->employee_id === null) ? 'selected' : '' }}>Murid</option>
                    </select>
                    @error('role_target') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
                </div>

                @if(isset($item))
                <!-- Readonly Target Information for Edit -->
                <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Karyawan Terpilih</label>
                        <input type="text" disabled value="{{ $item->employee ? $item->employee->name : 'Murid' }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight bg-gray-100 cursor-not-allowed">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Jabatan</label>
                        <input type="text" disabled value="{{ $item->employee && $item->employee->jabatan ? $item->employee->jabatan->name : '-' }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight bg-gray-100 cursor-not-allowed">
                    </div>
                </div>
                @else
                <div id="karyawan_section" class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6" style="display: none;">
                    <!-- Filter Jabatan -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Filter Jabatan</label>
                        <select id="filter_jabatan" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" onchange="filterEmployees()">
                            <option value="">-- Semua Jabatan --</option>
                            @foreach($jabatans as $jabatan)
                                <option value="{{ $jabatan->id }}" data-app="{{ $jabatan->app_id ?? '' }}">
                                    {{ $jabatan->name }} {{ auth()->user()->role == 0 && $jabatan->app ? '(' . $jabatan->app->name . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Karyawan Checkboxes -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Karyawan <span class="text-red-500">*</span></label>
                        
                        <div class="mb-2">
                            <input type="text" id="search_employee" onkeyup="filterEmployees()" placeholder="Cari nama karyawan..." class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 text-sm leading-tight focus:outline-none focus:shadow-outline mb-2">
                        </div>

                        <div class="mb-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" id="check_all_employees" class="form-checkbox h-4 w-4 text-green-600" onchange="toggleCheckAll(this)">
                                <span class="ml-2 text-gray-700 text-sm font-bold">Check All</span>
                            </label>
                        </div>
                        <div id="employee_list" class="max-h-48 overflow-y-auto border p-2 rounded bg-gray-50">
                            @foreach($employees as $emp)
                                <div class="employee-item mb-1" data-app="{{ $emp->app_id }}" data-jabatan="{{ $emp->jabatan_id }}" data-name="{{ strtolower($emp->name) }}">
                                    <label class="inline-flex items-center w-full cursor-pointer hover:bg-gray-100 p-1 rounded">
                                        <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}" class="emp-checkbox form-checkbox h-4 w-4 text-green-600">
                                        <span class="ml-2 text-gray-700 text-sm flex-1">{{ $emp->name }} ({{ $emp->jabatan->name ?? '-' }})</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('employee_ids') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
                    </div>
                </div>
                @endif
            </div>

            <hr class="my-6">

            <!-- Konfigurasi Waktu Per Hari -->
            <div class="mb-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-bold text-gray-800">Konfigurasi Waktu Per Hari</h3>
                    <button type="button" onclick="addDayRow()" class="bg-green-500 hover:bg-green-700 text-white text-xs font-semibold py-2 px-3 rounded-lg focus:outline-none flex items-center gap-1">
                        + Tambah Hari
                    </button>
                </div>
                @error('days') <p class="text-red-500 text-xs italic mb-2">{{ $message }}</p> @enderror

                <div id="day_rows_container" class="space-y-4">
                    {{-- Rows akan di-generate via JS --}}
                </div>

                <div class="mt-4">
                    <button type="button" onclick="addDayRow()" class="w-full py-2.5 border-2 border-dashed border-green-500 hover:border-green-600 hover:bg-green-50 text-green-700 font-semibold text-sm rounded-lg flex items-center justify-center gap-2 transition duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        + Tambah Hari
                    </button>
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
        const allDays = @json($dayList);
        let dayRowIndex = 0;

        @php
            $oldTimes = null;
            if (old('days') && old('times')) {
                $oldTimes = [];
                foreach (old('days') as $k => $day) {
                    if (isset(old('times')[$k])) {
                        $t = old('times')[$k];
                        $t['day'] = $day;
                        $oldTimes[] = $t;
                    }
                }
            }
        @endphp

        // Data yang sudah ada (old input atau edit mode)
        const oldTimes = @json($oldTimes);
        const existingTimes = oldTimes || @json(isset($item) ? $item->times : []);

        function addDayRow(dayData = null) {
            if (dayData instanceof Event) {
                dayData = null;
            }

            const container = document.getElementById('day_rows_container');
            const existingRows = container.querySelectorAll('.day-row');
            const idx = dayRowIndex++;

            const row = document.createElement('div');
            row.className = 'day-row border rounded-lg p-4 bg-gray-50 relative';
            row.setAttribute('data-index', idx);

            let selectedDay = '';
            let clockInStart = '';
            let clockInEnd = '';
            let clockOutStart = '';
            let clockOutEnd = '';
            let lat = '';
            let lng = '';
            let radius = 100;

            if (dayData) {
                selectedDay = dayData.day || '';
                clockInStart = (dayData.clock_in_start || '').substring(0, 5);
                clockInEnd = (dayData.clock_in_end || '').substring(0, 5);
                clockOutStart = (dayData.clock_out_start || '').substring(0, 5);
                clockOutEnd = (dayData.clock_out_end || '').substring(0, 5);
                lat = dayData.lat !== undefined && dayData.lat !== null ? dayData.lat : '';
                lng = dayData.lng !== undefined && dayData.lng !== null ? dayData.lng : '';
                radius = dayData.radius !== undefined && dayData.radius !== null ? dayData.radius : 100;
            } else if (existingRows.length > 0) {
                // Copy dari baris di atasnya (last row)
                const lastRow = existingRows[existingRows.length - 1];

                const lastDaySelect = lastRow.querySelector('select[name^="days"]');
                const lastClockInStart = lastRow.querySelector('input[name*="[clock_in_start]"]');
                const lastClockInEnd = lastRow.querySelector('input[name*="[clock_in_end]"]');
                const lastClockOutStart = lastRow.querySelector('input[name*="[clock_out_start]"]');
                const lastClockOutEnd = lastRow.querySelector('input[name*="[clock_out_end]"]');
                const lastLat = lastRow.querySelector('input[name*="[lat]"]');
                const lastLng = lastRow.querySelector('input[name*="[lng]"]');
                const lastRadius = lastRow.querySelector('input[name*="[radius]"]');

                clockInStart = lastClockInStart ? lastClockInStart.value : '';
                clockInEnd = lastClockInEnd ? lastClockInEnd.value : '';
                clockOutStart = lastClockOutStart ? lastClockOutStart.value : '';
                clockOutEnd = lastClockOutEnd ? lastClockOutEnd.value : '';
                lat = lastLat ? lastLat.value : '';
                lng = lastLng ? lastLng.value : '';
                radius = (lastRadius && lastRadius.value !== '') ? lastRadius.value : 100;

                // Tentukan hari berikutnya dari last row
                const lastDay = lastDaySelect ? lastDaySelect.value : '';
                const usedDays = Array.from(existingRows)
                    .map(r => r.querySelector('select[name^="days"]')?.value)
                    .filter(Boolean);

                if (lastDay) {
                    const lastDayIdx = allDays.indexOf(lastDay);
                    if (lastDayIdx !== -1) {
                        for (let i = 1; i <= allDays.length; i++) {
                            const candidate = allDays[(lastDayIdx + i) % allDays.length];
                            if (!usedDays.includes(candidate)) {
                                selectedDay = candidate;
                                break;
                            }
                        }
                    }
                }

                if (!selectedDay) {
                    selectedDay = allDays.find(d => !usedDays.includes(d)) || '';
                }
            }

            let dayOptions = `<option value="">-- Pilih Hari --</option>`;
            allDays.forEach(d => {
                dayOptions += `<option value="${d}" ${d === selectedDay ? 'selected' : ''}>${d}</option>`;
            });

            row.innerHTML = `
                <button type="button" onclick="removeDayRow(this)" class="absolute top-2 right-2 text-red-500 hover:text-red-700 text-lg font-bold" title="Hapus hari">&times;</button>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-3">
                    <div>
                        <label class="block text-gray-600 text-xs font-semibold mb-1">Hari <span class="text-red-500">*</span></label>
                        <select name="days[${idx}]" required class="border rounded w-full py-2 px-3 text-sm text-gray-700 focus:outline-none">
                            ${dayOptions}
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-xs font-semibold mb-1">Masuk (Awal) <span class="text-green-600">WIB</span></label>
                        <input type="time" name="times[${idx}][clock_in_start]" value="${clockInStart}" required class="border rounded w-full py-2 px-3 text-sm text-gray-700 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-gray-600 text-xs font-semibold mb-1">Masuk (Akhir) <span class="text-green-600">WIB</span></label>
                        <input type="time" name="times[${idx}][clock_in_end]" value="${clockInEnd}" required class="border rounded w-full py-2 px-3 text-sm text-gray-700 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-gray-600 text-xs font-semibold mb-1">Pulang (Awal) <span class="text-green-600">WIB</span></label>
                        <input type="time" name="times[${idx}][clock_out_start]" value="${clockOutStart}" required class="border rounded w-full py-2 px-3 text-sm text-gray-700 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-gray-600 text-xs font-semibold mb-1">Pulang (Akhir) <span class="text-green-600">WIB</span></label>
                        <input type="time" name="times[${idx}][clock_out_end]" value="${clockOutEnd}" required class="border rounded w-full py-2 px-3 text-sm text-gray-700 focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-gray-600 text-xs font-semibold mb-1">Latitude</label>
                        <input type="text" name="times[${idx}][lat]" value="${lat}" placeholder="-6.xxxx" class="border rounded w-full py-2 px-3 text-sm text-gray-700 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-gray-600 text-xs font-semibold mb-1">Longitude</label>
                        <input type="text" name="times[${idx}][lng]" value="${lng}" placeholder="106.xxxx" class="border rounded w-full py-2 px-3 text-sm text-gray-700 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-gray-600 text-xs font-semibold mb-1">Radius (Meter)</label>
                        <input type="number" name="times[${idx}][radius]" value="${radius}" class="border rounded w-full py-2 px-3 text-sm text-gray-700 focus:outline-none">
                    </div>
                </div>
            `;

            container.appendChild(row);
        }

        function removeDayRow(btn) {
            const row = btn.closest('.day-row');
            const container = document.getElementById('day_rows_container');
            if (container.querySelectorAll('.day-row').length > 1) {
                row.remove();
            } else {
                alert('Minimal harus ada 1 konfigurasi hari.');
            }
        }

        function copyConfig(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            if (!selectedOption || !selectedOption.value) return;

            const timesJson = selectedOption.getAttribute('data-times');
            if (!timesJson) return;

            const times = JSON.parse(timesJson);
            if (!times || times.length === 0) return;

            // Hapus semua row lama
            document.getElementById('day_rows_container').innerHTML = '';
            dayRowIndex = 0;

            // Buat row baru dari data copy
            times.forEach(t => addDayRow(t));
        }

        function filterEmployees() {
            const appId = document.getElementById('select_app') ? document.getElementById('select_app').value : null;
            const jabatanId = document.getElementById('filter_jabatan').value;
            const searchInput = document.getElementById('search_employee') ? document.getElementById('search_employee').value.toLowerCase() : '';
            const employeeItems = document.querySelectorAll('.employee-item');

            employeeItems.forEach(item => {
                const itemApp = item.getAttribute('data-app');
                const itemJabatan = item.getAttribute('data-jabatan');
                const itemName = item.getAttribute('data-name');
                
                let show = true;
                if (appId && itemApp && itemApp !== appId) show = false;
                if (jabatanId && itemJabatan !== jabatanId) show = false;
                if (searchInput && itemName && !itemName.includes(searchInput)) show = false;

                item.style.display = show ? '' : 'none';
                if (!show) {
                    item.querySelector('.emp-checkbox').checked = false;
                }
            });
            updateCheckAllStatus();
        }

        function toggleRoleTarget(role) {
            const section = document.getElementById('karyawan_section');
            if (section) {
                if (role === 'karyawan') {
                    section.style.display = '';
                    filterEmployees();
                } else {
                    section.style.display = 'none';
                    document.querySelectorAll('.emp-checkbox').forEach(cb => cb.checked = false);
                    document.getElementById('check_all_employees').checked = false;
                }
            }
        }

        function toggleCheckAll(checkbox) {
            const employeeItems = document.querySelectorAll('.employee-item');
            employeeItems.forEach(item => {
                if (item.style.display !== 'none') {
                    item.querySelector('.emp-checkbox').checked = checkbox.checked;
                }
            });
        }

        function updateCheckAllStatus() {
            const visibleCheckboxes = Array.from(document.querySelectorAll('.employee-item'))
                .filter(item => item.style.display !== 'none')
                .map(item => item.querySelector('.emp-checkbox'));
            
            const checkAllCb = document.getElementById('check_all_employees');
            if (!checkAllCb) return;
            if (visibleCheckboxes.length === 0) {
                checkAllCb.checked = false;
                return;
            }
            const allChecked = visibleCheckboxes.every(cb => cb.checked);
            checkAllCb.checked = allChecked;
        }

        function filterJabatanByApp(appId) {
            const filterJabatan = document.getElementById('filter_jabatan');
            if (filterJabatan) {
                const options = filterJabatan.querySelectorAll('option[data-app]');
                options.forEach(opt => {
                    const optApp = opt.getAttribute('data-app');
                    if (!appId || !optApp || optApp === appId) {
                        opt.style.display = '';
                        opt.disabled = false;
                    } else {
                        opt.style.display = 'none';
                        opt.disabled = true;
                        if (opt.selected) filterJabatan.value = '';
                    }
                });
            }
            filterEmployees();
        }

        document.querySelectorAll('.emp-checkbox').forEach(cb => {
            cb.addEventListener('change', updateCheckAllStatus);
        });

        // Inisialisasi
        window.addEventListener('load', () => {
            const appSelect = document.getElementById('select_app');
            if (appSelect && appSelect.value) {
                filterJabatanByApp(appSelect.value);
            }

            const roleTarget = document.getElementById('role_target');
            if (roleTarget && roleTarget.value) {
                toggleRoleTarget(roleTarget.value);
            }

            // Load existing times atau buat 1 default row
            if (existingTimes && existingTimes.length > 0) {
                existingTimes.forEach(t => addDayRow(t));
            } else {
                addDayRow();
            }
        });
    </script>
@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <style>
        .ts-control { border-radius: 0.25rem; padding: 0.5rem 0.75rem; border-color: #e5e7eb; }
        .ts-wrapper.single .ts-control { background-color: #fff; }
    </style>
@endpush

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('copy_config')) {
                new TomSelect("#copy_config",{
                    create: false,
                    plugins: ['clear_button'],
                    sortField: {
                        field: "text",
                        direction: "asc"
                    }
                });
            }
        });
    </script>
@endpush
@endsection
