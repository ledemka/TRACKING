<?php
// templates/home/shipment_tracker.php
// Bloc de suivi : photo à gauche, panneau jaune à droite avec formulaire GET /track
$content = get_site_content();
$tracker = $content['tracker_block'];
?>
<section class="py-12 sm:py-16 bg-white">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-[#F4F3ED] rounded-2xl overflow-hidden border border-stone-200 shadow-sm grid grid-cols-1 lg:grid-cols-12 items-stretch">
            <!-- Photo à gauche -->
            <div class="lg:col-span-5 relative min-h-[260px] sm:min-h-[320px] lg:min-h-full">
                <img 
                    src="<?= escape_html($tracker['image']) ?>" 
                    alt="<?= escape_html($tracker['image_alt']) ?>" 
                    class="w-full h-full object-cover object-center absolute inset-0" 
                    width="<?= (int)$tracker['width'] ?>" 
                    height="<?= (int)$tracker['height'] ?>" 
                    loading="lazy"
                >
            </div>

            <!-- Panneau jaune à droite avec formulaire GET /track -->
            <div class="lg:col-span-7 bg-[#FFE500] p-6 sm:p-10 lg:p-12 flex flex-col justify-center">
                <div class="max-w-xl">
                    <span class="inline-block bg-[#171717] text-[#FFE500] text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-md mb-4">
                        Suivi en direct
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#171717] tracking-tight mb-3">
                        <?= escape_html($tracker['title']) ?>
                    </h2>
                    <p class="text-sm sm:text-base text-[#171717] mb-6 leading-relaxed">
                        <?= escape_html($tracker['description']) ?>
                    </p>

                    <!-- Formulaire classique GET /track sans aucun fetch -->
                    <form action="/track" method="GET" class="flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-grow">
                            <label for="tracking-number-input" class="sr-only">Numéro de suivi</label>
                            <input 
                                type="text" 
                                id="tracking-number-input" 
                                name="id" 
                                placeholder="<?= escape_html($tracker['placeholder']) ?>" 
                                required 
                                class="w-full h-12 px-4 rounded-lg bg-white text-[#171717] border-2 border-[#171717] placeholder:text-stone-500 font-semibold focus-brand-light text-base tracking-wide"
                            >
                        </div>
                        <button 
                            type="submit" 
                            class="h-12 px-6 rounded-lg bg-[#171717] text-white hover:bg-black font-bold text-base transition-colors focus-brand-dark flex items-center justify-center gap-2 shrink-0 shadow-sm"
                        >
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <span><?= escape_html($tracker['btn_text']) ?></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
