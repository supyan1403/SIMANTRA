import sqlite3
import calendar

db_path = 'd:/SIMANTRA/database/database.sqlite'
conn = sqlite3.connect(db_path)
cur = conn.cursor()

print("=== SINKRONISASI TANGGAL MULAI & SELESAI PADA TABEL KEGIATANS ===")
cur.execute("SELECT id, nama, tahun FROM kegiatans")
kegiatans = cur.fetchall()

updated_count = 0
for k_id, k_nama, k_tahun in kegiatans:
    year = int(k_tahun) if k_tahun and str(k_tahun).isdigit() else 2024
    
    # Check active months in alokasi_honors
    cur.execute("""
        SELECT DISTINCT p.bulan_angka 
        FROM alokasi_honors a
        JOIN periodes p ON a.periode_id = p.id
        WHERE a.kegiatan_id = ? AND p.bulan_angka IS NOT NULL
        ORDER BY p.bulan_angka ASC
    """, (k_id,))
    months = [r[0] for r in cur.fetchall() if r[0]]
    
    if months:
        min_m = min(months)
        max_m = max(months)
    else:
        # Check if month indicated in name (e.g. Susenas Maret -> month 3)
        nama_lower = str(k_nama).lower()
        if 'januari' in nama_lower or 'jan' in nama_lower: min_m = max_m = 1
        elif 'februari' in nama_lower or 'feb' in nama_lower: min_m = max_m = 2
        elif 'maret' in nama_lower or 'mar' in nama_lower: min_m = max_m = 3
        elif 'april' in nama_lower or 'apr' in nama_lower: min_m = max_m = 4
        elif 'mei' in nama_lower: min_m = max_m = 5
        elif 'juni' in nama_lower or 'jun' in nama_lower: min_m = max_m = 6
        elif 'juli' in nama_lower or 'jul' in nama_lower: min_m = max_m = 7
        elif 'agustus' in nama_lower or 'agu' in nama_lower: min_m = max_m = 8
        elif 'september' in nama_lower or 'sep' in nama_lower: min_m = max_m = 9
        elif 'oktober' in nama_lower or 'okt' in nama_lower: min_m = max_m = 10
        elif 'november' in nama_lower or 'nopember' in nama_lower: min_m = max_m = 11
        elif 'desember' in nama_lower or 'des' in nama_lower: min_m = max_m = 12
        else:
            # Default whole year
            min_m = 1
            max_m = 12
            
    last_day_max_m = calendar.monthrange(year, max_m)[1]
    
    tgl_mulai = f"{year:04d}-{min_m:02d}-01"
    tgl_selesai = f"{year:04d}-{max_m:02d}-{last_day_max_m:02d}"
    
    cur.execute("""
        UPDATE kegiatans 
        SET tgl_mulai = ?, tgl_selesai = ?
        WHERE id = ?
    """, (tgl_mulai, tgl_selesai, k_id))
    updated_count += 1

conn.commit()
print(f"Berhasil menyinkronkan {updated_count} tanggal kegiatan.")

# Verify sample
cur.execute("SELECT id, nama, tgl_mulai, tgl_selesai FROM kegiatans LIMIT 10")
for r in cur.fetchall():
    print(f"- [ID {r[0]:2d}] {r[1]:45s} | Mulai: {r[2]} | Selesai: {r[3]}")

conn.close()
