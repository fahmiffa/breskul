<?php $__env->startSection('title', $title); ?>
<?php $__env->startPush('styles'); ?>
<style>
    [x-cloak] {
        display: none !important;
    }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
<div class="flex flex-col bg-white rounded-lg shadow-md p-6" x-data="{ ...dataTable(<?php echo e(json_encode($items)); ?>), showImportModal: false, showJob: false, showRfidModal: false, selectedStudentId: null, rfid: null }">

    <div class="mb-4 flex justify-between items-center gap-2">
        <input type="text" x-model="search" placeholder="Pencarian"
            class="w-full md:w-1/2 border border-gray-300  ring-0 rounded-xl px-3 py-2 focus:outline-[#177245]" />

        <div class="flex gap-2 items-center">
            <a href="<?php echo e(route('dashboard.master.murid.create')); ?>"
                class="cursor-pointer bg-green-500 text-xs hover:bg-green-700 text-white font-semibold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline">
                Tambah
            </a>

            <!-- Ubah tombol jadi buka modal -->
            <button @click="showImportModal = true"
                class="cursor-pointer bg-green-500 text-xs hover:bg-green-700 text-white font-semibold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline">
                Import
            </button>
        </div>
    </div>

    <!-- Tabel (tetap sama) -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 text-sm">
            <thead>
                <tr class="bg-green-500 text-left text-white">
                    <th class="px-4 py-2">No</th>
                    <th @click="sortBy('nis')" class="cursor-pointer px-4 py-2"><?php echo e(config('app.school_mode') ? 'NIS' : 'NIM'); ?></th>
                    <th @click="sortBy('name')" class="cursor-pointer px-4 py-2">Nama</th>
                    <th @click="sortBy('jenis')" class="cursor-pointer px-4 py-2">Jenis Kelamin</th>
                    <th class="cursor-pointer px-4 py-2">Alamat</th>
                    <?php if(config('app.qrcode')): ?>
                    <th class="px-4 py-2">QR Code</th>
                    <?php endif; ?>
                    <th class="px-4 py-2">Action</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(row, index) in paginatedData()" :key="index">
                    <tr class="border-t border-gray-300">
                        <td class="px-4 py-2" x-text="((currentPage - 1) * perPage) + index + 1"></td>
                        <td class="px-4 py-2" x-text="row.nis"></td>
                        <td class="px-4 py-2" x-text="row.name"></td>
                        <td class="px-4 py-2" x-text="row.jenis"></td>
                        <td class="px-4 py-2" x-text="row.alamat"></td>
                        <?php if(config('app.qrcode')): ?>
                        <td class="px-4 py-2">
                            <div class="flex flex-col items-center gap-1">
                                <img :src="'/dashboard/master/murid/' + row.id + '/qrcode'" alt="QR Code" class="w-16 h-16 border p-1 bg-white">
                                <a :href="'/dashboard/master/murid/' + row.id + '/qrcode/download'"
                                    class="text-[10px] bg-blue-500 hover:bg-blue-600 text-white px-2 py-0.5 rounded transition-colors">
                                    Download
                                </a>
                            </div>
                        </td>
                        <?php endif; ?>
                        <td class="px-4 py-2 flex items-center gap-1">
                            <a :href="'/dashboard/master/murid/' + row.id + '/edit'"
                                class="text-green-600 hover:text-green-700">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-pencil-icon lucide-pencil">
                                    <path
                                        d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z" />
                                    <path d="m15 5 4 4" />
                                </svg>
                            </a>

                            <form :action="'/dashboard/master/murid/' + row.id" method="POST"
                                @submit.prevent="deleteRow($event)">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="text-red-500 cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="lucide lucide-trash2-icon lucide-trash-2">
                                        <path d="M10 11v6" />
                                        <path d="M14 11v6" />
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                        <path d="M3 6h18" />
                                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                    </svg>
                                </button>
                            </form>

                            <?php if(config('app.uuid')): ?>
                            <button @click="showRfidModal = true; selectedStudentId = row.id; rfid = row.uuid"
                                :class="{
                                        'bg-green-500 hover:bg-green-700': row.uuid,
                                        'bg-red-500 hover:bg-red-700': !row.uuid
                                    }"
                                class="cursor-pointer text-xs text-white font-semibold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline">
                                <div class="flex gap-2 items-center">
                                    UUID
                                </div>
                            </button>
                            <?php endif; ?>

                        </td>
                    </tr>
                </template>
                <tr x-show="filteredData().length === 0">
                    <td colspan="<?php echo e(config('app.qrcode') ? 7 : 6); ?>" class="text-center px-4 py-2 text-gray-500">No results found.</td>
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

    <!-- Modal Import (Background Job + Progress) -->
    <div x-show="showImportModal" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
        x-transition>
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md" @click.away="showImportModal = false">
            <h2 class="text-lg font-semibold mb-4">Import Data <?php echo e(config('app.school_mode') ? 'Murid' : 'Mahasiswa'); ?></h2>

            <div x-data="generateStudentsImport()">
                <div class="mb-4">
                    <input type="file" accept=".xlsx,.xls,.csv" @change="file = $event.target.files[0]"
                        class="border border-gray-300 rounded px-3 py-2 w-full" />
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2"><?php echo e(config('app.school_mode') ? 'Kelas' : 'Prodi'); ?></label>
                    <select x-model="kelas"
                        class="block border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]"
                        required>
                        <option value="">Pilih <?php echo e(config('app.school_mode') ? 'kelas' : 'prodi'); ?></option>
                        <?php if(config('app.school_mode')): ?>
                        <?php $__currentLoopData = $kelas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($row->id); ?>"><?php echo e($row->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                        <?php $prodis = \App\Models\Prodi::latest()->get(); ?>
                        <?php $__currentLoopData = $prodis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($row->id); ?>"><?php echo e($row->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </select>
                </div>

                <form @submit.prevent="submitForm">
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showImportModal = false"
                            class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400" :disabled="isLoading">Batal</button>

                        <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
                            :disabled="isLoading || !file || !kelas">
                            <span x-show="!isLoading">Import</span>
                            <span x-show="isLoading">Memproses...</span>
                        </button>
                    </div>
                </form>

                <div class="w-full bg-gray-200 h-4 mt-4 rounded">
                    <div class="bg-green-500 h-4 rounded transition-all duration-300" :style="{ width: progress + '%' }"></div>
                </div>
                <p class="mt-2 text-sm text-gray-700" x-text="progress + '%' "></p>

                <template x-if="message">
                    <p class="text-green-600 text-sm mt-2" x-text="message"></p>
                </template>

                <template x-if="error">
                    <p class="text-red-600 text-sm mt-2" x-text="error"></p>
                </template>
            </div>
        </div>
    </div>

    <!-- Modal RFID -->
    <div x-show="showRfidModal" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
        x-transition>
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md" @click.away="showRfidModal = false">
            <h2 class="text-lg font-semibold mb-4">UUID</h2>

            <form :action="'/dashboard/master/uuid/' + selectedStudentId" method="POST">
                <?php echo csrf_field(); ?>
                <div class="mb-4">
                    <input type="text" name="rfid" required :value="rfid"
                        class="border border-gray-300 rounded px-3 py-2 w-full focus:outline-[#177245]" />
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" @click="showRfidModal = false"
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showJob" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
        x-transition>
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md" @click.away="showJob = false">
            <h2 class="text-lg font-semibold mb-4">Bill Job</h2>

            <div x-data="generateBill()">
                <form @submit.prevent="submitForm">
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showJob = false"
                            class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
                            :disabled="isLoading">Batal</button>

                        <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
                            :disabled="isLoading">
                            <span x-show="!isLoading">Import</span>
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
<?php echo $__env->make('base.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\project\breskul\web\resources\views/master/murid/index.blade.php ENDPATH**/ ?>