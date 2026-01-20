<?php $__env->startSection('title', 'Login'); ?>
<?php $__env->startSection('content'); ?>
<div class="min-h-screen flex items-center justify-center w-full px-4 sm:px-6 lg:px-8">
    <div class="bg-white/70 backdrop-blur-xl shadow-2xl w-full max-w-sm sm:max-w-md rounded-3xl border border-white/40 p-8">
        <div class="flex flex-col items-center mb-6">
            <img src="<?php echo e(asset('icon.png')); ?>" class="w-24 h-24 object-contain mb-4 drop-shadow-md" alt="Breskul">
            <h2 class="text-2xl font-bold text-gray-800">Welcome Back</h2>
            <p class="text-sm text-gray-500">Please sign in to your account</p>
        </div>

        <form method="POST" action="<?php echo e(route('login')); ?>" class="space-y-6">
            <?php echo csrf_field(); ?>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1 pl-1">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo e(old('email')); ?>" required
                    autocomplete="email" autofocus
                    class="block w-full px-4 py-3 rounded-xl border-gray-300 focus:border-green-500 focus:ring focus:ring-green-500/20 transition duration-200 bg-white/50 hover:bg-white/80"
                    placeholder="you@example.com">
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-red-500 text-xs mt-1 pl-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1 pl-1">Password</label>
                <div class="relative">
                    <input type="password" name="password" required autocomplete="current-password"
                        class="block w-full px-4 py-3 rounded-xl border-gray-300 focus:border-green-500 focus:ring focus:ring-green-500/20 transition duration-200 bg-white/50 hover:bg-white/80"
                        placeholder="••••••••">
                    <button type="button" onclick="show(this)"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-green-600 cursor-pointer transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" id="eyeIcon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-red-500 text-xs mt-1 pl-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="flex items-center justify-between text-sm">
                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" <?php echo e(old('remember') ? 'checked' : ''); ?>

                        class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500 cursor-pointer">
                    <label for="remember" class="ml-2 block text-gray-600 cursor-pointer select-none">
                        Remember Me
                    </label>
                </div>

                <?php if(Route::has('password.request')): ?>
                <a class="font-medium text-green-600 hover:text-green-800 transition-colors"
                    href="<?php echo e(route('password.request')); ?>">
                    Forgot Password?
                </a>
                <?php endif; ?>
            </div>

            <div>
                <button type="submit"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-300 transform hover:-translate-y-0.5">
                    Sign In
                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('script'); ?>
<script>
    const eyeIcon = `
        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
            <path fill-rule="evenodd" d="M1.32 11.45C2.81 6.98 7.03 3.75 12 3.75c4.97 0 9.19 3.22 10.68 7.69.12.36.12.75 0 1.11C21.19 17.02 16.97 20.25 12 20.25c-4.97 0-9.19-3.22-10.68-7.69a1.76 1.76 0 0 1 0-1.11ZM17.25 12a5.25 5.25 0 1 1-10.5 0 5.25 5.25 0 0 1 10.5 0Z" clip-rule="evenodd" />
        </svg>`;

    const eyeOffIcon = `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
            <path d="M3.53 2.47a.75.75 0 0 0-1.06 1.06l18 18a.75.75 0 1 0 1.06-1.06l-18-18ZM22.676 12.553a11.249 11.249 0 0 1-2.631 4.31l-3.099-3.099a5.25 5.25 0 0 0-6.71-6.71L7.759 4.577a11.217 11.217 0 0 1 4.242-.827c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113Z" />
            <path d="M15.75 12c0 .18-.013.357-.037.53l-4.244-4.243A3.75 3.75 0 0 1 15.75 12ZM12.53 15.713l-4.243-4.244a3.75 3.75 0 0 0 4.244 4.243Z" />
            <path d="M6.75 12c0-.619.107-1.213.304-1.764l-3.1-3.1a11.25 11.25 0 0 0-2.63 4.31c-.12.362-.12.752 0 1.114 1.489 4.467 5.704 7.69 10.675 7.69 1.5 0 2.933-.294 4.242-.827l-2.477-2.477A5.25 5.25 0 0 1 6.75 12Z" />
        </svg>
        `;

    function show(e) {
        const input = e.parentElement.querySelector('input[type="password"], input[type="text"]');
        if (input) {
            input.type = input.type === 'password' ? 'text' : 'password';
            e.innerHTML = input.type === 'password' ? eyeIcon : eyeOffIcon;
        }
    }
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    body {
        background-color: #b0e298 !important;
        background-image: none !important;
    }
</style>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('base.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\project\breskul\web\resources\views/auth/login.blade.php ENDPATH**/ ?>