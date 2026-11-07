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

            $stmt = mysqli_prepare($koneksi,
                "SELECT m.id, m.nama, m.nim,
                        j.nama_jurusan AS jurusan
                 FROM mahasiswa m
                 LEFT JOIN jurusan j
                   ON m.jurusan_id = j.id
                 WHERE m.id = ?"
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
                    "mahasiswa tidak ditemukan",
                    null,
                    404
                );
            }

            sendResponse(true, "berhasil", $data, 200);
        }

        // GET SEARCH (Prepared Statement)
        if (isset($_GET['search'])) {

            $search = '%' . $_GET['search'] . '%';

            $q = "SELECT m.id, m.nama, m.nim,
                         j.nama_jurusan AS jurusan";
            $q .= " FROM mahasiswa m
                    LEFT JOIN jurusan j
                    ON m.jurusan_id = j.id";
            $q .= " WHERE m.nama LIKE ?
                    OR m.nim LIKE ?
                    ORDER BY m.id DESC";

            $stmt = mysqli_prepare($koneksi, $q);

            if (!$stmt) {
                gagalQuery($koneksi);
            }

            mysqli_stmt_bind_param(
                $stmt, "ss", $search, $search
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

        // GET All Mahasiswa
        $q = "SELECT
                m.id,
                m.nama,
                m.nim,
                j.nama_jurusan AS jurusan
              FROM mahasiswa m
              LEFT JOIN jurusan j
              ON m.jurusan_id = j.id
              ORDER BY m.id DESC";

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
        $input = json_decode(file_get_contents("php://input"), true);

        // 1. Validasi field wajib
        if (!$input ||
            !isset($input['nama']) ||
            !isset($input['nim']) ||
            !isset($input['jurusan_id'])) {

            sendResponse(
                false,
                "field nama, nim, jurusan_id tidak boleh kosong",
                null,
                400
            );
        }

        $nama = (string) $input['nama'];
        $nim = (string) $input['nim'];
        $jurusan_id = (int) $input['jurusan_id'];

        // 2. Cek keberadaan jurusan
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM jurusan WHERE id = ?"
        );

        if (!$stmt) gagalQuery($koneksi);

        mysqli_stmt_bind_param($stmt, "i", $jurusan_id);

        if (!mysqli_stmt_execute($stmt)) {
            gagalQuery($koneksi);
        }

        $r_jurusan = mysqli_stmt_get_result($stmt);

        if (!$r_jurusan) gagalQuery($koneksi);

        if (mysqli_num_rows($r_jurusan) == 0) {
            sendResponse(
                false,
                "jurusan_id tidak ditemukan",
                null,
                404
            );
        }

        // POST Bagian 2: Cek NIM dan INSERT Data

        // 3. Cek duplikasi NIM
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM mahasiswa WHERE nim = ?"
        );

        if (!$stmt) gagalQuery($koneksi);

        mysqli_stmt_bind_param($stmt, "s", $nim);

        if (!mysqli_stmt_execute($stmt)) {
            gagalQuery($koneksi);
        }

        $r_nim = mysqli_stmt_get_result($stmt);

        if (!$r_nim) gagalQuery($koneksi);

        if (mysqli_num_rows($r_nim) > 0) {
            sendResponse(
                false,
                "nim sudah terdaftar",
                null,
                409
            );
        }

        // 4. Insert data mahasiswa baru
        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO mahasiswa
             (nama, nim, jurusan_id, created_at)
             VALUES (?, ?, ?, CURDATE())"
        );

        if (!$stmt) gagalQuery($koneksi);

        mysqli_stmt_bind_param(
            $stmt,
            "ssi",
            $nama,
            $nim,
            $jurusan_id
        );

        if (mysqli_stmt_execute($stmt)) {
            sendResponse(
                true,
                "data tersimpan",
                ["id" => mysqli_insert_id($koneksi)],
                201
            );
        } else {
            gagalQuery($koneksi);
        }

        break;

    case 'PUT':

        // PUT Bagian 1: Validasi dan Cek Relasi

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

        if (!$input ||
            !isset($input['nama']) ||
            !isset($input['nim']) ||
            !isset($input['jurusan_id'])) {

            sendResponse(
                false,
                "field nama, nim, jurusan_id tidak boleh kosong",
                null,
                400
            );
        }

        $id = (int) $_GET['id'];
        $nama = (string) $input['nama'];
        $nim = (string) $input['nim'];
        $jurusan_id = (int) $input['jurusan_id'];

        // 3. Cek keberadaan mahasiswa
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM mahasiswa WHERE id = ?"
        );

        if (!$stmt) gagalQuery($koneksi);

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (!mysqli_stmt_execute($stmt)) {
            gagalQuery($koneksi);
        }

        $r_mahasiswa = mysqli_stmt_get_result($stmt);

        if (!$r_mahasiswa) gagalQuery($koneksi);

        if (mysqli_num_rows($r_mahasiswa) == 0) {
            sendResponse(
                false,
                "mahasiswa tidak ditemukan",
                null,
                404
            );
        }

        // 4. Cek keberadaan jurusan
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM jurusan WHERE id = ?"
        );

        if (!$stmt) gagalQuery($koneksi);

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $jurusan_id
        );

        if (!mysqli_stmt_execute($stmt)) {
            gagalQuery($koneksi);
        }

        $r_jurusan = mysqli_stmt_get_result($stmt);

        if (!$r_jurusan) gagalQuery($koneksi);

        if (mysqli_num_rows($r_jurusan) == 0) {
            sendResponse(
                false,
                "jurusan_id tidak ditemukan",
                null,
                404
            );
        }

        // PUT Bagian 2: Cek NIM dan UPDATE

        // 5. Cek duplikasi NIM kecuali ID sendiri
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM mahasiswa
             WHERE nim = ? AND id != ?"
        );

        if (!$stmt) gagalQuery($koneksi);

        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $nim,
            $id
        );

        if (!mysqli_stmt_execute($stmt)) {
            gagalQuery($koneksi);
        }

        $r_nim = mysqli_stmt_get_result($stmt);

        if (!$r_nim) gagalQuery($koneksi);

        if (mysqli_num_rows($r_nim) > 0) {
            sendResponse(
                false,
                "nim sudah terdaftar",
                null,
                409
            );
        }

        // 6. Update data mahasiswa
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE mahasiswa
             SET nama = ?, nim = ?, jurusan_id = ?
             WHERE id = ?"
        );

        if (!$stmt) gagalQuery($koneksi);

        mysqli_stmt_bind_param(
            $stmt,
            "ssii",
            $nama,
            $nim,
            $jurusan_id,
            $id
        );

        // 7. Eksekusi dan respons
        if (mysqli_stmt_execute($stmt)) {
            sendResponse(
                true,
                "data berhasil diperbaharui",
                ["id" => $id],
                200
            );
        } else {
            gagalQuery($koneksi);
        }

        break;

    case 'DELETE':
        // DELETE Mahasiswa: Validasi dan Eksekusi Hapus
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

        // 2. Cek keberadaan mahasiswa
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM mahasiswa WHERE id = ?"
        );

        if (!$stmt) gagalQuery($koneksi);

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (!mysqli_stmt_execute($stmt)) {
            gagalQuery($koneksi);
        }

        $r_mahasiswa = mysqli_stmt_get_result($stmt);

        if (!$r_mahasiswa) gagalQuery($koneksi);

        if (mysqli_num_rows($r_mahasiswa) == 0) {
            sendResponse(
                false,
                "mahasiswa tidak ditemukan",
                null,
                404
            );
        }

        // 3. Eksekusi DELETE
        $stmt = mysqli_prepare(
            $koneksi,
            "DELETE FROM mahasiswa WHERE id = ?"
        );

        if (!$stmt) gagalQuery($koneksi);

        mysqli_stmt_bind_param($stmt, "i", $id);

        // 4. Eksekusi dan pengiriman respons
        if (mysqli_stmt_execute($stmt)) {
            sendResponse(
                true,
                "data berhasil dihapus",
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
