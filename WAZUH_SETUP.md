# SECURITY OPERATIONS - WAZUH SETUP

Untuk menghubungkan dashboard ini ke server Wazuh asli, tambahkan konfigurasi berikut ke file `.env` Anda:

```env
WAZUH_URL=https://IP_ADDRESS_WAZUH_ANDA_DISINI:55000
WAZUH_USERNAME=wazuh-wui
WAZUH_PASSWORD=password_wazuh_anda
```

## Catatan Penting
1. **Port API**: Pastikan port `55000` (default API Wazuh) terbuka di firewall server Wazuh Anda.
2. **Self-Signed SSL**: Dashboard ini sudah dikonfigurasi untuk mengabaikan error sertifikat SSL (`verify => false`) karena Wazuh biasanya menggunakan self-signed cert secara default.
3. **Fallback**: Jika koneksi gagal atau kredensial salah, dashboard akan otomatis kembali ke "Simulation Mode" agar tetap terlihat bagus.

## Tes Koneksi
Setelah update .env, buka dashboard SIEM. Jika widget "Active Agents" menampilkan data asli server Anda, berarti koneksi berhasil.
