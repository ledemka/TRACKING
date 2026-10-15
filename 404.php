<?php
require_once __DIR__ . '/api/bootstrap.php';
http_response_code(404);
$page_title = __('error_404.page_title');
$page_description = __('error_404.page_desc');
require_once __DIR__ . '/templates/public_header.php';

$banner_config = [
    'badge' => __('error_404.badge'),
    'title' => __('error_404.title'),
    'description' => __('error_404.desc'),
    'breadcrumbs' => [
        ['label' => __('nav.home'), 'url' => '/'],
        ['label' => __('error_404.breadcrumb_error')]
    ],
    'actions' => [
        ['label' => __('error_404.action_home'), 'url' => '/', 'primary' => true],
        ['label' => __('error_404.action_track'), 'url' => '/track', 'primary' => false],
    ]
];
?>

<main class="flex-grow flex flex-col bg-white">
    <?php require __DIR__ . '/templates/page_banner.php'; ?>

    <section class="py-16 sm:py-24 bg-[#F4F3ED] flex items-center justify-center">
        <div class="text-center max-w-lg mx-auto px-4">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white border border-stone-200 text-[#171717] mb-6 shadow-sm">
                <svg class="w-10 h-10 text-[#FFE500]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-[#171717] tracking-tight mb-4">
                <?= escape_html(__('error_404.not_available_title')) ?>
            </h2>
            <p class="text-base text-[#6B6B6B] leading-relaxed mb-8">
                <?= escape_html(__('error_404.not_available_desc')) ?>
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="/" class="inline-flex items-center gap-2 bg-[#171717] text-white hover:bg-black font-bold text-sm px-6 py-3.5 rounded-lg transition-colors focus-brand-dark shadow-sm">
                    <?= escape_html(__('error_404.action_home')) ?>
                </a>
                <a href="/track" class="inline-flex items-center gap-2 bg-[#FFE500] text-[#171717] hover:bg-[#E6CE00] font-bold text-sm px-6 py-3.5 rounded-lg transition-colors focus-brand-light shadow-sm">
                    <?= escape_html(__('error_404.action_track')) ?>
                </a>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
