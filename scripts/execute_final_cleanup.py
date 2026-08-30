import os
import shutil
import sqlite3
import datetime

db_path = 'd:/SIMANTRA/database/database.sqlite'
backup_path = f"d:/SIMANTRA/database/database.sqlite.backup_{datetime.datetime.now().strftime('%Y%m%d_%H%M%S')}"

# 1. Backup SQLite Database
shutil.copyfile(db_path, backup_path)
print(f"Step 1: Database backup successfully created at: {backup_path}")

conn = sqlite3.connect(db_path)
cur = conn.cursor()

# 2. Cleanup Legacy 2023 & Zero Pagu Kegiatans
print("\nStep 2: Cleaning up legacy 2023 & zero pagu kegiatans...")
cur.execute("""
    SELECT id, nama FROM kegiatans
    WHERE tahun = '2023' OR total = 0 OR total IS NULL OR kode_mata_anggaran IS NULL OR kode_mata_anggaran = ''
""")
to_delete = cur.fetchall()
print(f"Found {len(to_delete)} legacy kegiatans to remove.")

for k_id, k_nama in to_delete:
    # Delete allocations if any attached to legacy
    cur.execute("DELETE FROM alokasi_honors WHERE kegiatan_id = ?", (k_id,))
    cur.execute("DELETE FROM kegiatans WHERE id = ?", (k_id,))

# 3. Round all volume in alokasi_honors to pure integer
print("\nStep 3: Standardizing all volume in alokasi_honors to whole integer...")
cur.execute("""
    UPDATE alokasi_honors
    SET volume = CAST(ROUND(volume) AS INTEGER)
""")
print(f"Updated {cur.rowcount} allocation volume records to pure integer.")

# 4. Also ensure all kegiatans jumlah (target volume) are rounded to integer
cur.execute("""
    UPDATE kegiatans
    SET jumlah = CAST(ROUND(jumlah) AS INTEGER)
    WHERE jumlah IS NOT NULL
""")
print(f"Standardized target volume in kegiatans table.")

conn.commit()

# Verification stats
cur.execute("SELECT COUNT(*) FROM kegiatans")
total_keg = cur.fetchone()[0]

cur.execute("SELECT COUNT(*) FROM alokasi_honors")
total_alok = cur.fetchone()[0]

cur.execute("SELECT volume FROM alokasi_honors WHERE volume != CAST(volume AS INTEGER)")
non_int_vol = cur.fetchall()

print("\n=== VERIFIKASI HASIL EKSEKUSI ===")
print(f"Total Master Kegiatan Resmi Aktif : {total_keg}")
print(f"Total Alokasi Penugasan Riil     : {total_alok}")
print(f"Jumlah Alokasi dengan Desimal    : {len(non_int_vol)} (Harus 0)")

conn.close()
