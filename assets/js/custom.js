// File: assets/js/custom.js

document.addEventListener('DOMContentLoaded', function () {

    // Default input bulan (type="month") ke bulan berjalan kalau kosong
    document.querySelectorAll('input[type="month"]').forEach(function (input) {
        if (!input.value) {
            const now = new Date();
            input.value = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
        }
    });

    // Tampilan angka ribuan otomatis untuk input nominal/harga (mengetik biasa,
    // value asli tanpa titik tetap dikirim lewat data-raw saat submit)
    document.querySelectorAll('input[data-format="rupiah"]').forEach(function (input) {
        input.addEventListener('input', function () {
            let raw = this.value.replace(/\D/g, '');
            this.dataset.raw = raw;
            this.value = raw ? Number(raw).toLocaleString('id-ID') : '';
        });
    });

    // Reset validasi Bootstrap tiap kali modal dibuka ulang
    document.querySelectorAll('.modal').forEach(function (modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            const form = modal.querySelector('form');
            if (form) form.classList.remove('was-validated');
        });
    });
});

/**
 * Helper submit form modal generik via fetch + SweetAlert2.
 * Dipakai kalau mau menyeragamkan pola AJAX di halaman baru.
 *
 * @param {string} url       endpoint actions/*.php?act=...
 * @param {HTMLFormElement} form
 * @param {Function} onSuccess dipanggil setelah user klik OK di alert sukses
 */
function submitFormAjax(url, form, onSuccess) {
    fetch(url, { method: 'POST', body: new FormData(form) })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire('Berhasil', data.message, 'success').then(() => {
                    if (typeof onSuccess === 'function') onSuccess();
                });
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        })
        .catch(() => Swal.fire('Gagal', 'Terjadi kesalahan koneksi ke server.', 'error'));
}


function konfirmasiHapus(url, onSuccess) {
    Swal.fire({
        title: 'Hapus data ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'Batal'
    }).then(result => {
        if (result.isConfirmed) {
            fetch(url, { method: 'POST' })
                .then(res => res.json())
                .then(data => {
                    Swal.fire(data.status === 'success' ? 'Berhasil' : 'Gagal', data.message, data.status)
                        .then(() => { if (data.status === 'success' && typeof onSuccess === 'function') onSuccess(); });
                })
                .catch(() => Swal.fire('Gagal', 'Terjadi kesalahan koneksi ke server.', 'error'));
        }
    });
}