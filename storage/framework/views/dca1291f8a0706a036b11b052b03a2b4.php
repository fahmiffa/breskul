
<?php $__env->startSection('title', $title); ?>
<?php $__env->startSection('content'); ?>
<div class="flex flex-col gap-6" x-data="{ selectedStudents: [] }">

    
    <div class="flex items-center gap-3">
        <a href="<?php echo e(route('dashboard.penjadwalan-ujian.index')); ?>" class="p-2 bg-white hover:bg-gray-50 rounded-lg text-gray-500 shadow-sm border border-gray-100 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6" />
            </svg>
        </a>
        <h2 class="text-xl font-bold text-gray-800"><?php echo e($title); ?></h2>
    </div>

    
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="GET" action="<?php echo e(route('dashboard.penjadwalan-ujian.create')); ?>" class="flex flex-col md:flex-row items-end gap-4">
            <div class="flex-1 w-full">
                <label class="block text-gray-700 text-sm font-bold mb-2 ml-1">Filter Berdasarkan Kelas</label>
                <select name="class_id" onchange="this.form.submit()"
                    class="block border border-gray-300 rounded-xl px-4 py-3 w-full focus:outline-green-600 bg-white shadow-sm transition-all">
                    <option value="">Semua Kelas</option>
                    <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($class->id); ?>" <?php echo e($selectedClass == $class->id ? 'selected' : ''); ?>>
                        <?php echo e($class->name); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="w-full md:w-auto">
                <button type="submit" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-xl transition-all border border-gray-200">
                    Apply Filter
                </button>
            </div>
        </form>
    </div>

    
    <form method="POST" action="<?php echo e(route('dashboard.penjadwalan-ujian.store')); ?>" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <?php echo csrf_field(); ?>

        
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2 ml-1">Pilih Ujian</label>
                    <select name="ujian_id" required
                        class="block border border-gray-200 rounded-xl px-4 py-3 w-full focus:outline-green-600 bg-gray-50 shadow-sm transition-all font-medium">
                        <option value="">-- Pilih Ujian --</option>
                        <?php $__currentLoopData = $ujians; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ujian): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($ujian->id); ?>"><?php echo e($ujian->nama); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['ujian_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="p-4 bg-blue-50 rounded-xl border border-blue-100 space-y-2">
                    <h4 class="font-bold text-blue-800 text-xs uppercase tracking-wider flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 16v-4" />
                            <path d="M12 8h.01" />
                        </svg>
                        Ringkasan
                    </h4>
                    <div class="text-sm text-blue-700">
                        Murid Terpilih: <span class="font-black text-lg" x-text="selectedStudents.length"></span>
                    </div>
                </div>

                <button type="submit" :disabled="selectedStudents.length === 0"
                    class="w-full bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-4 px-6 rounded-xl transition-all shadow-lg shadow-green-100 flex items-center justify-center gap-2">
                    Simpan Penugasan
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m5 12 5 5L20 7" />
                    </svg>
                </button>
            </div>
        </div>

        
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-[600px]">
                <div class="bg-gray-50 px-6 py-4 border-b flex justify-between items-center">
                    <div>
                        <span class="text-sm font-bold text-gray-700">Daftar Murid</span>
                        <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wider"><?php echo e($selectedClass ? 'Di Kelas Terpilih' : 'Semua Murid di Institusi Anda'); ?></p>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer bg-white px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 transition-all shadow-sm">
                        <input type="checkbox" @change="if ($event.target.checked) { selectedStudents = <?php echo e(json_encode($students->pluck('id'))); ?> } else { selectedStudents = [] }" class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <span class="text-xs font-bold text-gray-600 uppercase">Pilih Semua</span>
                    </label>
                </div>

                <div class="overflow-y-auto flex-1 divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <label class="flex items-center gap-4 p-4 hover:bg-green-50/30 cursor-pointer transition-colors group">
                        <input type="checkbox" name="student_ids[]" value="<?php echo e($student->id); ?>" x-model="selectedStudents"
                            class="w-5 h-5 rounded-lg border-gray-300 text-green-600 focus:ring-green-500 transition-all">

                        <div class="flex-shrink-0 w-10 h-10 bg-gray-200 rounded-full overflow-hidden border border-gray-100">
                            <?php if($student->img): ?>
                            <img src="<?php echo e(asset('storage/'.$student->img)); ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-100 font-bold uppercase text-xs">
                                <?php echo e(substr($student->name, 0, 2)); ?>

                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-800 group-hover:text-green-700 transition-colors truncate"><?php echo e($student->name); ?></p>
                            <div class="flex gap-2 items-center">
                                <?php $__currentLoopData = $student->Kelas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kelas): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="text-[9px] font-bold uppercase tracking-wider text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded"><?php echo e($kelas->name); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="flex flex-col items-center justify-center h-full text-gray-400 p-8 text-center space-y-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                        <div>
                            <p class="text-sm font-bold text-gray-600">Tidak ada murid ditemukan.</p>
                            <p class="text-xs text-gray-400">Pastikan murid sudah terdaftar di institusi dan memiliki data kelas yang aktif.</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php $__errorArgs = ['student_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('base.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\project\breskul\web\resources\views/master/ujian_assignment/form.blade.php ENDPATH**/ ?>