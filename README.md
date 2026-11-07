# Project Pemrograman Database Client Server

## Identitas Project

- **Nama:** Sandy Valentino Gerani
- **NIM:** 2411520010
- **Mata kuliah:** Pemrograman Database Client Server
- **Repository GitHub:** https://github.com/imsaand/pdcs_sandy2411520010
- **Lingkungan pengembangan:** PHP, MySQL, Laragon, phpMyAdmin, Visual Studio Code, Thunder Client, Git, dan GitHub

Project ini berisi pengembangan REST API mahasiswa secara bertahap, mulai dari penyimpanan JSON hingga operasi CRUD menggunakan database MySQL.

## Struktur Folder Project

```text
pdcs_sandy2411520010/
├── mahasiswa.php              # Pertemuan 1: API berbasis JSON
├── data_mahasiswa.json        # Data mahasiswa Pertemuan 1
├── pertemuan_2/
│   ├── api/
│   │   └── mahasiswa.php      # API MySQL: GET, POST, PUT, DELETE
│   ├── helpers/
│   │   └── response.php       # Helper respons JSON
│   └── config.php             # Koneksi database MySQL
└── README.md
```

## Pertemuan 1 — Git dan REST API Berbasis JSON

### Deskripsi

Pada Pertemuan 1, dibuat REST API sederhana dengan PHP yang menyimpan data mahasiswa dalam file JSON. API mendukung GET untuk membaca data mahasiswa dan POST untuk menambahkan mahasiswa baru.

### Endpoint

`http://localhost/pdcs_sandy2411520010/mahasiswa.php`

| Method | Fungsi | Hasil pengujian |
|---|---|---|
| GET | Mengambil seluruh data mahasiswa dari JSON | 200 OK |
| POST | Menambahkan data mahasiswa ke file JSON | 201 Created |
| GET ulang | Memastikan data yang baru ditambahkan tersimpan | 200 OK |

### Data Pengujian

- Sandy Valentino Gerani — Teknik Informatika
- Andi Pratama — Sistem Informasi

### Kesimpulan

API PHP berbasis JSON berhasil dibuat dan diuji menggunakan Thunder Client. Source code dan dokumentasi Pertemuan 1 disimpan menggunakan Git dan GitHub.

---

## Pertemuan 2 — REST API dengan Database MySQL

### Deskripsi

Pada Pertemuan 2, penyimpanan dan pengambilan data mahasiswa dikembangkan menggunakan database relasional MySQL. API mengambil informasi mahasiswa beserta nama jurusan melalui `LEFT JOIN`.

### Database

- **Nama database:** `pdcs_sandy`
- **Tabel:** `jurusan`, `mahasiswa`, dan `matakuliah`
- **Relasi:** `mahasiswa.jurusan_id` dan `matakuliah.jurusan_id` mengacu ke `jurusan.id`.

### Berkas dan Fungsinya

| Berkas | Fungsi |
|---|---|
| `pertemuan_2/config.php` | Mengatur koneksi PHP ke MySQL |
| `pertemuan_2/helpers/response.php` | Menyediakan fungsi `sendResponse()` untuk respons JSON |
| `pertemuan_2/api/mahasiswa.php` | Menyediakan endpoint mahasiswa |

### Endpoint

`http://localhost/pdcs_sandy2411520010/pertemuan_2/api/mahasiswa.php`

### Hasil Pengujian

| Pengujian | Hasil |
|---|---|
| Koneksi PHP–MySQL | Berhasil |
| GET seluruh mahasiswa | 200 OK |
| `LEFT JOIN` mahasiswa dengan jurusan | Berhasil |
| Response JSON | Berhasil |
| Git commit dan push Pertemuan 2 | Berhasil |

### Kesimpulan

REST API PHP berhasil membaca data mahasiswa dari MySQL dan menampilkan nama jurusan melalui `LEFT JOIN`. Struktur `config.php`, helper respons, dan endpoint API menjadi dasar pengembangan CRUD pada materi berikutnya.

---

## Materi Lanjutan — RESTful API CRUD (7 November 2026)

### Acuan Materi

*Konsep RESTful API: Operasi CRUD dengan GET, POST, PUT & DELETE* — Rezky Yuranda.

### Tujuan

Mengembangkan endpoint mahasiswa agar mendukung proses **Create, Read, Update, Delete (CRUD)** dengan `switch-case`, Prepared Statement, validasi input, pemeriksaan relasi, serta respons JSON menggunakan HTTP status code yang tepat.

### Daftar Endpoint

Semua URL berikut menggunakan alamat dasar:

`http://localhost/pdcs_sandy2411520010/pertemuan_2/api/mahasiswa.php`

| Method | Parameter | Fungsi |
|---|---|---|
| GET | Tidak ada | Mengambil seluruh mahasiswa |
| GET | `?id=1` | Mengambil mahasiswa berdasarkan ID |
| GET | `?search=Sandy` | Mencari mahasiswa berdasarkan nama atau NIM |
| POST | Body JSON | Menambahkan mahasiswa baru |
| PUT | `?id=4` dan Body JSON | Memperbarui mahasiswa berdasarkan ID |
| DELETE | `?id=4` | Menghapus mahasiswa berdasarkan ID |

