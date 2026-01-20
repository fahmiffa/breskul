<header class="bg-green-700 text-white">
    <div class="max-w-7xl mx-auto flex items-center justify-between px-5 md:px-6 py-3">
        <div class="text-2xl font-bold">
            <?php echo e(env("APP_NAME")); ?><span class="font-light"></span>
        </div>
        <nav class="space-x-6">
            <a href="<?php echo e(route('dashboard.home')); ?>"
                class="<?php if(Route::is('dashboard.home')): ?> font-semibold <?php endif; ?> ">Home</a>
            <a href="<?php echo e(route('dashboard.master.index')); ?>"
                class="<?php if(Route::is('dashboard.master.index')): ?> font-semibold <?php endif; ?> ">Master</a>
        </nav>
        <div class="flex space-x-4">
            <div class="font-semibold hidden md:flex"><?php echo e(auth()->user()->name); ?></div>
            <a href="<?php echo e(route('dashboard.setting')); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-settings-icon lucide-settings">
                    <path
                        d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
            </a>
            <a class="text-sm" href="<?php echo e(route('dashboard.logout')); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-log-out-icon lucide-log-out">
                    <path d="m16 17 5-5-5-5" />
                    <path d="M21 12H9" />
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                </svg>
            </a>
        </div>
    </div>
</header>

<div class="bg-green-600 text-white">
    <div class="max-w-7xl mx-auto flex justify-between px-6 py-3">
        <div>
            <h2 class="text-xl font-semibold"><?php echo e(request()->segment(2) ? str_replace("-"," ",ucfirst(request()->segment(2))) : 'Dashboard'); ?>

            </h2>
            <?php if(request()->segment(3)): ?>
                <p class="text-sm opacity-80"><?php echo e(request()->segment(2) ? str_replace("-"," ",ucfirst(request()->segment(2))) : 'Dashboard'); ?>

                    >
                    <?php echo e(request()->segment(3) ? str_replace("-"," ",ucfirst(request()->segment(3))) : null); ?></p>
            <?php endif; ?>
        </div>

        <div class="block md:hidden items-center">
            <button @click="toggleSidebarMobile" class="text-gray-50 hover:text-black">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-menu-icon lucide-menu">
                    <path d="M4 12h16" />
                    <path d="M4 18h16" />
                    <path d="M4 6h16" />
                </svg>
            </button>
        </div>
    </div>
</div>
<?php /**PATH E:\project\breskul\web\resources\views/base/header.blade.php ENDPATH**/ ?>