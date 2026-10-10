#!/bin/bash
# Backup database tertentu MariaDB Docker

# =======================
# Konfigurasi
# =======================
#CONTAINER_NAME="mariadb_keuangan"      # Nama container MariaDB
DB_NAME="troubleshooting"                     # Nama database yang mau dibackup
BACKUP_DIR="/home/monitoring/angga/troubleshooting/database/"
LOG_DIR="/home/monitoring/angga/troubleshooting/database/"
LOG_FILE="$LOG_DIR/backup.log"
DATE=$(date +%F_%H-%M-%S)
FILE_NAME="${DB_NAME}_$DATE.sql"
FULL_PATH="$BACKUP_DIR/$FILE_NAME"

# =======================
# Pastikan folder backup dan log ada
# =======================
mkdir -p "$BACKUP_DIR"
mkdir -p "$LOG_DIR"

# Fungsi untuk mencatat log dengan timestamp
log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
}

log_message "=== Memulai Proses Backup untuk database: '$DB_NAME' ==="

# =======================
# Jalankan backup database tertentu
# =======================
#docker exec -i "$CONTAINER_NAME" mysqldump -u root -p'root123' "$DB_NAME" > "$FULL_PATH"
mysqldump -h 127.0.0.1 -P 3306 -u root -p'angga123' "$DB_NAME" > "$FULL_PATH" 2>> "$LOG_FILE"

# =======================
# Cek hasil backup
# =======================
if [ $? -eq 0 ]; then
    MSG="[SUKSES] Backup database '$DB_NAME' berhasil: $FULL_PATH"
    echo "$MSG"
    log_message "$MSG"
else
    MSG="[GAGAL] Backup gagal! Silakan cek detail error di atas."
    echo "$MSG"
    log_message "$MSG"
    exit 1
fi

# =======================
# Hapus backup lama >7 hari
# =======================
log_message "Memeriksa backup lama untuk dihapus..."
find "$BACKUP_DIR" -type f -name "${DB_NAME}_*.sql" -mtime +7 -exec rm {} \; -print >> "$LOG_FILE" 2>&1

MSG="[INFO] Proses pembersihan selesai. Backup lama (>7 hari) telah dihapus jika ada."
echo "$MSG"
log_message "$MSG"
log_message "=== Proses Selesai ==="
echo "" >> "$LOG_FILE" # Tambah baris kosong di log untuk pemisah antar run