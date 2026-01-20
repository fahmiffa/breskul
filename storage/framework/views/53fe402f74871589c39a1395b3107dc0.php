<?php $__env->startSection('title', $title); ?>
<?php $__env->startPush('styles'); ?>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
    <div class="flex flex-col bg-white rounded-lg shadow-md p-6" x-data="{
        ...dataTable(<?php echo e(json_encode($items)); ?>)
    }">
        <div class="mb-4 flex justify-between items-center gap-2">
            <input type="text" x-model="search" placeholder="Pencarian"
                class="w-full md:w-1/2 border border-gray-300  ring-0 rounded-xl px-3 py-2 focus:outline-[#177245]" />

            <div class="flex gap-2">
                <button @click="showTambahKelas = true" :disabled="selectedItems.length === 0"
                    class="cursor-pointer bg-blue-500 text-xs hover:bg-blue-700 text-white font-semibold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline disabled:opacity-50 disabled:cursor-not-allowed">
                    Kelas
                </button>

                <?php if($akademik): ?>
                    <button @click="showJob = true"
                        class="cursor-pointer bg-green-500 text-xs hover:bg-green-700 text-white font-semibold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline">
                        Import
                    </button>
                <?php endif; ?>
            </div>

        </div>

        <div class="overflow-x-auto">
            <div class="flex items-center gap-2 mb-3">
                <button @click="toggleAll()"
                    class="cursor-pointer bg-blue-500 text-xs hover:bg-blue-700 text-white font-semibold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-text="selectedItems.length > 0 ? 'Batalkan Semua' : 'Pilih Semua'"></span>
                </button>
                <div class="text-sm text-red-500">Murid Terpilih : <span x-text="selectedItems.length"></span></div>
            </div>
            <table class="min-w-full bg-white border border-gray-200 text-sm">
                <thead>
                    <tr class="bg-green-500 text-left text-white">
                        <th class="px-4 py-2">
                            Opsi
                        </th>
                        <th class="px-4 py-2">No</th>
                        <th @click="sortBy('nis')" class="cursor-pointer px-4 py-2">NIS</th>
                        <th @click="sortBy('name')" class="cursor-pointer px-4 py-2">Nama</th>
                        <th class="cursor-pointer px-4 py-2">Data</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, index) in paginatedData()" :key="index">
                        <tr class="border-t border-gray-300">
                            <td class="px-4 py-2">
                                <input type="checkbox" 
                                    :checked="selectedItems.includes(row.id)" :value="row.id"
                                    @change="toggleItem(row.id, $event)" class="rounded">
                            </td>
                            <td class="px-4 py-2" x-text="((currentPage - 1) * perPage) + index + 1"></td>
                            <td class="px-4 py-2" x-text="row.nis"></td>
                            <td class="px-4 py-2" x-text="row.name"></td>
                            <td class="px-4 py-2">
                                <div class="flex space-x-4 items-start">
                                    <!-- Kelas -->
                                    <div class="flex flex-col">
                                        <div class="font-semibold">Kelas</div>
                                        <template x-for="(val, index) in row.kelas || []" :key="index">
                                            <span class="text-sm" x-text="val.name"></span>
                                        </template>
                                    </div>

                                    <!-- Akademik -->
                                    <div class="flex flex-col">
                                        <div class="font-semibold">Semester</div>
                                        <template x-for="(val, index) in row.academics || []" :key="index">
                                            <span class="text-sm" x-text="val.name"></span>
                                        </template>
                                    </div>
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

        <!-- Modal Tambah Kelas -->
        <div x-show="showTambahKelas" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
            x-transition>
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md mx-5 md:mx-0"
                @click.away="showTambahKelas = false">
                <h2 class="text-lg font-semibold mb-4">Set Kelas untuk <span x-text="selectedItems.length"></span> Murid
                </h2>

                <form @submit.prevent="assignClass">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Kelas</label>
                        <select x-model="selectedClass" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Kelas</option>
                            <?php $__currentLoopData = \App\Models\Classes::latest()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($class->id); ?>"><?php echo e($class->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showTambahKelas = false"
                            class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400" :disabled="isLoading">Batal</button>

                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                            :disabled="isLoading || !selectedClass">
                            <span x-show="!isLoading">Simpan</span>
                            <span x-show="isLoading">Memproses...</span>
                        </button>
                    </div>
                </form>

                <!-- Notification -->
                <template x-if="message">
                    <p class="text-green-600 text-sm mt-2" x-text="message"></p>
                </template>

                <template x-if="error">
                    <p class="text-red-600 text-sm mt-2" x-text="error"></p>
                </template>
            </div>
        </div>

        <!-- Modal Import -->
        <div x-show="showJob" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" x-transition>
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md mx-5 md:mx-0" @click.away="showJob = false">
                <h2 class="text-lg font-semibold mb-4">Import Semua murid ke <?php echo e($akademik ? $akademik->name : null); ?></h2>

                <div x-data="generateImport()">
                    <form @submit.prevent="submitForm">
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="showJob = false"
                                class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
                                :disabled="isLoading">Batal</button>

                            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
                                :disabled="isLoading">
                                <span x-show="!isLoading">Simpan</span>
                                <span x-show="isLoading">Memproses...</span>
                            </button>
                        </div>
                    </form>

                    <!-- Progress bar -->
                    <div class="w-full bg-gray-200 h-4 mt-4 rounded">
                        <div class="bg-green-500 h-4 rounded transition-all duration-300"
                            :style="{ width: progress + '%' }"></div>
                    </div>
                    <p class="mt-2 text-sm text-gray-700" x-text="progress + '%'"></p>

                    <!-- Notification -->
                    <template x-if="message">
                        <p class="text-green-600 text-sm mt-2" x-text="message"></p>
                    </template>

                    <template x-if="error">
                        <p class="text-red-600 text-sm mt-2" x-text="error"></p>
                    </template>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('base.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/franken/breskull/resources/views/master/akademik/home.blade.php ENDPATH**/ ?>