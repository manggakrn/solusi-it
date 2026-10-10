# 🛠️ IT Troubleshooting & Ticketing System (Dockerized)

Aplikasi pencatatan masalah IT (Troubleshooting), manajemen solusi, dan sistem ticketing berbasis PHP 8.2 dan MySQL. Berjalan secara optimal di dalam ekosistem Docker.

## 🚀 Fitur Utama
- **Log Masalah (Troubleshooting Log):** Pencatatan *error*, *bug*, atau komplain sistem IT.
- **CRUD Operations:** Tambah, Edit, Hapus data masalah dan solusinya.
- **Manajemen User:** Sistem autentikasi admin.
- **Upload File/Bukti:** Lampiran foto atau dokumen untuk bukti kerusakan/perbaikan.

## 🛠 Persyaratan Sistem
- Docker & Docker Compose
- MySQL Server (Bisa berjalan via Docker atau langsung di Host)

## 📦 Cara Instalasi & Deploy di Server Baru

**1. Download / Clone Repository**
```bash
git clone https://github.com/manggakrn/solusi-it.git
cd solusi-it
```

**2. Setup Database**
Import file database ke dalam MySQL server Anda (buat database dengan nama `troubleshooting`).
Minta file `database_terbaru.sql` dari admin yang mengelola server utama, lalu import:
```bash
mysql -u root -p troubleshooting < database_terbaru.sql
```

**3. Setup Konfigurasi Database**
Salin file contoh konfigurasi dan isi dengan kredensial database Anda:
```bash
cp config.example.php config.php
nano config.php
```
Ganti nilai `YOUR_DB_HOST`, `YOUR_DB_USER`, dan `YOUR_DB_PASSWORD` dengan kredensial database Anda yang sebenarnya.

**4. Jalankan Aplikasi (Docker Compose)**
```bash
docker-compose up -d
```
Aplikasi akan otomatis menyala dan dapat diakses melalui port **8051** (atau sesuai mapping di dalam file `docker-compose.yml`).

## ⚠️ Catatan Keamanan
- File `config.php` berisi kredensial database dan **TIDAK** ikut di-upload ke GitHub demi keamanan.
- File `database_terbaru.sql` juga tidak di-upload. Hubungi admin server untuk mendapatkan dump database terbaru.
- Gunakan `config.example.php` sebagai template konfigurasi.
