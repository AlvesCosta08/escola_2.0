    </main>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
     <script src="../views/chamadas/js/chamadas.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    
    <!-- Script para identificar a página atual e marcar o link ativo -->
    <script>
        $(document).ready(function() {
            // Marca o link ativo baseado na URL atual
            var currentUrl = window.location.pathname;
            $('.navbar-nav .nav-link').each(function() {
                if (currentUrl.indexOf($(this).attr('href')) !== -1) {
                    $(this).addClass('active');
                }
            });
        });
    </script>
</body>
</html>