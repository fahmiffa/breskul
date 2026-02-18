
<?php $__env->startSection('title', $title); ?>
<?php $__env->startPush('styles'); ?>
<style>
    [x-cloak] {
        display: none !important;
    }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
<div class="flex flex-col bg-white rounded-lg shadow-md p-6" x-data="{ ...dataTable(<?php echo e(json_encode($items)); ?>) }">

    <div class="mb-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="w-full md:w-1/2 relative">
            <input type="text" x-model="search" placeholder="Cari Ujian..."
                class="w-full border border-gray-300 ring-0 rounded-xl px-4 py-2.5 pl-10 focus:outline-[#177245] shadow-sm transition-all" />
            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.3-4.3" />
                </svg>
            </div>
        </div>

        <div class="flex gap-2 items-center">
            <a href="<?php echo e(route('dashboard.master.ujian.create')); ?>"
                class="cursor-pointer bg-green-500 hover:bg-green-600 text-white font-bold py-2.5 px-6 rounded-xl focus:outline-none transition-all duration-200 shadow-lg shadow-green-100 flex items-center gap-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14" />
                    <path d="M12 5v14" />
                </svg>
                Tambah Ujian
            </a>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
        <table class="min-w-full bg-white text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-gray-600 border-b border-gray-200">
                    <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">No</th>
                    <th @click="sortBy('nama')" class="cursor-pointer px-6 py-4 font-bold uppercase tracking-wider text-[11px] flex items-center gap-1"> Nama Ujian <svg x-show="sortColumn === 'nama'" :class="sortDirection === 'asc' ? '' : 'rotate-180'" class="w-3 h-3 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg></th>
                    <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">Mata Pelajaran</th>
                    <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">Jumlah Soal</th>
                    <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <template x-for="(row, index) in paginatedData()" :key="index">
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-gray-500 font-medium" x-text="((currentPage - 1) * perPage) + index + 1"></td>
                        <td class="px-6 py-4 font-bold text-gray-800" x-text="row.nama"></td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 bg-green-50 text-green-700 rounded-full font-semibold text-[11px] border border-green-100" x-text="row.mapel ? row.mapel.name : '-'"></span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-700 font-medium" x-text="row.soal_count"></span>
                                <span class="text-gray-400 text-xs">Pertanyaan</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <a :href="'/dashboard/master/ujian/' + row.id + '/edit'"
                                    class="p-2 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-lg transition-all duration-200 shadow-sm border border-blue-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z" />
                                        <path d="m15 5 4 4" />
                                    </svg>
                                </a>

                                <button @click="selectedRow = row; open = true" type="button" class="p-2 bg-green-50 text-green-600 hover:bg-green-600 hover:text-white rounded-lg transition-all duration-200 shadow-sm border border-green-100 cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </button>

                                <form :action="'/dashboard/master/ujian/' + row.id" method="POST"
                                    @submit.prevent="deleteRow($event)">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="p-2 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-lg transition-all duration-200 shadow-sm border border-red-100 cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M10 11v6" />
                                            <path d="M14 11v6" />
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                            <path d="M3 6h18" />
                                            <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="filteredData().length === 0">
                    <td colspan="5" class="text-center px-6 py-10 text-gray-500">
                        <div class="flex flex-col items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300">
                                <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
                                <polyline points="14.5 2 14.5 7.5 20 7.5" />
                                <path d="M10 13h4" />
                                <path d="M12 11v4" />
                            </svg>
                            <span>Belum ada data ujian.</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    
    <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="open = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-gray-100">
                <div class="bg-white">
                    
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800" id="modal-title" x-text="selectedRow?.nama"></h3>
                            <p class="text-xs text-gray-500 mt-0.5" x-text="selectedRow?.mapel?.name"></p>
                        </div>
                        <button type="button" @click="open = false" class="p-2 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-200 transition-all">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    
                    <div class="px-6 py-6 max-h-[70vh] overflow-y-auto">
                        <div class="space-y-8">
                            <template x-for="(soal, sIndex) in selectedRow?.questions" :key="soal.id">
                                <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 relative group transition-all hover:bg-white hover:shadow-md">
                                    <div class="absolute -top-3 left-6 px-4 py-1 bg-green-500 text-white rounded-full text-[10px] font-bold tracking-widest uppercase" x-text="'Soal #' + (sIndex + 1)"></div>

                                    <div class="space-y-4 pt-1">
                                        
                                        <div class="text-gray-800 font-medium leading-relaxed trix-content prose prose-sm max-w-none" x-html="soal.nama"></div>

                                        
                                        <template x-if="soal.tipe === 'Pilihan ganda'">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                                                <template x-for="opt in ['a', 'b', 'c', 'd', 'e']" :key="opt">
                                                    <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 bg-white" x-show="soal['opsi_' + opt]">
                                                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-[10px] font-bold text-gray-500 uppercase flex-shrink-0" x-text="opt"></span>
                                                        <span class="text-sm text-gray-700" x-text="soal['opsi_' + opt]"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>

                                        
                                        <div class="mt-6 p-4 rounded-xl bg-green-50/50 border border-green-100 flex items-start gap-4">
                                            <div class="p-2 bg-green-500 text-white rounded-lg">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="m9 12 2 2 4-4" />
                                                    <circle cx="12" cy="12" r="10" />
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="text-[10px] font-bold text-green-600 uppercase tracking-widest mb-1">Kunci Jawaban</div>
                                                <div class="text-gray-800 font-bold" x-text="soal.jawaban"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template x-if="selectedRow?.questions?.length === 0">
                                <div class="text-center py-10 text-gray-400">
                                    Tidak ada data soal untuk ujian ini.
                                </div>
                            </template>
                        </div>
                    </div>

                    
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                        <button type="button" @click="open = false" class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-all text-sm">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mt-6 gap-4">
        <div class="text-xs text-gray-500 font-medium">
            Menampilkan <span x-text="paginatedData().length"></span> dari <span x-text="filteredData().length"></span> data
        </div>
        <div class="flex items-center gap-2">
            <button @click="prevPage()" :disabled="currentPage === 1"
                class="px-4 py-2 text-xs font-bold text-gray-600 rounded-xl bg-gray-100 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                Prev
            </button>

            <div class="flex gap-1">
                <template x-for="page in totalPages()" :key="page">
                    <button @click="currentPage = page"
                        :class="currentPage === page ? 'bg-green-500 text-white shadow-md shadow-green-100' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'"
                        class="w-8 h-8 rounded-lg text-xs font-bold transition-all" x-text="page"></button>
                </template>
            </div>

            <button @click="nextPage()" :disabled="currentPage === totalPages()"
                class="px-4 py-2 text-xs font-bold text-gray-600 rounded-xl bg-gray-100 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-1">
                Next
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6" />
                </svg>
            </button>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('base.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\project\breskul\web\resources\views/master/ujian/index.blade.php ENDPATH**/ ?>