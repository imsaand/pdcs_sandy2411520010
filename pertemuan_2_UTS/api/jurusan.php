
<?php

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../helpers/response.php";

// Fungsi untuk menangani kegagalan query database
function gagalQuery(mysqli $koneksi) {

    error_log(mysqli_error($koneksi));

    sendResponse(
        false,
        "query gagal",
        null,
        500
    );
}

// Membaca HTTP Method dari Client
$method = $_SERVER['REQUEST_METHOD'];

// Routing menggunakan Switch-Case
switch ($method) {

    case 'GET':

        // GET BY ID (Prepared Statement)
        if (isset($_GET['id'])) {

            $id = (int) ($_GET['id'] ?? 0);

            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT id, kode_jurusan, nama_jurusan, fakultas
                 FROM jurusan
                 WHERE id = ?"
            );

            if (!$stmt) {
                gagalQuery($koneksi);
            }

            mysqli_stmt_bind_param($stmt, "i", $id);

            if (!mysqli_stmt_execute($stmt)) {
                gagalQuery($koneksi);
            }

            $r = mysqli_stmt_get_result($stmt);

            if (!$r) {
                gagalQuery($koneksi);
            }

            $data = mysqli_fetch_assoc($r);

            if (!$data) {
                sendResponse(
                    false,
                    "jurusan tidak ditemukan",
                    null,
                    404
                );
            }

            sendResponse(true, "berhasil", $data, 200);
        }

        // GET SEARCH (Prepared Statement)
        if (isset($_GET['search'])) {

            $search = '%' . trim($_GET['search']) . '%';

            $q = "SELECT id, kode_jurusan, nama_jurusan, fakultas
                  FROM jurusan
                  WHERE kode_jurusan LIKE ?
                  OR nama_jurusan LIKE ?
                  OR fakultas LIKE ?
                  ORDER BY id DESC";

            $stmt = mysqli_prepare($koneksi, $q);

            if (!$stmt) {
                gagalQuery($koneksi);
            }

            mysqli_stmt_bind_param(
                $stmt,
                "sss",
                $search,
                $search,
                $search
            );

            if (!mysqli_stmt_execute($stmt)) {
                gagalQuery($koneksi);
            }

            $r = mysqli_stmt_get_result($stmt);

            if (!$r) {
                gagalQuery($koneksi);
            }

            $data = [];

            while ($row = mysqli_fetch_assoc($r)) {
                $data[] = $row;
            }

            sendResponse(true, "berhasil", $data, 200);
        }

        // GET ALL JURUSAN
        $q = "SELECT
                  id,
                  kode_jurusan,
                  nama_jurusan,
                  fakultas
              FROM jurusan
              ORDER BY id DESC";

        $r = mysqli_query($koneksi, $q);

        if (!$r) {
            gagalQuery($koneksi);
        }

        $data = [];

        while ($row = mysqli_fetch_assoc($r)) {
            $data[] = $row;
        }

        sendResponse(true, "berhasil", $data, 200);

        break;


    case 'POST':

        // POST Bagian 1: Parsing dan Validasi JSON
        $input = json_decode(
            file_get_contents("php://input"),
            true
        );

        // 1. Validasi field wajib
        if (
            !$input ||
            !isset($input['kode_jurusan']) ||
            !isset($input['nama_jurusan']) ||
            !isset($input['fakultas'])
        ) {

            sendResponse(
                false,
                "field kode_jurusan, nama_jurusan, fakultas tidak boleh kosong",
                null,
                400
            );
        }

        $kode_jurusan = trim((string) $input['kode_jurusan']);
        $nama_jurusan = trim((string) $input['nama_jurusan']);
        $fakultas = trim((string) $input['fakultas']);

        // 2. Validasi isi dan panjang karakter
        if (
            $kode_jurusan === '' ||
            $nama_jurusan === '' ||
            $fakultas === ''
        ) {

            sendResponse(
                false,
                "semua field wajib diisi",
                null,
                400
            );
        }

        if (
            strlen($kode_jurusan) > 32 ||
            strlen($nama_jurusan) > 64 ||
            strlen($fakultas) > 32
        ) {

            sendResponse(
                false,
                "panjang karakter melebihi batas kolom database",
                null,
                400
            );
        }

        // POST Bagian 2: Cek Duplikasi dan INSERT Data

        // 3. Cek duplikasi kode jurusan
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM jurusan WHERE kode_jurusan = ?"
        );

        if (!$stmt) {
            gagalQuery($koneksi);
        }

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $kode_jurusan
        );

        if (!mysqli_stmt_execute($stmt)) {
            gagalQuery($koneksi);
        }

        $r_kode = mysqli_stmt_get_result($stmt);

        if (!$r_kode) {
            gagalQuery($koneksi);
        }

        if (mysqli_num_rows($r_kode) > 0) {
            sendResponse(
                false,
                "kode jurusan sudah terdaftar",
                null,
                409
            );
        }

        // 4. Insert data jurusan baru
        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO jurusan
             (kode_jurusan, nama_jurusan, fakultas)
             VALUES (?, ?, ?)"
        );

        if (!$stmt) {
            gagalQuery($koneksi);
        }

        mysqli_stmt_bind_param(
            $stmt,
            "sss",
            $kode_jurusan,
            $nama_jurusan,
            $fakultas
        );

        if (mysqli_stmt_execute($stmt)) {

            sendResponse(
                true,
                "data jurusan tersimpan",
                ["id" => mysqli_insert_id($koneksi)],
                201
            );

        } else {

            gagalQuery($koneksi);
        }

        break;


    case 'PUT':

        // PUT Bagian 1: Validasi dan Cek Data

        // 1. Validasi parameter ID pada URL
        if (!isset($_GET['id'])) {

            sendResponse(
                false,
                "id wajib diisi pada url",
                null,
                400
            );
        }

        // 2. Parsing JSON dan validasi field wajib
        $input = json_decode(
            file_get_contents("php://input"),
            true
        );

        if (
            !$input ||
            !isset($input['kode_jurusan']) ||
            !isset($input['nama_jurusan']) ||
            !isset($input['fakultas'])
        ) {

            sendResponse(
                false,
                "field kode_jurusan, nama_jurusan, fakultas tidak boleh kosong",
                null,
                400
            );
        }

        $id = (int) $_GET['id'];

        $kode_jurusan = trim((string) $input['kode_jurusan']);
        $nama_jurusan = trim((string) $input['nama_jurusan']);
        $fakultas = trim((string) $input['fakultas']);

        // 3. Validasi isi dan panjang karakter
        if (
            $kode_jurusan === '' ||
            $nama_jurusan === '' ||
            $fakultas === ''
        ) {

            sendResponse(
                false,
                "semua field wajib diisi",
                null,
                400
            );
        }

        if (
            strlen($kode_jurusan) > 32 ||
            strlen($nama_jurusan) > 64 ||
            strlen($fakultas) > 32
        ) {

            sendResponse(
                false,
                "panjang karakter melebihi batas kolom database",
                null,
                400
            );
        }

        // 4. Cek keberadaan jurusan
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM jurusan WHERE id = ?"
        );

        if (!$stmt) {
            gagalQuery($koneksi);
        }

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (!mysqli_stmt_execute($stmt)) {
            gagalQuery($koneksi);
        }

        $r_jurusan = mysqli_stmt_get_result($stmt);

        if (!$r_jurusan) {
            gagalQuery($koneksi);
        }

        if (mysqli_num_rows($r_jurusan) == 0) {

            sendResponse(
                false,
                "jurusan tidak ditemukan",
                null,
                404
            );
        }

        // PUT Bagian 2: Cek Duplikasi dan UPDATE

        // 5. Cek duplikasi kode jurusan kecuali ID sendiri
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM jurusan
             WHERE kode_jurusan = ? AND id != ?"
        );

        if (!$stmt) {
            gagalQuery($koneksi);
        }

        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $kode_jurusan,
            $id
        );

        if (!mysqli_stmt_execute($stmt)) {
            gagalQuery($koneksi);
        }

        $r_kode = mysqli_stmt_get_result($stmt);

        if (!$r_kode) {
            gagalQuery($koneksi);
        }

        if (mysqli_num_rows($r_kode) > 0) {

            sendResponse(
                false,
                "kode jurusan sudah terdaftar",
                null,
                409
            );
        }

        // 6. Update data jurusan
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE jurusan
             SET kode_jurusan = ?,
                 nama_jurusan = ?,
                 fakultas = ?
             WHERE id = ?"
        );

        if (!$stmt) {
            gagalQuery($koneksi);
        }

        mysqli_stmt_bind_param(
            $stmt,
            "sssi",
            $kode_jurusan,
            $nama_jurusan,
            $fakultas,
            $id
        );

        // 7. Eksekusi dan respons
        if (mysqli_stmt_execute($stmt)) {

            sendResponse(
                true,
                "data jurusan berhasil diperbaharui",
                ["id" => $id],
                200
            );

        } else {

            gagalQuery($koneksi);
        }

        break;


    case 'DELETE':

        // DELETE Jurusan: Validasi dan Pemeriksaan Relasi

        // 1. Validasi parameter ID pada URL
        if (!isset($_GET['id'])) {

            sendResponse(
                false,
                "id wajib diisi pada url",
                null,
                400
            );
        }

        $id = (int) $_GET['id'];

        // 2. Cek keberadaan jurusan
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM jurusan WHERE id = ?"
        );

        if (!$stmt) {
            gagalQuery($koneksi);
        }

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (!mysqli_stmt_execute($stmt)) {
            gagalQuery($koneksi);
        }

        $r_jurusan = mysqli_stmt_get_result($stmt);

        if (!$r_jurusan) {
            gagalQuery($koneksi);
        }

        if (mysqli_num_rows($r_jurusan) == 0) {

            sendResponse(
                false,
                "jurusan tidak ditemukan",
                null,
                404
            );
        }

        // 3. Cek relasi dengan tabel mahasiswa
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT COUNT(*) AS total
             FROM mahasiswa
             WHERE jurusan_id = ?"
        );

        if (!$stmt) {
            gagalQuery($koneksi);
        }

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (!mysqli_stmt_execute($stmt)) {
            gagalQuery($koneksi);
        }

        $r_mahasiswa = mysqli_stmt_get_result($stmt);

        if (!$r_mahasiswa) {
            gagalQuery($koneksi);
        }

        $data_mahasiswa = mysqli_fetch_assoc($r_mahasiswa);

        $jumlah_mahasiswa = (int) $data_mahasiswa['total'];

        // 4. Cek relasi dengan tabel matakuliah
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT COUNT(*) AS total
             FROM matakuliah
             WHERE jurusan_id = ?"
        );

        if (!$stmt) {
            gagalQuery($koneksi);
        }

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (!mysqli_stmt_execute($stmt)) {
            gagalQuery($koneksi);
        }

        $r_matakuliah = mysqli_stmt_get_result($stmt);

        if (!$r_matakuliah) {
            gagalQuery($koneksi);
        }

        $data_matakuliah = mysqli_fetch_assoc($r_matakuliah);

        $jumlah_matakuliah = (int) $data_matakuliah['total'];

        // 5. Tolak penghapusan jika masih memiliki relasi
        if (
            $jumlah_mahasiswa > 0 ||
            $jumlah_matakuliah > 0
        ) {

            sendResponse(
                false,
                "jurusan tidak dapat dihapus karena masih memiliki relasi",
                [
                    "jumlah_mahasiswa" => $jumlah_mahasiswa,
                    "jumlah_matakuliah" => $jumlah_matakuliah
                ],
                409
            );
        }

        // 6. Eksekusi DELETE
        $stmt = mysqli_prepare(
            $koneksi,
            "DELETE FROM jurusan WHERE id = ?"
        );

        if (!$stmt) {
            gagalQuery($koneksi);
        }

        mysqli_stmt_bind_param($stmt, "i", $id);

        // 7. Eksekusi dan pengiriman respons
        if (mysqli_stmt_execute($stmt)) {

            sendResponse(
                true,
                "data jurusan berhasil dihapus",
                ["id" => $id],
                200
            );

        } else {

            gagalQuery($koneksi);
        }

        break;


    default:

        sendResponse(
            false,
            "Method Not Allowed",
            null,
            405
        );

        break;
}
