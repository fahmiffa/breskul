
<?php $__env->startSection('title', $action); ?>
<?php $__env->startSection('content'); ?>
    <div class="flex flex-col bg-white rounded-lg shadow-md p-6">
        <div class="font-semibold mb-3 text-xl"><?php echo e($action); ?></div>
        <?php if(isset($items)): ?>
            <form method="POST" action="<?php echo e(route('dashboard.master.ekstrakurikuler.update', $items->id)); ?>" class="grid grid-cols-1">
                <?php echo method_field('PUT'); ?>
        <?php else: ?>
            <form method="POST" action="<?php echo e(route('dashboard.master.ekstrakurikuler.store')); ?>" class="grid grid-cols-1">
        <?php endif; ?>
                <?php echo csrf_field(); ?>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Nama</label>
                    <div class="relative">
                        <input type="text" name="nama" value="<?php echo e(old('nama', $items->nama ?? '')); ?>"
                            class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full md:w-1/2 focus:outline-[#177245]">
                    </div>
                    <?php $__errorArgs = ['nama'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-500 text-xs italic mt-2"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Guru</label>
                    <div class="relative">
                        <select name="guru_id" class="border border-gray-300 ring-0 rounded-xl px-3 py-2 w-full md:w-1/2 focus:outline-[#177245]">
                            <option value="">Pilih Guru</option>
                            <?php $__currentLoopData = $teaches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teach): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($teach->id); ?>" <?php echo e(old('guru_id', $items->guru_id ?? '') == $teach->id ? 'selected' : ''); ?>>
                                    <?php echo e($teach->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <?php $__errorArgs = ['guru_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-500 text-xs italic mt-2"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="mb-4" x-data="{ 
                    waktu: '<?php echo e(old('waktu', isset($items) ? \Illuminate\Support\Carbon::parse($items->waktu)->format('Y-m-d\TH:i') : '')); ?>',
                    get formattedWaktuIndo() {
                        if (!this.waktu) return '';
                        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        
                        const date = new Date(this.waktu);
                        if (isNaN(date.getTime())) return '';
                        const dayName = days[date.getDay()];
                        const day = date.getDate();
                        const monthName = months[date.getMonth()];
                        const year = date.getFullYear();
                        const hours = String(date.getHours()).padStart(2, '0');
                        const minutes = String(date.getMinutes()).padStart(2, '0');
                        
                        return `${dayName}, ${day} ${monthName} ${year} Jam ${hours}:${minutes}`;
                    }
                }">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Waktu</label>
                    <div class="relative">
                        <input type="datetime-local" name="waktu" x-model="waktu"
                            class="border border-gray-300 ring-0 rounded-xl px-3 py-2 w-full md:w-1/2 focus:outline-[#177245]">
                    </div>
                    <p class="text-green-600 text-xs font-semibold mt-1" x-show="waktu" x-text="formattedWaktuIndo"></p>
                    <?php $__errorArgs = ['waktu'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-500 text-xs italic mt-2"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="flex items-center">
                    <button type="submit"
                        class="cursor-pointer bg-green-500 text-sm hover:bg-green-700 text-white font-bold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline">
                        Simpan
                    </button>
                    <a href="<?php echo e(route('dashboard.master.ekstrakurikuler.index')); ?>" class="ml-2 text-gray-600 hover:text-gray-800">Batal</a>
                </div>
            </form>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('base.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\project\breskul\web\resources\views/master/ekstrakurikuler/form.blade.php ENDPATH**/ ?>