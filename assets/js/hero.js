// assets/js/hero.js
// Gestion accessible du carrousel Hero (clavier, tactile, pause/lecture, sans saut de mise en page)
(function() {
    'use strict';

    function initHeroCarousel() {
        const carousel = document.querySelector('[data-hero-carousel]');
        if (!carousel) return;

        const slides = carousel.querySelectorAll('.hero-slide');
        const indicators = carousel.querySelectorAll('[data-hero-indicator]');
        const prevBtn = carousel.querySelector('[data-hero-prev]');
        const nextBtn = carousel.querySelector('[data-hero-next]');
        const pauseBtn = carousel.querySelector('[data-hero-pause]');
        const pauseIconPlay = carousel.querySelector('[data-icon-play]');
        const pauseIconPause = carousel.querySelector('[data-icon-pause]');

        if (slides.length <= 1) return;

        let currentIndex = 0;
        let isPlaying = false; // Par défaut désactivé selon spécification
        let timer = null;
        const intervalMs = 6000;

        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function showSlide(index) {
            if (index < 0) index = slides.length - 1;
            if (index >= slides.length) index = 0;
            currentIndex = index;

            slides.forEach((slide, i) => {
                const isActive = (i === currentIndex);
                if (isActive) {
                    slide.classList.add('is-active');
                    slide.removeAttribute('aria-hidden');
                } else {
                    slide.classList.remove('is-active');
                    slide.setAttribute('aria-hidden', 'true');
                }
            });

            indicators.forEach((ind, i) => {
                const isCurrent = (i === currentIndex);
                ind.setAttribute('aria-current', isCurrent ? 'true' : 'false');
                if (isCurrent) {
                    ind.classList.remove('opacity-40');
                    ind.classList.add('opacity-100');
                } else {
                    ind.classList.remove('opacity-100');
                    ind.classList.add('opacity-40');
                }
            });
        }

        function nextSlide() {
            showSlide(currentIndex + 1);
        }

        function prevSlide() {
            showSlide(currentIndex - 1);
        }

        function startAutoplay() {
            if (prefersReducedMotion) return;
            isPlaying = true;
            if (pauseBtn) {
                pauseBtn.setAttribute('aria-label', 'Mettre en pause le carrousel');
                if (pauseIconPlay) pauseIconPlay.classList.add('hidden');
                if (pauseIconPause) pauseIconPause.classList.remove('hidden');
            }
            clearInterval(timer);
            timer = setInterval(nextSlide, intervalMs);
        }

        function stopAutoplay() {
            isPlaying = false;
            if (pauseBtn) {
                pauseBtn.setAttribute('aria-label', 'Activer le défilement automatique du carrousel');
                if (pauseIconPlay) pauseIconPlay.classList.remove('hidden');
                if (pauseIconPause) pauseIconPause.classList.add('hidden');
            }
            clearInterval(timer);
            timer = null;
        }

        function toggleAutoplay() {
            if (isPlaying) {
                stopAutoplay();
            } else {
                startAutoplay();
            }
        }

        if (prevBtn) prevBtn.addEventListener('click', function() { prevSlide(); stopAutoplay(); });
        if (nextBtn) nextBtn.addEventListener('click', function() { nextSlide(); stopAutoplay(); });
        if (pauseBtn) pauseBtn.addEventListener('click', toggleAutoplay);

        indicators.forEach((ind) => {
            ind.addEventListener('click', function() {
                const targetIdx = parseInt(ind.getAttribute('data-index'), 10);
                showSlide(targetIdx);
                stopAutoplay();
            });
        });

        // Navigation Clavier
        carousel.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') {
                prevSlide();
                stopAutoplay();
            } else if (e.key === 'ArrowRight') {
                nextSlide();
                stopAutoplay();
            }
        });

        // Support Tactile (Swipe)
        let touchStartX = 0;
        let touchEndX = 0;

        carousel.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        carousel.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            const diff = touchEndX - touchStartX;
            if (Math.abs(diff) > 40) {
                if (diff < 0) {
                    nextSlide();
                } else {
                    prevSlide();
                }
                stopAutoplay();
            }
        }, { passive: true });

        // Initialisation de l'état du bouton Pause/Lecture
        stopAutoplay();
        showSlide(0);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeroCarousel);
    } else {
        initHeroCarousel();
    }
})();
