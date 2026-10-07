        </div> <!-- Fin max-w-7xl mx-auto w-full -->
    </main> <!-- Fin main content area -->
    
    <script>
        const btn = document.getElementById('admin-mobile-btn');
        const menu = document.getElementById('admin-mobile-menu');
        if(btn && menu) {
            btn.addEventListener('click', () => {
                menu.classList.toggle('hidden');
            });
        }
    </script>
</body>
</html>
