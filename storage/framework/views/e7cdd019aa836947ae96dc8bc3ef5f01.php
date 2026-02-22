<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="<?php echo e(asset('icon.png')); ?>" type="image/png" />
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body class="bg-gray-100" x-data="layout()" x-init="init()">
    <?php if(session('status')): ?>
        <div x-data="{ show: false, message: '' }" x-init="<?php if(session('status')): ?> message = '<?php echo e(session('status')); ?>';
            show = true;
            setTimeout(() => show = false, 3000); <?php endif; ?>" class="fixed top-4 right-10 z-[100]">
            <div x-show="show" x-transition class="bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg">
                <span x-text="message"></span>
            </div>
        </div>
        <div x-data="{ show: false, message: '' }" x-init="<?php if(session('err')): ?> message = '<?php echo e(session('err')); ?>';
            show = true;
            setTimeout(() => show = false, 3000); <?php endif; ?>" class="fixed top-50 right-4 z-[100]">
            <div x-show="show" x-transition class="bg-red-600 text-white px-4 py-2 rounded-lg shadow-lg">
                <span x-text="message"></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if(Route::is('dashboard.*')): ?>
        <!-- HEADER -->
        <?php echo $__env->make('base.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <!-- MAIN CONTENT -->
        <main class="max-w-7xl mx-auto mt-6 px-6 grid grid-cols-1 md:grid-cols-5 gap-6">

            <!-- SIDEBAR -->
            <?php echo $__env->make('base.side', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <!-- CONTENT -->
            <section class="col-span-4">

                <?php if(session('success')): ?>
                    <div x-data="{ show: true }" x-show="show"
                        class="flex items-center justify-between p-4 mb-3 text-white bg-green-400 rounded-lg shadow"
                        role="alert">
                        <div class="flex gap-2 font-semibold">
                            <span class="font-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-check-icon lucide-check">
                                    <path d="M20 6 9 17l-5-5" />
                                </svg>
                            </span> <?php echo e(session('success')); ?>

                        </div>

                        <button @click="show = false"
                            class="ml-4 text-white hover:text-gray-200 focus:outline-none text-xl leading-none">
                            &times;
                        </button>
                    </div>
                <?php elseif(session('err')): ?>
                    <div x-data="{ show: true }" x-show="show"
                        class="flex items-center justify-between p-4 mb-3 text-white bg-red-400 rounded-lg shadow"
                        role="alert">
                        <div class="flex gap-2 font-semibold">
                            <span class="font-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-circle-alert-icon lucide-circle-alert">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="12" x2="12" y1="8" y2="12" />
                                    <line x1="12" x2="12.01" y1="16" y2="16" />
                                </svg>
                            </span> <?php echo e(session('err')); ?>

                        </div>

                        <button @click="show = false"
                            class="ml-4 text-white hover:text-gray-200 focus:outline-none text-xl leading-none">
                            &times;
                        </button>
                    </div>
                <?php elseif(akademik()): ?>
                    <div x-data="{ show: true }" x-show="show"
                        class="flex items-center justify-between p-4 mb-3 text-white bg-red-500 rounded-lg shadow"
                        role="alert">
                        <div class="flex gap-2 font-semibold">
                            <span class="font-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-circle-alert-icon lucide-circle-alert">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="12" x2="12" y1="8" y2="12" />
                                    <line x1="12" x2="12.01" y1="16" y2="16" />
                                </svg>
                            </span> <?php echo e(akademik()); ?>

                        </div>

                        <button @click="show = false"
                            class="ml-4 text-white hover:text-gray-200 focus:outline-none text-xl leading-none">
                            &times;
                        </button>
                    </div>
                <?php endif; ?>
                <?php echo $__env->yieldContent('content'); ?>
            </section>

        </main>
    <?php else: ?>
        <?php echo $__env->yieldContent('content'); ?>
    <?php endif; ?>
</body>
<?php echo $__env->yieldPushContent('script'); ?>

</html>
<?php /**PATH /home/franken/breskull/resources/views/base/layout.blade.php ENDPATH**/ ?>