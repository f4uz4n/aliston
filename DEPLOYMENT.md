# Panduan Deployment ke Production

## Instalasi Dependencies

Setelah pull ke production, **WAJIB** menjalankan perintah berikut untuk menginstall dependencies:

```bash
composer install --no-dev --optimize-autoloader
```

Penjelasan:
- `--no-dev`: Tidak menginstall dev dependencies (untuk production)
- `--optimize-autoloader`: Optimize autoloader untuk performa lebih baik

## Dependencies yang Diperlukan

Project ini menggunakan library berikut yang harus terinstall:
- `firebase/php-jwt` (^7.0) - Untuk JWT authentication
- `codeigniter4/framework` (^4.0) - Framework utama

## Troubleshooting

### Error: Class "Firebase\JWT\JWT" not found

**Penyebab:** Library `firebase/php-jwt` belum terinstall di production.

**Solusi:**
1. Pastikan Anda berada di root directory project
2. Jalankan: `composer install --no-dev --optimize-autoloader`
3. Pastikan folder `vendor/` sudah terbuat dan berisi library firebase/php-jwt
4. Clear cache jika diperlukan: `php spark cache:clear`

### Error: Composer tidak ditemukan

**Solusi:**
1. Install Composer di server production
2. Atau gunakan `composer.phar` yang sudah ada
3. Atau install dependencies di local, lalu upload folder `vendor/` ke production (tidak disarankan)

## Checklist Deployment

- [ ] Pull code terbaru dari repository
- [ ] Jalankan `composer install --no-dev --optimize-autoloader`
- [ ] Copy file `.env` dan sesuaikan konfigurasi untuk production
- [ ] Set JWT_SECRET_KEY di `.env` dengan key yang aman
- [ ] Jalankan migration: `php spark migrate`
- [ ] Set permission folder `writable/` menjadi 755 atau 775
- [ ] Clear cache: `php spark cache:clear`
- [ ] Test login untuk memastikan JWT berfungsi

## Konfigurasi .env untuk Production

Pastikan file `.env` memiliki konfigurasi berikut:

```env
# Environment
CI_ENVIRONMENT = production

# JWT Secret Key (WAJIB diubah untuk production!)
JWT_SECRET_KEY=your-very-secure-secret-key-min-32-characters-change-this

# Database
database.default.hostname = your_db_host
database.default.database = your_db_name
database.default.username = your_db_user
database.default.password = your_db_password

# Base URL
app.baseURL = 'https://yourdomain.com/'
```

## Catatan Penting

1. **Jangan commit folder `vendor/`** ke repository (sudah ada di `.gitignore`)
2. **Selalu jalankan `composer install`** setelah pull di production
3. **JWT Secret Key** harus berbeda antara development dan production
4. **Gunakan HTTPS** di production untuk keamanan cookie JWT
