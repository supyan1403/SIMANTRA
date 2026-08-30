import openpyxl
import sqlite3
import os
import sys

sys.stdout.reconfigure(encoding='utf-8')

db_path = r'd:\SIMANTRA\database\database.sqlite'
excel_path = r'd:\SIMANTRA\1. Input MANTRA (Monitoring Alokasi Pekerjaan Dan Honor Mitra) oleh PJ Kegiatan atau Ketua Tim - Update3.xlsx.xlsx'

if not os.path.exists(excel_path):
    print("Excel file not found!")
    sys.exit(1)

print("Opening SQLite database...")
conn = sqlite3.connect(db_path)
cursor = conn.cursor()

print("Opening Excel file (read-only mode)...")
wb = openpyxl.load_workbook(excel_path, read_only=True, data_only=True)
sheet = wb['DB Mitra 2024 (2621)']

print("Syncing SOBAT fields...")
updated_count = 0

for row in sheet.iter_rows(min_row=3, values_only=True):
    if not row or len(row) < 32:
        continue
    
    nama = str(row[1]).strip() if row[1] is not None else ""
    if not nama or nama == 'Nama' or nama == 'None':
        continue
        
    alamat = str(row[2]).strip() if row[2] is not None else ""
    pekerjaan = str(row[4]).strip() if row[4] is not None else ""
    nik = str(row[6]).strip() if row[6] is not None else ""
    posisi = str(row[7]).strip() if row[7] is not None else ""
    email = str(row[9]).strip() if row[9] is not None else ""
    tgl_lahir = str(row[16]).strip() if row[16] is not None else ""
    npwp = str(row[17]).strip() if row[17] is not None else ""
    pendidikan = str(row[21]).strip() if row[21] is not None else ""
    no_hp = str(row[24]).strip() if row[24] is not None else ""
    
    exp_sp = 1 if row[26] == 1 or row[26] == '1' else 0
    exp_st = 1 if row[27] == 1 or row[27] == '1' else 0
    exp_se = 1 if row[28] == 1 or row[28] == '1' else 0
    exp_susenas = 1 if row[29] == 1 or row[29] == '1' else 0
    exp_sakernas = 1 if row[30] == 1 or row[30] == '1' else 0
    exp_sbh = 1 if row[31] == 1 or row[31] == '1' else 0
    
    sobat_id = str(row[35]).strip() if len(row) > 35 and row[35] is not None else ""

    cursor.execute("""
        UPDATE mitras SET
            nik = CASE WHEN ? != '' THEN ? ELSE nik END,
            posisi = CASE WHEN ? != '' THEN ? ELSE posisi END,
            id_sobat = CASE WHEN ? != '' THEN ? ELSE id_sobat END,
            email = CASE WHEN ? != '' THEN ? ELSE email END,
            npwp = CASE WHEN ? != '' THEN ? ELSE npwp END,
            tanggal_lahir = CASE WHEN ? != '' THEN ? ELSE tanggal_lahir END,
            pendidikan = CASE WHEN ? != '' THEN ? ELSE pendidikan END,
            exp_sp = ?,
            exp_st = ?,
            exp_se = ?,
            exp_susenas = ?,
            exp_sakernas = ?,
            exp_sbh = ?
        WHERE nama = ? OR (id_sobat != '' AND id_sobat = ?) OR (nik != '' AND nik = ?)
    """, (
        nik, nik,
        posisi, posisi,
        sobat_id, sobat_id,
        email, email,
        npwp, npwp,
        tgl_lahir, tgl_lahir,
        pendidikan, pendidikan,
        exp_sp, exp_st, exp_se, exp_susenas, exp_sakernas, exp_sbh,
        nama, sobat_id, nik
    ))
    if cursor.rowcount > 0:
        updated_count += cursor.rowcount

conn.commit()
conn.close()

print(f"Sukses! Berhasil menyinkronkan {updated_count} baris data mitra SOBAT BPS ke database SQLite.")
