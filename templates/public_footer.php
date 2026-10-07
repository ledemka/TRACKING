    <footer class="bg-primary border-t border-primary py-12 mt-auto">
        <div class="max-w-7xl mx-auto px-4 md:px-8 lg:px-12">
            <div class="flex flex-col md:flex-row justify-between items-center md:items-start gap-8 mb-8">
                <div class="text-center md:text-left max-w-sm">
                    <h2 class="text-xl font-bold text-white mb-3"><?= escape_html($settings['nom']) ?></h2>
                    <p class="text-slate-300 text-sm leading-relaxed">
                        Suivez vos expéditions simplement et consultez l'avancement de vos colis à chaque étape.
                    </p>
                </div>
                <nav class="flex flex-wrap justify-center md:justify-end gap-6 text-sm font-medium text-slate-300">
                    <a href="/" class="hover:text-action transition-colors">Accueil</a>
                    <a href="/track" class="hover:text-action transition-colors">Suivre un colis</a>
                    <a href="/#services" class="hover:text-action transition-colors">Services</a>
                    <a href="/contact" class="hover:text-action transition-colors">Contact</a>
                    <a href="/login" class="hover:text-action transition-colors">Connexion</a>
                </nav>
            </div>
        </div>
    </footer>
</body>
</html>
