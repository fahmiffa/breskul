@extends('base.layout')
@section('title', 'Izin Karyawan/Guru')
@section('content')
<div class="flex flex-col bg-white rounded-lg shadow-md p-6" x-data="dataTable({{ json_encode($items) }})">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
        <h2 class="text-2xl font-bold text-gray-800">Data Izin</h2>
        <div class="relative w-full sm:w-64">
            <input x-model="search" type="text" placeholder="Cari izin..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-500 transition-all duration-300">
            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
        <table class="min-w-full whitespace-nowrap">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Karyawan/Guru</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Keterangan</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <template x-for="(row, index) in paginatedRows" :key="row.id">
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-6 py-4 text-sm text-gray-700" x-text="(currentPage - 1) * perPage + index + 1"></td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900" x-text="row.employee ? row.employee.name : '-'"></div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700" x-text="row.tanggal ?? '-'"></td>
                        <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate" x-text="row.keterangan"></td>
                        <td class="px-6 py-4">
                            <span x-show="row.status == 3" class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                            <span x-show="row.status == 1" class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Berhasil</span>
                            <span x-show="row.status == 2" class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center items-center space-x-2">
                                <form :action="'{{ url('dashboard/master/izin') }}/' + row.id" method="POST" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="1">
                                    <button type="submit" class="p-1.5 text-white bg-green-500 rounded-lg hover:bg-green-600 transition-colors" title="Terima">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                    </button>
                                </form>
                                <form :action="'{{ url('dashboard/master/izin') }}/' + row.id" method="POST" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="2">
                                    <button type="submit" class="p-1.5 text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors" title="Tolak">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="paginatedRows.length === 0">
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                            <span class="text-lg font-medium">Tidak ada data izin ditemukan</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('dataTable', (initialData) => ({
            rows: initialData,
            search: '',
            sortColumn: 'id',
            sortAsc: false,
            currentPage: 1,
            perPage: 10,
            
            get filteredRows() {
                let filtered = this.rows;
                if (this.search) {
                    filtered = filtered.filter(row => {
                        return (row.employee && row.employee.name && row.employee.name.toLowerCase().includes(this.search.toLowerCase())) ||
                               (row.keterangan && row.keterangan.toLowerCase().includes(this.search.toLowerCase()));
                    });
                }
                return filtered;
            },
            get paginatedRows() {
                const start = (this.currentPage - 1) * this.perPage;
                const end = start + this.perPage;
                return this.filteredRows.slice(start, end);
            },
            get totalPages() {
                return Math.ceil(this.filteredRows.length / this.perPage);
            }
        }));
    });
</script>
@endsection
