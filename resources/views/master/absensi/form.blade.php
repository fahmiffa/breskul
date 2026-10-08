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
                            <option value="{{ $config->id }}" 
                                data-clock-in-start="{{ $config->clock_in_start }}"
                                data-clock-in-end="{{ $config->clock_in_end }}"
                                data-clock-out-start="{{ $config->clock_out_start }}"
                                data-clock-out-end="{{ $config->clock_out_end }}"
                                data-lat="{{ $config->lat }}"
                                data-lng="{{ $config->lng }}"
                                data-radius="{{ $config->radius }}">
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

                <!-- Clock In -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Jam Masuk (Awal) <span class="text-green-600">WIB</span></label>
                    <input type="time" name="clock_in_start" value="{{ old('clock_in_start', isset($item) ? $item->clock_in_start : '') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Jam Masuk (Akhir) <span class="text-green-600">WIB</span></label>
                    <input type="time" name="clock_in_end" value="{{ old('clock_in_end', isset($item) ? $item->clock_in_end : '') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <!-- Clock Out -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Jam Pulang (Awal) <span class="text-green-600">WIB</span></label>
                    <input type="time" name="clock_out_start" value="{{ old('clock_out_start', isset($item) ? $item->clock_out_start : '') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Jam Pulang (Akhir) <span class="text-green-600">WIB</span></label>
                    <input type="time" name="clock_out_end" value="{{ old('clock_out_end', isset($item) ? $item->clock_out_end : '') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <hr class="md:col-span-2 my-4">

                <!-- Coordinates -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Latitude</label>
                    <input type="text" name="lat" value="{{ old('lat', isset($item) ? $item->lat : '') }}" placeholder="-6.xxxx" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Longitude</label>
                    <input type="text" name="lng" value="{{ old('lng', isset($item) ? $item->lng : '') }}" placeholder="106.xxxx" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Radius (Meter)</label>
                    <input type="number" name="radius" value="{{ old('radius', isset($item) ? $item->radius : '100') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
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
                
                // uncheck if hidden
                if (!show) {
                    item.querySelector('.emp-checkbox').checked = false;
                }
            });
            updateCheckAllStatus();
        }

        function copyConfig(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            if (!selectedOption.value) return;

            const fields = [
                {name: 'clock_in_start', val: selectedOption.getAttribute('data-clock-in-start')},
                {name: 'clock_in_end', val: selectedOption.getAttribute('data-clock-in-end')},
                {name: 'clock_out_start', val: selectedOption.getAttribute('data-clock-out-start')},
                {name: 'clock_out_end', val: selectedOption.getAttribute('data-clock-out-end')},
                {name: 'lat', val: selectedOption.getAttribute('data-lat')},
                {name: 'lng', val: selectedOption.getAttribute('data-lng')},
                {name: 'radius', val: selectedOption.getAttribute('data-radius')}
            ];

            fields.forEach(f => {
                const input = document.querySelector(`input[name="${f.name}"]`);
                if (input && f.val) {
                    input.value = f.val;
                    // Trigger change for time inputs preview
                    if (input.type === 'time') {
                        input.dispatchEvent(new Event('change'));
                    }
                }
            });
        }

        function toggleRoleTarget(role) {
            const section = document.getElementById('karyawan_section');
            if (section) {
                if (role === 'karyawan') {
                    section.style.display = '';
                    filterEmployees();
                } else {
                    section.style.display = 'none';
                    // Uncheck all employees
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
        
        document.querySelectorAll('.emp-checkbox').forEach(cb => {
            cb.addEventListener('change', updateCheckAllStatus);
        });

        // Trigger on load for edited items or old input
        window.addEventListener('load', () => {
            const appSelect = document.getElementById('select_app');
            if (appSelect && appSelect.value) {
                filterJabatanByApp(appSelect.value);
            }

            const roleTarget = document.getElementById('role_target');
            if (roleTarget && roleTarget.value) {
                toggleRoleTarget(roleTarget.value);
            }

            document.querySelectorAll('input[type="time"]').forEach(input => {
                if (input.value) {
                    input.dispatchEvent(new Event('change'));
                }
            });
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
