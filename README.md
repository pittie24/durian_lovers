# Durian Lovers — Sistem Informasi Penjualan Berbasis Website

Project Laravel 9 untuk toko online "Durian Lovers" sesuai spesifikasi.

## Fitur Utama
- Landing page guest, login & registrasi pelanggan
- Katalog produk, detail produk, keranjang, checkout
- Simulasi pembayaran Midtrans (Snap token dummy)
- Tracking pesanan, riwayat, dan rating
- Admin panel: dashboard, produk, pesanan, laporan, pelanggan

## Quick Start
1. Salin env
```bash
copy .env.example .env
```
2. Atur koneksi DB di `.env`
```
DB_DATABASE=durian_lovers
DB_USERNAME=root
DB_PASSWORD=
```
3. Generate key
```bash
php artisan key:generate
```
4. Migrasi dan seed
```bash
php artisan migrate --seed
```
5. Jalankan server
```bash
php artisan serve
```

## Akun Demo
- Admin: `admin@durianlovers.test` / `admin123`
- Pelanggan: `pelanggan@durianlovers.test` / `password`

## Midtrans
Tambahkan kunci di `.env` bila ingin integrasi nyata:
```
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
```

Endpoint webhook:
```
POST /payment/midtrans/webhook
```
