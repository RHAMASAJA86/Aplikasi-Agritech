<?php
// Menutup app-main (dibuka di halaman masing-masing), lalu app-wrapper, lalu load JS.
?>
    <footer class="app-footer">
        <!--<div class="float-end d-none d-sm-inline">Agritech &copy; <?= date('Y') ?></div>-->
        <strong>Copyright <?= date('Y') ?> Agritech.
    </footer>
</div><!-- /.app-wrapper -->

<!-- Bootstrap 5 Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE 4 (local) -->
<!--<script src="assets/adminlte/dist/js/adminlte.js"></script>-->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@4/dist/js/adminlte.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Custom JS -->
<script src="<?= BASE_URL ?>assets/js/custom.js"></script>

<script>
document.querySelectorAll('.logout-link').forEach(function (el) {
    el.addEventListener('click', function (e) {
        e.preventDefault();
        const url = this.getAttribute('href');
        Swal.fire({
            title: 'Apakah Anda yakin ingin keluar?',
            text: 'Terima kasih telah menggunakan aplikasi ini.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Keluar',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#28a745'
        }).then(function (result) {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });
});
</script>
</body>
</html>