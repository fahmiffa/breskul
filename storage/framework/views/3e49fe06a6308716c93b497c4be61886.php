<?php $__env->startSection('title', $title); ?>
<?php $__env->startPush('styles'); ?>
<style>
    [x-cloak] {
        display: none !important;
    }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
<div class="flex flex-col bg-white rounded-lg shadow-md p-6" x-data="verificationPayment(<?php echo e(json_encode($items)); ?>)">
    <div class="mb-4 flex flex-wrap justify-between items-center gap-2">
        <div class="flex gap-2 w-full md:w-2/3">
            <input type="text" x-model="search" placeholder="Pencarian"
                class="w-full border border-gray-300 ring-0 rounded-xl px-3 py-2 focus:outline-[#177245]" />
            <select x-model="selectedKelas"
                class="w-full md:w-1/3 border border-gray-300 ring-0 rounded-xl px-3 py-2 focus:outline-[#177245]">
                <option value="">Semua <?php echo e(config('app.school_mode') ? 'Kelas' : 'Prodi'); ?></option>
                <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kelas): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($kelas->name); ?>"><?php echo e($kelas->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div class="flex gap-2">
            <button @click="showTambahKelas = true" :disabled="selectedItems.length === 0"
                class="cursor-pointer bg-blue-500 text-xs hover:bg-blue-700 text-white font-semibold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline disabled:opacity-50 disabled:cursor-not-allowed">
                Tambah Pembayaran
            </button>

        </div>

    </div>

    <div class="overflow-x-auto">
        <div class="flex items-center gap-2 mb-3">
            <button @click="selectAll()"
                class="cursor-pointer bg-blue-500 text-xs hover:bg-blue-700 text-white font-semibold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-text="selectedItems.length > 0 ? 'Batalkan Semua' : 'Pilih Semua'"></span>
            </button>
            <div class="text-sm text-red-500"><?php echo e(config('app.school_mode') ? 'Murid' : 'Mahasiswa'); ?> Terpilih : <span x-text="selectedItems.length"></span></div>
        </div>
        <table class="min-w-full bg-white border border-gray-200 text-sm">
            <thead>
                <tr class="bg-green-500 text-left text-white">
                    <th class="px-4 py-2">
                        Opsi
                    </th>
                    <th @click="sortBy('nis')" class="cursor-pointer px-4 py-2"><?php echo e(config('app.school_mode') ? 'NIS' : 'NIM'); ?></th>
                    <th @click="sortBy('name')" class="cursor-pointer px-4 py-2">Nama</th>
                    <th @click="sortBy('kelas')" class="cursor-pointer px-4 py-2"><?php echo e(config('app.school_mode') ? 'Kelas' : 'Prodi'); ?></th>
                    <th class="cursor-pointer px-4 py-2">Data</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(row, index) in paginatedData()" :key="index">
                    <tr class="border-t border-gray-300">
                        <td class="px-4 py-2">
                            <input type="checkbox" :checked="selectedItems.includes(row.head)"
                                @change="toggleItem(row.head, $event)" :value="row.head" class="rounded">
                        </td>
                        <td class="px-4 py-2" x-text="row.nis"></td>
                        <td class="px-4 py-2" x-text="row.name"></td>
                        <td class="px-4 py-2" x-text="row.kelas"></td>
                        <td class="px-4 py-2">
                            <div class="flex space-x-4 items-start">
                                <div class="flex-col gap-2">
                                    <template x-for="(val, index) in row.bill || []" :key="index">
                                        <div class="flex gap-3 items-center mb-1">
                                            <span class="text-sm font-medium" x-text="val.name"></span>
                                            <span class="text-sm" x-text="val.nominal"></span>
                                            <span class="text-sm" x-text="val.status"></span>
                                            <template x-if="val.status === 'Tagihan'">
                                                <button @click="verifyBill(val.bill)"
                                                    class="bg-green-500 hover:bg-green-600 text-white text-[10px] px-2 py-0.5 rounded shadow cursor-pointer">
                                                    Verifikasi
                                                </button>
                                            </template>
                                        </div>
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
            <h2 class="text-lg font-semibold mb-4">Tambah Bayar untuk <span x-text="selectedItems.length"></span> <?php echo e(config('app.school_mode') ? 'Murid' : 'Mahasiswa'); ?>

            </h2>

            <form @submit.prevent="assignPay">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Bayar</label>
                    <select x-model="selectedClass" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Pembayaran</option>
                        <?php $__currentLoopData = \App\Models\Payment::latest()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($item->id); ?>"><?php echo e($item->name); ?></option>
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

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('base.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/franken/breskull/resources/views/home/pay/index.blade.php ENDPATH**/ ?>