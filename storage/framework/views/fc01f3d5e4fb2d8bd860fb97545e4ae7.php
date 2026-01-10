<?php $__env->startSection('title', $action); ?>
<?php $__env->startSection('content'); ?>
    <div class="flex flex-col bg-white rounded-lg shadow-md p-6">
        <div class="font-semibold mb-3 text-xl"><?php echo e($action); ?></div>
        <form method="POST"
            action="<?php echo e(isset($items) ? route('dashboard.master.guru.update', ['guru' => $items->id]) : route('dashboard.master.guru.store')); ?>"
            class="grid grid-cols-1">
            <?php if(isset($items)): ?>
                <?php echo method_field('PUT'); ?>
            <?php endif; ?>
            <?php echo csrf_field(); ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Nama</label>
                    <div class="relative">
                        <input type="text" name="name" value="<?php echo e(old('name', $items->name ?? '')); ?>"
                            class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full  focus:outline-[#177245]">
                    </div>
                    <?php $__errorArgs = ['name'];
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
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Gender</label>
                    <select name="gender"
                        class="block border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]"
                        required>
                        <option value="">Pilih Gender</option>
                        <option value="1" <?php if(old('gender', isset($items) && $items->gender) == '1'): echo 'selected'; endif; ?>>Laki-laki</option>
                        <option value="2" <?php if(old('gender', isset($items) && $items->gender) == '2'): echo 'selected'; endif; ?>>Perempuan</option>
                    </select>
    
                    <?php $__errorArgs = ['gender'];
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
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Alamat</label>
                    <textarea name="alamat" class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]"><?php echo e(old('alamat', $items->alamat ?? '')); ?></textarea>
                    <?php $__errorArgs = ['alamat'];
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
            </div>
            <div class="flex items-center">
                <button type="submit"
                    class="cursor-pointer bg-green-500 text-sm hover:bg-green-700 text-white font-bold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline">
                    Simpan
                </button>
            </div>
        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('base.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\project\breskul\web\resources\views/master/guru/form.blade.php ENDPATH**/ ?>