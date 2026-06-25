#!/bin/bash
# SIOPK Badung — Automated Backup Script
# Cron: 0 2 * * * /var/www/html/scripts/backup.sh >> /var/log/siopk-backup.log 2>&1

set -e

APP_DIR="/var/www/html"
BACKUP_DIR="${APP_DIR}/storage/backups"
RETENTION_DAYS=30
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
DB_NAME="${DB_DATABASE:-siopk_badung}"
DB_USER="${DB_USERNAME:-root}"
DB_PASS="${DB_PASSWORD}"

mkdir -p "${BACKUP_DIR}"

# ── 1. Backup Database ──
echo "[$(date)] Starting database backup..."
mysqldump \
    --user="${DB_USER}" \
    --password="${DB_PASS}" \
    --host="${DB_HOST:-127.0.0.1}" \
    --port="${DB_PORT:-3306}" \
    --single-transaction \
    --routines \
    --triggers \
    --set-gtid-purged=OFF \
    "${DB_NAME}" \
    | gzip > "${BACKUP_DIR}/db_${TIMESTAMP}.sql.gz"

echo "[$(date)] Database backup: db_${TIMESTAMP}.sql.gz ($(du -h ${BACKUP_DIR}/db_${TIMESTAMP}.sql.gz | cut -f1))"

# ── 2. Backup Storage (foto + dokumen OPK) ──
echo "[$(date)] Starting storage backup..."
tar -czf "${BACKUP_DIR}/storage_${TIMESTAMP}.tar.gz" \
    -C "${APP_DIR}/storage/app/public" \
    foto_opk dokumen_opk 2>/dev/null || echo "  (no media files yet)"

echo "[$(date)] Storage backup: storage_${TIMESTAMP}.tar.gz ($(du -h ${BACKUP_DIR}/storage_${TIMESTAMP}.tar.gz | cut -f1))"

# ── 3. Cleanup old backups ──
echo "[$(date)] Cleaning backups older than ${RETENTION_DAYS} days..."
find "${BACKUP_DIR}" -name "*.sql.gz" -mtime +${RETENTION_DAYS} -delete
find "${BACKUP_DIR}" -name "*.tar.gz" -mtime +${RETENTION_DAYS} -delete

echo "[$(date)] Backup complete. Current backups:"
ls -lh "${BACKUP_DIR}" | tail -5
echo "---"
