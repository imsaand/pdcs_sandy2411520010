
<?php

// Mengatur format response menjadi JSON
header("Content-Type: application/json; charset=UTF-8");

// Lokasi file penyimpanan data
$file = __DIR__ . "/data_mahasiswa.json";

// Fungsi untuk mengirim response JSON
function sendResponse($code, $response) {
    http_response_code($code);
    echo json_encode(
        $response,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );
    exit;
}

// Memastikan file JSON tersedia
if (!file_exists($file)) {
    sendResponse(500, [
        "status" => "error",
        "message" => "File data mahasiswa tidak ditemukan"
    ]);
}

// Membaca data mahasiswa
$mahasiswa = json_decode(file_get_contents($file), true);

if (!is_array($mahasiswa)) {
    sendResponse(500, [
        "status" => "error",
        "message" => "Format data JSON tidak valid"
    ]);
}

// Mengambil HTTP Method
$method = $_SERVER["REQUEST_METHOD"];

// METHOD GET: Mengambil data mahasiswa
if ($method === "GET") {
    sendResponse(200, [
        "status" => "success",
        "message" => "Data mahasiswa berhasil diambil",
        "data" => $mahasiswa
    ]);
}

// METHOD POST: Menambahkan mahasiswa
if ($method === "POST") {

    // Membaca JSON dari request body
    $input = json_decode(file_get_contents("php://input"), true);

    if (!is_array($input)) {
        sendResponse(400, [
            "status" => "error",
            "message" => "Format JSON request tidak valid"
        ]);
    }

    $nim = trim($input["nim"] ?? "");
    $nama = trim($input["nama"] ?? "");
    $jurusan = trim($input["jurusan"] ?? "");

    // Memeriksa kelengkapan data
    if ($nim === "" || $nama === "" || $jurusan === "") {
        sendResponse(400, [
            "status" => "error",
            "message" => "NIM, nama, dan jurusan wajib diisi"
        ]);
    }

    // Membuat ID otomatis
    $nextId = 1;

    foreach ($mahasiswa as $item) {
        $currentId = (int)($item["id"] ?? 0);

        if ($currentId >= $nextId) {
            $nextId = $currentId + 1;
        }
    }

    // Membuat data mahasiswa baru
    $newMahasiswa = [
        "id" => $nextId,
        "nim" => $nim,
        "nama" => $nama,
        "jurusan" => $jurusan
    ];

    // Menambahkan data ke array
    $mahasiswa[] = $newMahasiswa;

    // Menyimpan kembali ke file JSON
    $saved = file_put_contents(
        $file,
        json_encode(
            $mahasiswa,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        ),
        LOCK_EX
    );

    if ($saved === false) {
        sendResponse(500, [
            "status" => "error",
            "message" => "Gagal menyimpan data mahasiswa"
        ]);
    }

    sendResponse(201, [
        "status" => "success",
        "message" => "Data mahasiswa berhasil ditambahkan",
        "data" => $newMahasiswa
    ]);
}

// METHOD selain GET dan POST
header("Allow: GET, POST");

sendResponse(405, [
    "status" => "error",
    "message" => "Method tidak diizinkan"
]);

