<?php $__env->startSection('title', 'Master Jadwal'); ?>
<?php $__env->startSection('content'); ?>
    <div class="flex flex-col bg-white rounded-lg shadow-md p-6" x-data="dataTable(<?php echo e(json_encode($items)); ?>)">

        <div class="mb-4 flex justify-between items-center gap-2">
            <input type="text" x-model="search" placeholder="Cari Nama"
                class="w-full md:w-1/2 border border-gray-300  ring-0 rounded-xl px-3 py-2 focus:outline-[#177245]" />

            <a href="<?php echo e(route('dashboard.master.jadwal.create')); ?>"
                class="cursor-pointer bg-green-500 text-xs hover:bg-green-700 text-white font-semibold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline">
                Tambah
            </a>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <template x-for="(row, index) in paginatedData()" :key="row.id">
                <div class="bg-white border  border-gray-300 rounded-2xl shadow-sm p-6 relative overflow-hidden group hover:shadow-md transition-all duration-300">
                    <!-- Header with Name and Actions -->
                    <div class="flex justify-between items-start mb-6 border-b border-gray-300 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="bg-green-100 text-green-600 w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg" 
                                 x-text="((currentPage - 1) * perPage) + index + 1">
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-800" x-text="row.name"></h3>
                                <p class="text-xs text-gray-500">Jadwal Pelajaran</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <!-- Edit button -->
                            <a :href="'/dashboard/master/jadwal/' + row.id + '/edit'"
                                class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-xl transition-all" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z" />
                                    <path d="m15 5 4 4" />
                                </svg>
                            </a>
                            <!-- Delete form -->
                            <form :action="'/dashboard/master/jadwal/' + row.id" method="POST"
                                @submit.prevent="deleteRow($event)">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all" title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
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
                    </div>

                    <!-- Schedule Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                        <template x-for="(daySchedule, index) in row.jadwal" :key="index">
                            <div class="border border-green-100 rounded-xl p-3 bg-green-50/20 hover:bg-green-50 transition-colors h-full">
                                <div class="flex items-center gap-2 mb-3 pb-2 border-b border-green-100">
                                    <span class="font-bold text-gray-700 text-sm uppercase" x-text="['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][daySchedule.hari] || daySchedule.hari"></span>
                                </div>
                                <div class="space-y-2">
                                    <template x-for="(timeSlot, tIndex) in daySchedule.time" :key="tIndex">
                                        <div class="flex flex-col text-xs bg-white p-2 rounded-lg border border-gray-100 shadow-sm relative group/item hover:border-green-200 transition-colors">
                                            <span class="font-bold text-gray-800 line-clamp-3" :title="timeSlot.mapel.name" x-text="timeSlot.mapel.name"></span>
                                            <div class="flex items-center gap-1 text-gray-500 mt-1 font-medium">
                                                <span x-text="timeSlot.start.slice(0,5) + ' - ' + timeSlot.end.slice(0,5)"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
            
            <div x-show="filteredData().length === 0" class="text-center py-12 bg-white rounded-2xl border border-dashed border-gray-300">
                <p class="text-gray-500">Tidak ada jadwal ditemukan.</p>
            </div>
        </div>

        <div class="flex justify-between items-center mt-4">
            <button @click="prevPage()" :disabled="currentPage === 1"
                class="px-3 py-1 text-white rounded bg-green-500 hover:bg-green-600 disabled:opacity-50">Prev</button>

            <span>Halaman <span x-text="currentPage"></span> dari <span x-text="totalPages()"></span></span>

            <button @click="nextPage()" :disabled="currentPage === totalPages()"
                class="px-3 py-1 text-white rounded bg-green-500 hover:bg-green-600 disabled:opacity-50">Next</button>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('base.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/franken/breskull/resources/views/master/jadwal/index.blade.php ENDPATH**/ ?>