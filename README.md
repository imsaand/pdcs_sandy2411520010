
# Project Pemrograman Database Client Server

## Pertemuan 1 - Git dan REST API

### Identitas Project
- Nama: Sandy Valentino Gerani
- Mata Kuliah: Pemrograman Database Client Server
- Tugas pertemuam 1.


### Deskripsi Project
Project ini merupakan implementasi REST API sederhana menggunakan PHP dengan penyimpanan data mahasiswa dalam file JSON.

API memiliki dua fungsi utama:
1. GET untuk mengambil data mahasiswa.
2. POST untuk menambahkan data mahasiswa baru.

### Teknologi yang Digunakan
- PHP
- JSON
- Git dan GitHub
- Laragon
- Visual Studio Code
- Thunder Client

### Struktur Folder
- README.md
- mahasiswa.php
- data_mahasiswa.json

### Endpoint API
URL:
http://localhost/pdcs_sandy2411520010/mahasiswa.php

**1. GET - Mengambil Data Mahasiswa**
- Method: GET
- Fungsi: Menampilkan seluruh data mahasiswa.
- Hasil pengujian: 200 OK.

**2. POST - Menambahkan Data Mahasiswa**
- Method: POST
- Fungsi: Menambahkan data mahasiswa baru.
- Format: JSON.
- Hasil pengujian: 201 Created.

### Hasil Pengujian
Pengujian dilakukan menggunakan Thunder Client.

| Pengujian | Hasil |
|---|---|
| GET | 200 OK |
| POST | 201 Created |
| GET ulang | 200 OK |
| Penyimpanan JSON | Berhasil |

Data mahasiswa yang berhasil diuji:
1. Sandy Valentino Gerani - Teknik Informatika.
2. Andi Pratama - Sistem Informasi.

### Kesimpulan
Implementasi REST API menggunakan PHP berhasil dilakukan. API dapat mengambil data mahasiswa menggunakan metode GET dan menambahkan data menggunakan metode POST. Data berhasil disimpan dalam file JSON dan dapat ditampilkan kembali melalui endpoint API.
