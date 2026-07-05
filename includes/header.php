<?php
// KONTRAK PEMAKAIAN — sebelum include file ini, halaman pemanggil WAJIB:
//   require_once '../classes/Auth.php';
//   $auth = new Auth();
//   $auth->requireLogin();
//   $pageTitle = 'Judul Halaman';
// lalu baru: include '../includes/header.php';

if (!isset($auth)) {
    require_once __DIR__ . '/../classes/Auth.php';
    $auth = new Auth();
    $auth->requireLogin();
}

$currentUser = $auth->getUser();
$pageTitle   = $pageTitle ?? 'Agritech';

if (!function_exists('formatRupiah')) {
    function formatRupiah($angka)
    {
        return 'Rp. ' . number_format((float)$angka, 0, ',', '.');
    }
}

if (!function_exists('formatTanggalIndo')) {
    function formatTanggalIndo($tanggal, $format = 'd F Y')
    {
        $bulanIndo = [
            'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
            'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
            'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
            'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember',
        ];
        $tgl = date($format, strtotime($tanggal));
        return strtr($tgl, $bulanIndo);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?> | Agritech</title>

    <!-- AdminLTE 4 (letakkan hasil download dist/ AdminLTE4 di assets/adminlte/) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/adminlte/dist/css/adminlte.css">
    <!-- Bootstrap Icons -->
    <!--<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4/dist/css/adminlte.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/custom.css">
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">