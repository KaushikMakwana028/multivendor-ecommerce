</div><!-- /page-inner -->
</main><!-- /main-content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Sidebar toggle for mobile
    document.getElementById('sidebarToggle').addEventListener('click', function () {
        document.getElementById('sidebar').classList.toggle('mobile-open');
    });

    // Auto-dismiss alerts
    setTimeout(function () {
        document.querySelectorAll('.alert-custom').forEach(function (el) {
            el.style.opacity = '0';
            el.style.transition = 'opacity 0.5s';
            setTimeout(function () { el.remove(); }, 500);
        });
    }, 4000);
</script>
</body>
</html>