**Catatan:** ID 4 merupakan data dummy yang digunakan saat praktik dan telah dihapus setelah pengujian. URL PUT/DELETE dengan ID 4 dicantumkan sebagai contoh historis pengujian, bukan data yang masih tersedia.

### Konsep yang Diterapkan

1. **Routing `switch-case`:** membedakan request GET, POST, PUT, dan DELETE, serta menolak method lain dengan HTTP 405.
2. **Prepared Statement:** menggunakan placeholder `?` dan parameter binding untuk query yang menerima input pengguna, guna mengurangi risiko SQL Injection.
3. **GET by ID:** mengambil satu mahasiswa menggunakan binding parameter integer (`i`).
4. **GET Search:** mencari nama atau NIM menggunakan `LIKE ?`, wildcard `%`, dan binding string (`ss`).
5. **POST:** membaca body JSON, memeriksa field wajib dan keberadaan jurusan, mencegah NIM duplikat, lalu menjalankan INSERT dengan binding `ssi`.
6. **PUT:** memeriksa ID mahasiswa, field JSON, jurusan, dan NIM duplikat (kecuali record sendiri), lalu menjalankan UPDATE dengan binding `ssii`.
7. **DELETE:** memeriksa ID dan keberadaan mahasiswa sebelum menjalankan query DELETE menggunakan Prepared Statement.
8. **Penanganan error:** memakai helper `sendResponse()` dan fungsi `gagalQuery()` untuk memberikan respons JSON yang konsisten.

### Ringkasan Pengujian Thunder Client

| Skenario | Status HTTP aktual | Hasil |
|---|---|---|
| GET seluruh mahasiswa | 200 OK | Berhasil |
| GET mahasiswa dengan ID 1 | 200 OK | Berhasil |
| GET ID 999 tidak ditemukan | 404 Not Found | Sesuai harapan |
| GET Search nama `Sandy` | 200 OK | Berhasil |
| GET Search NIM `2026002` | 200 OK | Berhasil |
| GET Search `TidakAda999` | 200 OK, array `[]` | Sesuai harapan |
| POST tambah mahasiswa dummy | 201 Created | Berhasil, ID baru 4 |
| POST NIM duplikat | 409 Conflict | Sesuai harapan |
| POST tanpa field `nim` | 400 Bad Request | Sesuai harapan |
| POST jurusan ID 999 | 404 Not Found | Sesuai harapan |
| PUT update mahasiswa ID 4 | 200 OK | Berhasil |
| GET verifikasi hasil PUT | 200 OK | Data berubah sesuai permintaan |
| PUT tanpa parameter `id` | 400 Bad Request | Sesuai harapan |
| PUT mahasiswa ID 999 | 404 Not Found | Sesuai harapan |
| PUT jurusan ID 999 | 404 Not Found | Sesuai harapan |
| PUT NIM milik mahasiswa lain | 409 Conflict | Sesuai harapan |
| DELETE tanpa parameter `id` | 400 Bad Request | Sesuai harapan |
| DELETE mahasiswa ID 999 | 404 Not Found | Sesuai harapan |
| DELETE mahasiswa dummy ID 4 | 200 OK | Berhasil |
| DELETE ulang ID 4 | 404 Not Found | Sesuai harapan |
| GET verifikasi ID 4 setelah DELETE | 404 Not Found | Data sudah tidak ditemukan |
| GET seluruh mahasiswa setelah DELETE | 200 OK | Tiga mahasiswa awal tetap ada |
| PATCH (method tidak diizinkan) | 405 Method Not Allowed | Sesuai harapan |

### Verifikasi Data Akhir

Setelah mahasiswa dummy ID 4 dihapus, GET seluruh mahasiswa kembali menampilkan tiga data awal:

| ID | Nama | NIM | Jurusan |
|---|---|---|---|
| 1 | Sandy Valentino Gerani | 2411520010 | Teknik Informatika |
| 2 | Budi Santoso | 2026002 | Teknik Informatika |
| 3 | Siti Aminah | 2026003 | Sistem Informasi |

### Batasan Implementasi

- Project ini dibuat untuk pembelajaran dan belum dimaksudkan sebagai API produksi.
- Validasi field pada contoh materi masih berfokus pada keberadaan field; nilai string kosong dan format yang tidak valid memerlukan validasi tambahan.
- Pada database saat pengujian, kolom NIM belum ditunjukkan memiliki constraint `UNIQUE`. Pemeriksaan duplikasi melalui kode PHP sudah diuji, tetapi untuk penggunaan nyata sebaiknya juga diberlakukan keunikan pada tingkat database.
- GET seluruh mahasiswa tidak membutuhkan parameter dari pengguna; Prepared Statement digunakan pada query yang menerima input, seperti pencarian, INSERT, UPDATE, dan DELETE.

### Kesimpulan

Implementasi RESTful API mahasiswa menggunakan PHP dan MySQL berhasil diuji melalui Thunder Client. GET, POST, PUT, dan DELETE telah bekerja pada skenario utama, termasuk validasi 400, 404, 409, serta 405 untuk method yang tidak diizinkan. Data mahasiswa dummy digunakan dan dihapus setelah pengujian; tiga mahasiswa awal tetap tersedia.

**Status akhir:** Implementasi dan pengujian CRUD selesai. Dokumentasi README telah disiapkan; commit dan push perubahan materi CRUD ke GitHub masih perlu dilakukan.
