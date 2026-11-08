# BAB 6: ANALISIS DEVSECOPS

**Disusun Oleh:**  
Nama : Haafizd Alhabib Azwir (Habib)  
NIM  : 1302213045  
Peran: DevSecOps Lead & Application Hardening (Anggota 2)  
Mata Kuliah: DevOps (CCK4IBB3) — Fakultas Informatika, Telkom University  
Proyek: ReTide Webpage (Laravel 11)  

---

## 6.1 Threat Modeling Menggunakan Framework STRIDE
Analisis ancaman keamanan (Threat Modeling) dilakukan secara sistematis pada arsitektur aplikasi ReTide (Platform Donasi Konservasi Laut & Marketplace Daur Ulang) menggunakan metodologi **STRIDE** (Microsoft Threat Modeling Standard). 

Berikut adalah pemetaan ancaman, komponen terdampak, tingkat risiko, dan strategi mitigasi konkret yang telah diimplementasikan:

| No | Kategori STRIDE | Komponen Terkait | Skenario Ancaman Spesifik | Tingkat Risiko | Strategi Mitigasi Terimplementasi |
| :---: | :--- | :--- | :--- | :---: | :--- |
| **1** | **Spoofing** (Pemalsuan Identitas) | Auth & User Session (`/login`, `/register`) | Penyerang mencoba memalsukan identitas pengguna atau menebak password admin/donatur via serangan kamus (*brute-force login*). | **High** | 1. Rate Limiting ketat pada route autentikasi (`throttle:auth-limit` max 5 request/menit).<br>2. Hashing password menggunakan algoritma baku Bcrypt dengan per-user salt bawaan Laravel.<br>3. Regenerasi session ID otomatis saat autentikasi berhasil untuk mencegah Session Fixation. |
| **2** | **Tampering** (Manipulasi Data) | Payment Callback & Midtrans Webhook (`/payment/notification`) | Penyerang memanipulasi payload HTTP POST pada endpoint notifikasi pembayaran untuk mengubah status transaksi donasi/marketplace menjadi *settlement* tanpa membayar. | **High** | 1. Verifikasi cryptographic signature key SHA512: `SHA512(order_id + status_code + gross_amount + ServerKey)`.<br>2. Validasi integritas order ID dan status pembayaran sebelum mutasi record database.<br>3. Penerapan CSRF exception hanya pada route webhook yang tervalidasi signature-nya. |
| **3** | **Repudiation** (Penyangkalan Aksi) | Manajemen Donasi, Marketplace, & Transaksi Admin (`DonationController`, `MarketplaceProductController`, `DonationUpdateController`) | Pengguna atau administrator menyangkal telah melakukan penghapusan data donasi, mutasi status pesanan/donasi, atau penghapusan produk katalog. | **Medium** | 1. Implementasi **`SecurityAuditService`** yang mencatat setiap event mutasi kritis (pembuatan donasi, penghapusan data donasi, penghapusan produk, pembaruan status donasi admin, User ID, IP Address, User-Agent, dan Timestamp ISO-8601).<br>2. Dedicated immutable log channel (`storage/logs/security.log`) dengan retensi log selama 30 hari. |
| **4** | **Information Disclosure** (Kebocoran Data) | HTTP Responses, Application Errors, & Cloud Storage | Penyerang memanfaatkan clickjacking, inspeksi MIME-type sniffing, atau kebocoran kredensial API Midtrans/Database di dalam kode sumber publik. | **High** | 1. Implementasi **`SecurityHeadersMiddleware`** yang menyisipkan header: `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `X-XSS-Protection: 1; mode=block`, dan `Content-Security-Policy`.<br>2. Audit seluruh repository menjamin zero hardcoded secret.<br>3. Pemindahan credential rahasia ke **GitHub Secrets**. |
| **5** | **Denial of Service (DoS)** | Submission Donasi & Checkout Cart (`/donation`, `/cart/process`) | Penyerang membanjiri server dengan request pembuatan invoice donasi atau checkout keranjang berulang kali untuk menghabiskan memory, connection pool DB, dan kuota API Midtrans. | **Medium** | 1. Konfigurasi `RateLimiter` di `AppServiceProvider`: `donation-limit` (10 request/menit/IP) dan `checkout-limit` (10 request/menit/user).<br>2. Mengembalikan response status HTTP `429 Too Many Requests` saat limit terlampaui. |
| **6** | **Elevation of Privilege** (Eskalasi Hak Akses) | Admin Panel Dashboard (`/admin/*`) | Pengguna biasa (role `user`) memodifikasi parameter URL atau request state untuk mengakses fitur kelola admin, menghapus donasi, atau mengubah role user lain. | **High** | 1. Penerapan `AdminMiddleware` dan `RedirectIfAdmin` berbasis kolom `role` pada database.<br>2. Strict Route Grouping dengan prefix `/admin` yang memverifikasi kepemilikan hak akses admin sebelum controller dieksekusi. |

---

## 6.2 Dependency Security Check pada Pipeline CI
Sebagai bagian dari penerapan prinsip *Shift-Left Security*, setiap pull request dan commit yang didorong ke branch `main` wajib melalui pemeriksaan celah keamanan pustaka dependensi secara otomatis.

* **Tool yang Digunakan:** `composer audit`
* **Integrasi GitHub Actions:** Dikonfigurasikan pada workflow `.github/workflows/ci.yml`:
  ```yaml
  - name: DevSecOps - Composer Dependency Audit
    run: composer audit --format=plain
  ```
* **Mekanisme Kerja:**
  Perintah ini secara otomatis memeriksa seluruh pustaka di `composer.lock` terhadap basis data *FriendsOfPHP Security Advisories Database* dan *GitHub Advisory Database*. Jika ditemukan dependensi dengan status *CVE Vulnerability (Critical / High)*, pipeline CI akan secara otomatis gagal (*exit code 1*), memblokir proses merge ke branch utama.

---

## 6.3 Pengelolaan Secret dan Kredensial (Secret Management)
Berdasarkan prinsip *Security as Code*, pengelolaan kredensial sensitif dalam proyek ReTide mematuhi aturan berikut:
1. **Zero Hardcoded Credentials:**
   Tidak ada API key, token, database password, atau secret key yang disimpan langsung di file kode program (`.php` atau `.blade.php`).
2. **Environment Variables:**
   Variabel sensitif diakses melalui fungsi `env()` dan file konfigurasi `config/midtrans.php`, `config/database.php`, serta `config/cloudinary.php`.
3. **GitHub Secrets:**
   Variabel lingkungan untuk pipeline otomatis GitHub Actions dikelola melalui antarmuka **Repository Settings -> Secrets and variables -> Actions**:
   * `MIDTRANS_SERVER_KEY`: Server key sandbox/production Midtrans.
   * `MIDTRANS_CLIENT_KEY`: Client key publik Midtrans.
   * `SONAR_TOKEN`: Token autentikasi SonarCloud / SonarQube Scanner.
   * `SONAR_PROJECT_KEY`: Kunci identifikasi proyek SonarCloud.
   * `SONAR_ORGANIZATION`: Organisasi SonarCloud.

---

## 6.4 Branch Protection & Code Review Policy
Untuk memastikan integritas basis kode utama dan mencegah push langsung (*direct commit*) ke lingkungan produksi:
1. **Branch Protection Rules (`main`):**
   * **Require a pull request before merging:** Direct push dinonaktifkan untuk semua anggota (termasuk repository owner).
   * **Require approvals:** Minimal **1 peer reviewer** wajib menyetujui Pull Request sebelum tombol merge aktif.
   * **Require status checks to pass:** Pipeline GitHub Actions (`Build, Audit & Test`) wajib berstatus **hijau / success** sebelum penggabungan kode diizinkan.
2. **Audit & Review Checklist:**
   Setiap Pull Request wajib diverifikasi aspek fungsionalitas, ketersediaan unit test baru, hasil `composer audit`, serta ketiadaan celah keamanan baru pada SonarQube Quality Gate.
