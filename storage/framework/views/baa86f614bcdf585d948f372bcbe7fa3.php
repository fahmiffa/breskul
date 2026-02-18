
<?php $__env->startSection('title', $title); ?>
<?php $__env->startSection('content'); ?>
<div class="flex flex-col bg-white rounded-lg shadow-md p-6">
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b pb-4">
        <h2 class="text-xl font-bold text-gray-800">Daftar Exam</h2>
        <a href="<?php echo e(route('dashboard.penjadwalan-ujian.create')); ?>"
            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-lg flex items-center gap-2 text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14" />
                <path d="M12 5v14" />
            </svg>
            Tugaskan Ujian
        </a>
    </div>

    <?php if(session('success')): ?>
    <div class="mb-4 p-4 bg-green-50 text-green-700 rounded-xl border border-green-200">
        <?php echo e(session('success')); ?>

    </div>
    <?php endif; ?>

    <div class="mb-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <form method="GET" action="<?php echo e(route('dashboard.penjadwalan-ujian.index')); ?>" class="w-full flex flex-col md:flex-row gap-2">
            <div class="relative flex-1">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari Nama Murid..."
                    class="w-full border border-gray-300 ring-0 rounded-xl px-4 py-2.5 pl-10 focus:outline-[#177245] shadow-sm transition-all text-sm" />
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                </div>
            </div>

            <select name="class_id" onchange="this.form.submit()" class="border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-[#177245] shadow-sm transition-all text-sm min-w-[150px]">
                <option value="">Semua Kelas</option>
                <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($class->id); ?>" <?php echo e(request('class_id') == $class->id ? 'selected' : ''); ?>><?php echo e($class->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <button type="submit" class="hidden">Filter</button>
        </form>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
        <table class="min-w-full bg-white text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-gray-600 border-b border-gray-200">
                    <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">No</th>
                    <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">Ujian</th>
                    <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">Murid</th>
                    <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">Kelas</th>
                    <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">Status</th>
                    <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">Skor</th>
                    <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 text-gray-500 font-medium"><?php echo e($items->firstItem() + $index); ?></td>
                    <td class="px-6 py-4 font-bold text-gray-800"><?php echo e($row->ujian->nama); ?></td>
                    <td class="px-6 py-4 text-gray-700"><?php echo e($row->student->name); ?></td>
                    <td class="px-6 py-4 text-gray-600">
                        <?php $__currentLoopData = $row->student->Kelas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kelas): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="px-2 py-0.5 bg-gray-100 rounded text-[10px]"><?php echo e($kelas->name); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </td>
                    <td class="px-6 py-4">
                        <?php
                        $statusMap = [
                        0 => ['label' => 'Belum', 'class' => 'bg-gray-100 text-gray-600'],
                        1 => ['label' => 'Mengerjakan', 'class' => 'bg-blue-100 text-blue-600'],
                        2 => ['label' => 'Selesai', 'class' => 'bg-green-100 text-green-600'],
                        ];
                        $st = $statusMap[$row->status] ?? $statusMap[0];
                        ?>
                        <span class="px-2 py-1 <?php echo e($st['class']); ?> rounded-full font-bold text-[10px] uppercase">
                            <?php echo e($st['label']); ?>

                        </span>
                    </td>
                    <td class="px-6 py-4 font-bold text-gray-800"><?php echo e($row->score ?? '-'); ?></td>
                    <td class="px-6 py-4 text-center">
                        <form action="<?php echo e(route('dashboard.penjadwalan-ujian.destroy', $row->id)); ?>" method="POST" onsubmit="return confirm('Hapus penugasan ini?')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18" />
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                    <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                </svg>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="text-center px-6 py-10 text-gray-500">
                        Belum ada ujian yang ditugaskan.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        <?php echo e($items->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('base.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\project\breskul\web\resources\views/master/ujian_assignment/index.blade.php ENDPATH**/ ?>