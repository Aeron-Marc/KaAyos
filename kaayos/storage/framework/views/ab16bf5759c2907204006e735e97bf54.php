<!DOCTYPE html>
<html lang="<?php echo e(auth()->check() && auth()->user()->language === 'Filipino' ? 'fil' : 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', __('page_title.default')); ?> — KaAyos</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/client.css']); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>

<div class="shell">

    <aside class="sidebar">

        <a href="<?php echo e(route('home')); ?>" class="sidebar-logo">
            <div class="logo-mark">
                <i class="fa-solid fa-house-chimney" aria-hidden="true"></i>
            </div>
            <span class="logo-text">KaAyos</span>
        </a>

        <nav class="sidebar-nav">

            <a href="<?php echo e(route('client.dashboard')); ?>"
               class="nav-item <?php echo e(request()->routeIs('client.dashboard*') ? 'active' : ''); ?>">
                <i class="fa-solid fa-gauge-high nav-icon" aria-hidden="true"></i>
                <?php echo e(__('nav.dashboard')); ?>

            </a>

            <a href="<?php echo e(route('client.workers')); ?>"
               class="nav-item <?php echo e(request()->routeIs('client.workers*') ? 'active' : ''); ?>">
                <i class="fa-solid fa-users nav-icon" aria-hidden="true"></i>
                <?php echo e(__('nav.find_workers')); ?>

            </a>

            <a href="<?php echo e(route('client.bookings')); ?>"
               class="nav-item <?php echo e(request()->routeIs('client.bookings*') ? 'active' : ''); ?>">
                <i class="fa-solid fa-calendar-check nav-icon" aria-hidden="true"></i>
                <?php echo e(__('nav.bookings')); ?>

            </a>

            <a href="<?php echo e(route('client.messages')); ?>"
               class="nav-item <?php echo e(request()->routeIs('client.messages*') ? 'active' : ''); ?>">
                <i class="fa-solid fa-comment-dots nav-icon" aria-hidden="true"></i>
                <?php echo e(__('nav.messages')); ?>

            </a>

            <a href="<?php echo e(route('client.reviews')); ?>"
               class="nav-item <?php echo e(request()->routeIs('client.reviews*') ? 'active' : ''); ?>">
                <i class="fa-solid fa-star nav-icon" aria-hidden="true"></i>
                <?php echo e(__('nav.reviews')); ?>

            </a>

            <a href="<?php echo e(route('client.suggestions')); ?>"
               class="nav-item <?php echo e(request()->routeIs('client.suggestions*') ? 'active' : ''); ?>">
                <i class="fa-solid fa-lightbulb nav-icon" aria-hidden="true"></i>
                <?php echo e(__('nav.suggestions')); ?>

            </a>

            <a href="<?php echo e(route('client.account.profile')); ?>"
               class="nav-item <?php echo e(request()->routeIs('client.account*') ? 'active' : ''); ?>">
                <i class="fa-solid fa-user nav-icon" aria-hidden="true"></i>
                <?php echo e(__('nav.account')); ?>

            </a>

        </nav>

        <div class="sidebar-spacer"></div>

        <div class="sidebar-profile">
            <div class="profile-avatar">
                <?php if(auth()->user()->avatar): ?>
                    <img src="<?php echo e(\Illuminate\Support\Facades\Storage::url(auth()->user()->avatar)); ?>" alt="" class="sidebar-avatar-img" />
                <?php else: ?>
                    <?php echo e(strtoupper(substr(auth()->user()->name ?? 'U', 0, 2))); ?>

                <?php endif; ?>
            </div>
            <div class="profile-info">
                <p class="profile-name"><?php echo e(auth()->user()->name ?? 'User'); ?></p>
                <span class="profile-role"><?php echo e(__('role.homeowner')); ?></span>
            </div>
        </div>

        <form method="POST" action="<?php echo e(route('logout')); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit" class="sidebar-logout">
                <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
                <?php echo e(__('action.logout')); ?>

            </button>
        </form>

    </aside>

    <div class="main">

        <header class="topbar">
            <h1 class="page-title"><?php echo $__env->yieldContent('page_title', __('page_title.default')); ?></h1>

            <div class="topbar-actions">
                <a href="<?php echo e(route('client.dashboard.notifications')); ?>" class="icon-btn" aria-label="<?php echo e(__('action.notifications')); ?>">
                    <i class="fa-solid fa-bell" style="font-size:1rem;" aria-hidden="true"></i>
                    <?php if(collect($notifications ?? [])->where('unread', true)->count() > 0): ?>
                        <span class="badge-dot"></span>
                    <?php endif; ?>
                </a>

                <?php echo $__env->yieldContent('topbar_actions'); ?>
            </div>
        </header>

        <?php if (! empty(trim($__env->yieldContent('tabs')))): ?>
        <nav class="subtab-bar" role="tablist">
            <?php echo $__env->yieldContent('tabs'); ?>
        </nav>
        <?php endif; ?>

        <main class="content" id="main-content">
            <?php echo $__env->yieldContent('content'); ?>
        </main>

    </div>

</div>

<?php echo $__env->make('client.partials.chat-widget', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Capstone\KaAyos\kaayos\resources\views/layouts/client.blade.php ENDPATH**/ ?>