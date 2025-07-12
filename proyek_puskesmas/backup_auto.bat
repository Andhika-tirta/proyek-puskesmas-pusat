@echo off
:: Ambil jam dan menit
set hh=%time:~0,2%
if "%hh:~0,1%"==" " set hh=0%hh:~1,1%
set yymmdd_hhmm=%date:~6,4%%date:~3,2%%date:~0,2%_%hh%%time:~3,2%

:: Pindah ke folder mysqldump
cd /d C:\xampp\mysql\bin

:: Jalankan backup (masukkan password langsung setelah -p)
mysqldump -u root db_dinkes_kota > C:\xampp\htdocs\proyek_puskesmas\backup_data\db_dinkes_kota_%yymmdd_hhmm%.sql

:: Tampilkan hasil
echo Backup berhasil: C:\xampp\htdocs\proyek_puskesmas\backup_data\db_dinkes_kota_%yymmdd_hhmm%.sql
