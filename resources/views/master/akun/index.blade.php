@extends('base.layout')
@section('title', 'Dashboard Absensi')
@push('styles')
<style>
    [x-cloak] {
        display: none !important;
    }
</style>
@endpush
@section('content')
<div class="flex flex-col bg-white rounded-lg shadow-md p-6" x-data="accountManagement({{ json_encode($items) }})">

    <div class="mb-4 flex justify-between items-center gap-2">
        <input type="text" x-model="search" placeholder="Cari Nama"
            class="w-full md:w-1/2 border border-gray-300  ring-0 rounded-xl px-3 py-2 focus:outline-[#177245]" />
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 text-sm">
            <thead>
                <tr class="bg-green-500 text-left text-white">
                    <th class="px-4 py-2">No</th>
                    <th @click="sortBy('name')" class="cursor-pointer px-4 py-2">Nama</th>
                    <th class="px-4 py-2">Username</th>
                    <th class="px-4 py-2">Waktu</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Opsi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(row, index) in paginatedData()" :key="row.id">
                    <tr class="border-t border-gray-300">
                        <td class="px-4 py-2" x-text="((currentPage - 1) * perPage) + index + 1"></td>
                        <td class="px-4 py-2" x-text="row.data ? row.data.name : row.name"></td>
                        <td class="px-4 py-2" x-text="row.username"></td>
                        <td class="px-4 py-2" x-text="row.roles"></td>
                        <td class="px-4 py-2">
                            <span :class="row.status == 1 ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100'"
                                class="px-2 py-1 rounded-full text-xs font-medium"
                                x-text="row.state"></span>
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex gap-2">
                                <button @click="openPasswordModal(row)"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs cursor-pointer">
                                    Edit Password
                                </button>
                                <button @click="updateStatus(row)"
                                    :class="row.status == 1 ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600'"
                                    class="text-white px-2 py-1 rounded text-xs cursor-pointer"
                                    x-text="row.status == 1 ? 'Nonaktifkan' : 'Aktifkan'">
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="filteredData().length === 0">
                    <td colspan="6" class="text-center px-4 py-2 text-gray-500">No results found.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="flex justify-between items-center mt-4">
        <button @click="prevPage()" :disabled="currentPage === 1"
            class="px-3 py-1 text-white rounded bg-green-500 hover:bg-green-600 disabled:opacity-50">Prev</button>
        <span>Halaman <span x-text="currentPage"></span> dari <span x-text="totalPages()"></span></span>
        <button @click="nextPage()" :disabled="currentPage === totalPages()"
            class="px-3 py-1 text-white rounded bg-green-500 hover:bg-green-600 disabled:opacity-50">Next</button>
    </div>

    <!-- Edit Password Modal -->
    <div x-show="passwordModalOpen" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" x-transition>
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md mx-5" @click.away="closePasswordModal()">
            <h3 class="text-lg font-bold mb-4">Edit Password - <span x-text="selectedUserName"></span></h3>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                <input type="password" x-model="newPassword" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500" placeholder="Minimal 6 karakter">
            </div>
            <div class="flex justify-end gap-2">
                <button @click="closePasswordModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 text-sm">Batal</button>
                <button @click="updatePassword()" :disabled="isLoading || newPassword.length < 6" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm disabled:opacity-50">
                    <span x-show="!isLoading">Simpan</span>
                    <span x-show="isLoading">Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection