    <footer class="bg-white border-t border-slate-200 py-8 mt-auto">
        <div class="container mx-auto px-4 text-center text-sm text-slate-500">
            <p>&copy; <?= date('Y') ?> <?= escape_html($settings['nom']) ?>. Tous droits réservés.</p>
            <div class="mt-4 flex justify-center gap-4">
                <a href="#" class="hover:text-primary transition-colors">Mentions légales</a>
                <a href="#" class="hover:text-primary transition-colors">Politique de confidentialité</a>
            </div>
        </div>
    </footer>
</body>
</html>
