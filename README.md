# Spreadsheet Notes - Offline + Online

Aplikasi spreadsheet sederhana untuk membuat catatan dengan kemampuan menyimpan data ke file JSON.

## 🚀 Cara Menjalankan

### Opsi 1: Menggunakan PHP Built-in Server (Recommended)

1. **Pastikan PHP sudah terinstall** di komputer Anda
   - Cek dengan menjalankan: `php -v` di Command Prompt
   - Jika belum ada, download dari: https://www.php.net/downloads.php

2. **Jalankan server lokal:**
   - **Windows:** Double-click file `start-server.bat`
   - **Manual:** Buka Command Prompt di folder ini, lalu jalankan:
     ```bash
     php -S localhost:8000
     ```

3. **Buka browser** dan akses:
   ```
   http://localhost:8000/index.html
   ```

### Opsi 2: Menggunakan XAMPP/WAMP/MAMP

1. **Copy semua file** ke folder `htdocs` (XAMPP) atau `www` (WAMP/MAMP)
2. **Start Apache** dari control panel XAMPP/WAMP/MAMP
3. **Buka browser** dan akses:
   ```
   http://localhost/note/index.html
   ```
   (sesuaikan path sesuai struktur folder Anda)

### Opsi 3: Menggunakan Python Simple HTTP Server (Alternatif)

Jika tidak ada PHP, bisa menggunakan Python:

```bash
# Python 3
python -m http.server 8000

# Python 2
python -m SimpleHTTPServer 8000
```

**Catatan:** Opsi ini hanya untuk testing HTML/CSS/JS saja. Untuk menggunakan fitur save/load data, tetap perlu PHP server karena `api.php` memerlukan PHP.

## 📁 Struktur File

```
note/
├── index.html      # File utama aplikasi
├── api.php         # API untuk save/load data ke data.json
├── data.json       # File penyimpanan data (akan dibuat otomatis)
├── start-server.bat # Script untuk menjalankan server (Windows)
└── README.md       # File ini
```

## ⚠️ Penting

**JANGAN membuka `index.html` langsung dari file explorer** (double-click file HTML).

Browser akan memblokir akses ke `api.php` karena kebijakan CORS ketika file dibuka dengan protokol `file://`.

**Selalu gunakan server lokal** (localhost) untuk menjalankan aplikasi ini.

## 🔧 Troubleshooting

### Error: "php is not recognized"
- Install PHP atau tambahkan PHP ke PATH environment variable
- Atau gunakan XAMPP/WAMP yang sudah include PHP

### Error: "Access to fetch blocked by CORS policy"
- Pastikan Anda membuka aplikasi melalui `http://localhost:8000` bukan `file://`
- Pastikan server sudah berjalan

### Error: "Failed to save data"
- Pastikan folder memiliki permission write untuk membuat/mengubah `data.json`
- Pastikan `api.php` dapat diakses (cek di browser: `http://localhost:8000/api.php`)

## 📝 Fitur

- ✅ Tambah baris dan kolom
- ✅ Edit cell langsung (contenteditable)
- ✅ Simpan data ke `data.json` via API
- ✅ Load data dari `data.json` saat halaman dibuka
- ✅ Reset ke data default
- ✅ Hapus baris/kolom terakhir

## 💾 Penyimpanan Data

Data disimpan di file `data.json` dalam format JSON. File ini akan dibuat otomatis saat pertama kali menyimpan data.
