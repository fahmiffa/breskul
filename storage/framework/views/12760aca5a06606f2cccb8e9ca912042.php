<?php $__env->startSection('title', $action); ?>
<?php $__env->startSection('content'); ?>

    <div class="flex flex-col bg-white rounded-lg shadow-md p-6">
        <div class="font-semibold mb-3 text-xl"><?php echo e($action); ?></div>

        <form method="POST"
            action="<?php echo e(isset($items) ? route('dashboard.master.murid.update', ['murid' => $items->id]) : route('dashboard.master.murid.store')); ?>"
            class="grid grid-cols-1" enctype="multipart/form-data">
            <?php if(isset($items)): ?>
                <?php echo method_field('PUT'); ?>
            <?php endif; ?>
            <?php echo csrf_field(); ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">NIS</label>
                    <div class="relative">
                        <input type="text" name="nis" value="<?php echo e(old('nis', $items->nis ?? '')); ?>"
                            class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                    </div>
                    <?php $__errorArgs = ['nis'];
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
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Nama</label>
                    <div class="relative">
                        <input type="text" name="name" value="<?php echo e(old('name', $items->name ?? '')); ?>"
                            class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
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
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Kelas</label>
                    <select name="kelas"
                        class="block border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]"
                        required>
                        <option value="">Pilih kelas</option>
                        <?php $__currentLoopData = $kelas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($row->id); ?>" <?php if(old('kelas', isset($items) && $items->kelas) == $row->id): echo 'selected'; endif; ?>><?php echo e($row->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <?php $__errorArgs = ['kelas'];
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
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Semester</label>
                    <select name="akademik"
                        class="block border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]"
                        required>
                        <option value="">Pilih Semester</option>
                        <?php $__currentLoopData = $akademik; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($row->id); ?>" <?php if(old('akademik', isset($items) && $items->akademik) == $row->id): echo 'selected'; endif; ?>><?php echo e($row->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <?php $__errorArgs = ['akademik'];
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
                <div class="mb-4" x-data="{ imagePreview: '<?php echo e(isset($items) && $items->img ? asset('storage/' . $items->img) : null); ?>' }">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Photo</label>
                    <input type="file" name="image" accept="image/*"
                        @change="let file = $event.target.files[0]; imagePreview = URL.createObjectURL(file)"
                        class="block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:border-0
                   file:text-sm file:font-semibold file:bg-blue-50 file:text-green-700 
                   hover:file:bg-blue-100 cursor-pointer" />
                    <template x-if="imagePreview">
                        <img :src="imagePreview" class="w-24 h-24 object-cover rounded border border-gray-300 my-3" />
                    </template>
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
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Jenis Kelamin</label>
                    <select name="gender"
                        class="block border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]"
                        required>
                        <option value="">Pilih Jenis</option>
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
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Tempat, Tanggal lahir</label>
                    <div class="flex items-center gap-2">
                        <input type="text" name="place" placeholder="Tempat lahir"
                            value="<?php echo e(old('place', $items->place ?? '')); ?>"
                            class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                        <input type="date" name="birth" value="<?php echo e(old('birth', $items->birth ?? '')); ?>"
                            class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Nomor HP</label>
                    <div class="relative">
                        <input type="text" name="hp_siswa" value="<?php echo e(old('hp_siswa', $items->hp_siswa ?? '')); ?>"
                            class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                    </div>
                    <?php $__errorArgs = ['hp_siswa'];
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
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Email</label>
                    <div class="relative">
                        <input type="email" name="email" value="<?php echo e(old('email', $items->email ?? '')); ?>"
                            class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                    </div>
                    <?php $__errorArgs = ['email'];
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

            <div class="flex items-center justify-center my-6">
                <div class="flex-grow border-t border-gray-300"></div>
                <span class="mx-4 text-gray-500 text-sm font-bold">DATA ORANG TUA/WALI</span>
                <div class="flex-grow border-t border-gray-300"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Ayah</label>
                    <div class="flex items-center gap-2">
                        <input type="text" name="dad" placeholder="Nama"
                            value="<?php echo e(old('dad', $student->dad ?? '')); ?>"
                            class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                        <input type="text" placeholder="Pekerjaan" name="dadJob"
                            value="<?php echo e(old('dadJob', $student->dadJob ?? '')); ?>"
                            class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Ibu</label>
                    <div class="flex items-center gap-2">
                        <input type="text" name="mom" placeholder="Nama"
                            value="<?php echo e(old('mom', $student->mom ?? '')); ?>"
                            class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                        <input type="text" placeholder="Pekerjaan" name="momJob"
                            value="<?php echo e(old('momJob', $student->momJob ?? '')); ?>"
                            class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Nomor HP Orang Tua</label>
                    <div class="flex items-center gap-2">
                        <input type="text" name="hp_parent" placeholder="Nomor HP"
                            value="<?php echo e(old('hp_parent', $student->hp_parent ?? '')); ?>"
                            class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                    </div>
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

<?php echo $__env->make('base.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/franken/breskull/resources/views/master/murid/form.blade.php ENDPATH**/ ?>