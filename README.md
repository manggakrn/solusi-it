# IT Troubleshooting & Ticketing System (Dockerized)

Aplikasi pencatatan masalah IT, manajemen solusi, dan sistem ticketing berbasis PHP 8.2 dan MySQL.

## Fitur Utama

- Log Masalah (Troubleshooting Log)
- CRUD Operations (Tambah, Edit, Hapus)
- Manajemen User & Autentikasi
- Upload File/Bukti (foto, dokumen)

## Persyaratan

- Docker & Docker Compose

## Struktur Folder

```
solusi-it/
├── database/
│   └── init.sql            # Database schema + data (auto-import)
├── uploads/                # Folder upload file/gambar
├── .env.example            # Template konfigurasi (COPY ke .env)
├── .gitignore              # File yang tidak di-commit
├── config.example.php      # Template koneksi database
├── config.php              # TIDAK DI-COMMIT (copy dari example)
├── docker-compose.yml      # Konfigurasi Docker
└── *.php                   # File aplikasi
```

## Cara Deploy di Server Baru

### 1. Clone Repository

```bash
git clone https://github.com/manggakrn/solusi-it.git
cd solusi-it
```

### 2. Setup Environment

```bash
# Copy template konfigurasi
cp .env.example .env
cp config.example.php config.php

# Edit sesuai kebutuhan
nano .env
nano config.php
```

Isi file `.env`:
```env
APP_PORT=8051
DB_HOST=localhost
DB_NAME=troubleshooting
DB_USER=your_user
DB_PASS=your_password
```

### 3. Jalankan Aplikasi

```bash
docker-compose up -d
```

Tunggu sekitar 30 detik untuk inisialisasi, lalu akses:
```
http://<IP-SERVER>:8051
```

### 4. Import Database (jika belum ada)

```bash
# Masuk ke MySQL dan import
mysql -u root -p troubleshooting < database/init.sql
```

## Perintah Berguna

```bash
# Stop aplikasi
docker-compose down

# Restart aplikasi
docker-compose restart

# Lihat log
docker-compose logs -f

# Masuk ke container
docker exec -it troubleshooting bash
```

## Catatan Keamanan

- File `.env` dan `config.php` berisi kredensial dan **tidak di-commit** ke Git
- Ganti password default sebelum deploy ke production
- Backup database secara berkala
