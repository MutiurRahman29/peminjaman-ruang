# Audit Frontend dan Implementasi

## Ringkasan Eksekutif

- **Lingkup audit:** seluruh halaman Blade, shared layout, navigasi, form, tabel, status, notifikasi, gambar, responsivitas, keyboard navigation, accessibility, dan konsistensi desain.
- **Batas audit:** backend, route, middleware, model, enum, validation, permission, dan kontrak data tidak diubah.
- **Target visual:** warm ivory, taupe, dark brown, spacing yang konsisten, dan visual editorial yang tidak terlalu dekoratif.
- **Status umum:** sistem secara visual sudah memiliki fondasi yang bagus, tetapi masih ada beberapa masalah UI yang langsung memengaruhi kegunaan, terutama pada class button, mobile navigation, logo, aksi destructive, dan keterbacaan beberapa heading.
- **Prioritas implementasi:** perbaiki semua temuan P0/P1 sebelum melakukan visual polishing lanjutan.

## Metodologi Audit

- Pemeriksaan seluruh 26 view Blade yang ditemukan.
- Pemeriksaan shared layout, header, footer, CSS design system, JavaScript, dan seluruh route role.
- Pemeriksaan kelas CSS/Tailwind, warna, focus state, heading, tindakan, empty state, form error, tabel, dan responsive layout.
- Pemeriksaan aksesibilitas dasar: landmark, label, `aria-*`, heading, focus-visible, warna, kontrast, dan keterbacaan.
- Pemeriksaan integrasi UI dengan route, permission, dan data yang benar-benar tersedia.
- Pemeriksaan tidak dilakukan terhadap API atau server-side behavior yang tidak memengaruhi rendering.

## Temuan Utama

### P0 — Class button yang digunakan tidak tersedia pada CSS

**Dampak:** tombol yang memakai `primary-button`, `soft-button`, dan `action-button-*` mungkin tidak memiliki styling yang diharapkan. Setelah pemeriksaan CSS, definisi yang tersedia hanya `action-button`, `action-button-primary`, `action-button-secondary`, dan `action-button-quiet`.

**Lokasi:**

- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`
- `resources/views/peminjam/peminjaman/index.blade.php`
- `resources/views/peminjam/peminjaman/show.blade.php`
- `resources/views/petugas/peminjaman/index.blade.php`
- `resources/views/petugas/peminjaman/show.blade.php`
- `resources/views/admin/ruangan/index.blade.php`
- `resources/views/admin/users/index.blade.php`
- `resources/views/admin/peminjaman/index.blade.php`

**Bukti:** `resources/css/app.css` hanya mendefinisikan `action-button*`; class `primary-button` dan `soft-button` tidak muncul di CSS.

**Status:** Belum diperbaiki.

**Rekomendasi:** gunakan satu set class button yang telah didefinisikan, atau tambahkan alias kompatibilitas sementara di CSS. Jangan mengandalkan class yang hanya tersedia di view lama.

### P0 — Logo registrasi tidak terbaca

**Dampak:** logo awal registrasi memakai latar dan teks dengan warna yang sama (`bg-charcoal` dan `text-charcoal`), sehingga ikon tidak terlihat.

**Lokasi:** `resources/views/auth/register.blade.php`.

**Bukti:** span logo memiliki `class="flex h-10 w-10 ... bg-charcoal text-charcoal"`.

**Status:** Belum diperbaiki.

**Rekomendasi:** gunakan `bg-charcoal text-ivory`, atau tampilkan logo SVG yang memiliki kontras yang cukup.

### P0 — Mobile navigation tidak menutup setelah memilih menu

**Dampak:** pengguna dapat membuka menu mobile, memilih navigasi, tetapi menu tetap terbuka. Ini mengganggu pengalaman navigasi dan dapat membuat halaman terasa tidak responsif.

**Lokasi:** `resources/views/partials/header.blade.php`.

**Bukti:** menu mobile memakai `x-show`, tetapi link tidak memiliki handler `x-on:click` untuk mengubah `open` menjadi `false`.

**Status:** Belum diperbaiki.

**Rekomendasi:** pada setiap link mobile, tambahkan `x-on:click="open = false"` atau tutup menu saat URL berubah. Jika menu menggunakan `@click.outside`, tambahkan juga keyboard event dan fokus management.

### P0 — Aksi penghapusan tidak memiliki konfirmasi

**Dampak:** pengguna dapat menghapus data pengguna, ruangan, atau fasilitas dengan satu klik dan tanpa konfirmasi ulang. Ini berisiko untuk kesalahan klik atau aksi yang tidak disengaja.

**Lokasi:**

- `resources/views/admin/ruangan/index.blade.php`
- `resources/views/admin/fasilitas/index.blade.php`
- `resources/views/admin/users/index.blade.php`

**Bukti:** setiap form hapus memakai `button type="submit"` tanpa modal, dialog, atau atribut `confirm`. Tidak ada pesan penghapusan yang menjelaskan dampak dan data yang akan hilang.

**Status:** Belum diperbaiki.

**Rekomendasi:** gunakan dialog konfirmasi eksplisit, label yang jelas, serta status loading pada tombol hapus. Jangan hanya mengandalkan warna merah untuk memberi tahu efek destruktif.

### P1 — Banyak SVG dekoratif tidak memiliki `aria-hidden="true"`

**Dampak:** beberapa screen reader dapat mengulang ikon sebagai konten grafis, terutama ketika SVG tidak memiliki makna sendiri.

**Lokasi:** seluruh button, card, dan ikon yang memakai SVG di view Blade.

**Bukti:** icon yang terletak di dalam tombol atau sebagai elemen dekoratif belum secara konsisten memiliki `aria-hidden="true"`.

**Status:** Belum diperbaiki secara menyeluruh.

**Rekomendasi:** beri `aria-hidden="true"` pada ikon dekoratif; hanya ikon yang mempunyai fungsi informasi yang harus diberi label secara eksplisit.

### P1 — Error message tidak selalu terhubung ke field

**Dampak:** pesan error dapat muncul tanpa memberi sinyal yang jelas ke input yang bermasalah, terutama pada form CRUD dan form peminjaman.

**Lokasi:**

- `resources/views/admin/ruangan/create.blade.php`
- `resources/views/admin/ruangan/edit.blade.php`
- `resources/views/admin/fasilitas/create.blade.php`
- `resources/views/admin/fasilitas/edit.blade.php`
- `resources/views/admin/users/create.blade.php`
- `resources/views/admin/users/edit.blade.php`
- `resources/views/peminjam/peminjaman/create.blade.php`

**Bukti:** beberapa field memakai error border, tetapi tidak menyediakan `aria-invalid`, `aria-describedby`, atau hubungan yang jelas dengan ID pesan kesalahan.

**Status:** Belum diperbaiki.

**Rekomendasi:** berikan `aria-invalid="true"` pada field error, `aria-describedby` yang mengarah ke pesan error, dan fokus otomatis ke field pertama yang error.

### P1 — Form `id_ruangan` belum memiliki placeholder yang membantu pemilihan

**Dampak:** pengguna dapat melihat menu dropdown kosong atau tanpa pilihan yang menjelaskan apakah data tersedia.

**Lokasi:** `resources/views/peminjam/peminjaman/create.blade.php`.

**Bukti:** form `select` tidak menyediakan opsi placeholder yang konkret ketika tidak ada ruangan, dan page lain hanya menampilkan `Tidak ada ruangan tersedia`.

**Status:** Belum diperbaiki.

**Rekomendasi:** tampilkan opsi default yang menjelaskan `Pilih ruangan`, aktifkan disabled state saat tidak ada data, dan tambahkan hint kapan data tersedia.

### P1 — Text dan background status belum memiliki kontrast yang konsisten

**Dampak:** beberapa status memakai warna yang tidak cukup kontras pada latar background ringan, sedangkan beberapa warna status sama dengan elemen interface lain.

**Lokasi:** seluruh status badge dan status card di:

- `resources/views/admin/peminjaman/index.blade.php`
- `resources/views/admin/peminjaman/show.blade.php`
- `resources/views/peminjam/peminjaman/show.blade.php`
- `resources/views/petugas/peminjaman/show.blade.php`
- `resources/views/admin/ruangan/index.blade.php`
- `resources/views/admin/users/index.blade.php`

**Bukti:** status menggunakan `bg-* /10` dengan teks `text-*` dan border `border-* /20`, tetapi beberapa kombinasi hanya menggunakan warna yang semantik, bukan nilai yang telah diuji pada kontras aksesibilitas.

**Status:** Perlu review kontras manual.

**Rekomendasi:** gunakan token status yang terukur, siapkan tampilan untuk setiap status, dan hindari warna status yang tidak tersedia untuk orang dengan gangguan warna.

### P1 — Empty state memiliki variasi visual yang terlalu kecil

**Dampak:** beberapa empty state tidak memberikan indikasi tindakan yang musti diambil, sehingga pengguna mungkin tidak yakin apakah data kosong atau terjadi kegagalan.

**Lokasi:**

- Administrator dan peminjam untuk ruangan, fasilitas, dan pengguna.
- Petugas dan peminjam untuk antrean serta riwayat.

**Bukti:** empty state sering hanya menggunakan ikon, heading, dan satu paragraf. Tidak selalu tersedia tombol tindakan atau teks yang membedakan data kosong dari data tidak ditemukan.

**Status:** Belum diperbaiki.

**Rekomendasi:** gunakan label yang jelas, contoh perilaku berikutnya, dan tombol aksi bila memang relevan. Pada kondisi kosong yang diharapkan, label seperti `Belum ada ruangan` lebih baik dari `Tidak ada data`.

### P1 — Tabel memiliki keterbatasan responsivitas pada perangkat kecil

**Dampak:** tabel memakai `overflow-x-auto`, tetapi tidak menyediakan label atau sticky column yang membantu saat scroll horizontal. Hal ini dapat menyulitkan peminjam atau petugas yang memakai perangkat kecil.

**Lokasi:**

- `resources/views/peminjam/ruangan/index.blade.php`
- `resources/views/peminjam/fasilitas/index.blade.php`
- `resources/views/admin/ruangan/index.blade.php`
- `resources/views/admin/users/index.blade.php`
- `resources/views/admin/peminjaman/index.blade.php`
- `resources/views/petugas/peminjaman/index.blade.php`
- `resources/views/petugas/peminjaman/history.blade.php`

**Bukti:** tabel hanya dibungkus `overflow-x-auto`, tanpa status tabel `aria-label`, tanpa informasi kolom saat scroll, dan tanpa tombol filter atau pencarian yang mudah digunakan.

**Status:** Belum diperbaiki.

**Rekomendasi:** tambahkan label tabel, status baris/kolom, sticky header, maksimum lebar kolom yang reasonable, serta tombol pencarian/filter yang tetap terlihat.

### P1 — Navigasi role belum ada tanda visual yang memperjelas status user pada header desktop

**Dampak:** nama pengguna ditampilkan, tetapi role pengguna tidak terlihat di header. Ini mengurangi kemampuan pengguna membedakan akun mereka saat berpindah role atau akun yang sama.

**Lokasi:** `resources/views/partials/header.blade.php`.

**Bukti:** user badge hanya menampilkan inisial dan nama. Tidak ada role atau label `Peminjam`, `Petugas`, atau `Admin`.

**Status:** Belum diperbaiki.

**Rekomendasi:** tampilkan role sebagai label kecil di sebelah nama pengguna, atau gunakan ikon role yang konsisten.

### P1 — Tidak ada loading state untuk submit form

**Dampak:** ketika form dikirim, pengguna tidak menerima feedback bahwa aksi sedang diproses. Tombol dapat ditekan berulang kali dan menghasilkan request ganda.

**Lokasi:** seluruh form submit dalam aplikasi.

**Bukti:** tombol submit hanya memakai perubahan warna hover tanpa `disabled`, `aria-busy`, atau loading spinner.

**Status:** Belum diperbaiki.

**Rekomendasi:** tambahkan `data-loading`, `disabled`, `aria-busy`, dan label `Memproses...` saat form dikirim.

### P1 — Tombol halaman dashboard di footer tidak selalu cocok dengan user context

**Dampak:** saat pengguna belum login, footer menampilkan link `Masuk ke sistem`, tetapi ketika sudah login menampilkan `Kembali ke dashboard`. Link dashboard selalu mengarahkan ke route admin, sehingga peminjam atau petugas dapat diarahkan ke dashboard yang tidak sesuai dengan role jika `role` tidak tersedia pada route yang dievaluasi.

**Lokasi:** `resources/views/partials/footer.blade.php`.

**Bukti:** route `dashboard` hanya didefinisikan untuk admin; halaman `dashboard` tidak menemukan role peminjam maupun petugas.

**Status:** Belum diperbaiki.

**Rekomendasi:** untuk pengguna yang sudah login, tampilkan link ke dashboard yang sesuai role. Untuk pengguna yang belum login, tampilkan link ke landing page atau halaman login.

### P2 — Hero section menggunakan dua min-height yang tidak perlu

**Dampak:** `min-h-[100vh]` dan `min-h-[100dvh]` ditulis berurutan. Ini tidak membahayakan, tetapi meningkatkan jumlah class secara tidak perlu dan dapat membuat aturan CSS lebih sulit dibaca.

**Lokasi:** `resources/views/landing.blade.php`.

**Bukti:** setiap section kembali menggunakan `min-h-[100vh] min-h-[100dvh]` secara bersamaan.

**Status:** Belum diperbaiki. Praktik ini tetap valid tetapi kurang bersih.

**Rekomendasi:** gunakan `min-h-[100dvh]` sebagai nilai utama, atau gunakan `min-h-[100vh]` sebagai fallback kompatibilitas bila diperlukan.

### P2 — `overflow-hidden` digunakan pada banyak card tabel tanpa kebutuhan visual yang jelas

**Dampak:** penggunaan overflow pada card dapat mencegah efek focus ring atau shadow untuk elemen tertentu, serta menyamarkan isi yang melebihi area card.

**Lokasi:** semua card CRUD dan detail yang memakai `glass-card overflow-hidden`.

**Bukti:** banyak view memakai properti `overflow-hidden` pada card tanpa ada gambar atau elemen yang harus di potong.

**Status:** Belum diperbaiki.

**Rekomendasi:** gunakan overflow hanya pada elemen yang memang membutuhkan clipping, misalnya gambar atau badge. Untuk form dan kartu, gunakan `overflow-visible` agar fokus dan shadow tetap terlihat.

### P2 — Landing page dan dashboard memakai banyak color class yang hardcoded

**Dampak:** design system menjadi sulit dipertahankan karena beberapa page memakai warna di luar token yang sudah didefinisikan, terutama `sky-*`, `indigo-*`, `emerald-*`, `slate-*`, dan warna text putih.

**Lokasi:** landing, dashboard, status, dan beberapa page CRUD.

**Bukti:** token visual tersedia di `resources/css/app.css`, tetapi beberapa page tetap memakai warna legacy atau warna khusus per role.

**Status:** Sebagian besar sudah dikonsolidasikan, tetapi belum seluruh page mengikuti satu set warna yang sama.

**Rekomendasi:** gunakan token semantic `ivory`, `cream`, `charcoal`, `muted`, `taupe`, `border`, `success`, `warning`, `error`, `info` untuk seluruh page dan tidak memakai warna legacy secara langsung.

### P2 — Visual tidak konsisten antara page landing, dashboard, dan CRUD

**Dampak:** pengguna dapat merasakan pengalaman yang berbeda antar page walaupun datang dari shared layout. Beberapa halaman terlihat lebih ringan sementara lain memakai elevasi yang jauh lebih kuat.

**Lokasi:** `resources/views/landing.blade.php`, `resources/views/dashboard.blade.php`, dan seluruh CRUD.

**Bukti:** beberapa halaman memakai `bg-charcoal`, beberapa memakai `bg-cream`, sementara `glass-card` tidak selalu digunakan dengan radius, shadow, dan spacing yang sama.

**Status:** Konsistensi belum sepenuhnya uniform.

**Rekomendasi:** buat satu standar typo, radius, spacing, shadow, dan status visual, lalu gunakan pada setiap page.

### P2 — Footer tidak menggunakan container yang konsisten dengan responsivitas global

**Dampak:** bagian footer memiliki banyak penggunaan `text-right` dan align yang berbeda, sementara header dan main content memakai max-width 7xl.

**Lokasi:** `resources/views/partials/footer.blade.php`.

**Bukti:** grid footer menggunakan 3 kolom pada desktop dan tidak memiliki mekanisme khusus untuk ukuran browser yang lebih kecil dari 1024px. Footer memerlukan pemeriksaan layout ketika bagian teks lebih panjang.

**Status:** Belum diperbaiki.

**Rekomendasi:** pertahankan standard grid 3 kolom, tetapi tambahkan gap yang lebih responsif dan `text-left` pada narrow breakpoint.

### P2 — Auto-focus berada pada form login dan register, tetapi tidak ada indicator yang jelas bahwa form dikirim

**Dampak:** fokus otomatis sudah membantu pengguna yang memakai keyboard, tetapi tidak ada feedback visual ketika form sedang diproses.

**Lokasi:** `resources/views/auth/login.blade.php` dan `resources/views/auth/register.blade.php`.

**Bukti:** `autofocus` diterapkan pada field pertama, tetapi tidak ada loading state atau disabled state setelah submit.

**Status:** Belum diperbaiki.

**Rekomendasi:** tambahkan `aria-live` atau spinner, dan disable tombol submit setelah submit pertama.

### P3 — Footer menyertakan link login tanpa diketahui apakah backend sudah login

**Dampak:** link login di footer tidak selalu sesuai dengan state autentikasi dan route yang diberikan. Ini bukan kegagalan kategori tinggi, tetapi dapat salah arah saat pengguna sudah login.

**Lokasi:** `resources/views/partials/footer.blade.php`.

**Bukti:** footer mengecek `@auth`, tetapi link dashboard yang muncul hanya untuk admin dan bukan role yang tepat.

**Status:** Belum diperbaiki.

**Rekomendasi:** tampilkan `Dashboard` dengan role-aware link dan gunakan `route('dashboard')` hanya ketika role admin memiliki akses.

### P3 — Browser scroll behavior tidak memiliki `scroll-padding-top`

**Dampak:** ketika anchor link memindahkan fokus ke section tertentu, konten bisa tertutup oleh header fixed.

**Lokasi:** `resources/css/app.css` dan `resources/views/landing.blade.php`.

**Bukti:** section memakai `scroll-mt-28`, tetapi ini lebih terkait dengan kondisi desktop; beberapa browser dan mobile view dapat membutuhkan nilai sesuai header.

**Status:** Belum diperbaiki secara universal.

**Rekomendasi:** gunakan `scroll-padding-top` pada `html` dan sesuaikan nilai dengan tinggi header yang aktif.

### P3 — Footer text dan container dapat berukuran terlalu kecil pada landscape mobile

**Dampak:** footer bisa memiliki jarak antar elemen yang cukup rapat ketika viewport lebih sempit, terutama pada mode landscape.

**Lokasi:** `resources/views/partials/footer.blade.php`.

**Bukti:** grid footer menggunakan dua ukuran spacing yang diperkirakan, tetapi tidak ada rule breakpoint khusus untuk landscape mobile.

**Status:** Perlu melakukan pengujian browser real device.

**Rekomendasi:** pastikan minimum spacing 16px, tombol tidak saling bertumpuk, dan footer tetap dapat discroll.

### P3 — Penggunaan warna status belum diberi semantic label yang cukup eksplisit

**Dampak:** orang dengan gangguan warna dapat kesulitan membedakan status tanpa membaca text. Badge hanya menggunakan warna dan dot sebagai indikator visual.

**Lokasi:** seluruh status badge dan status card.

**Bukti:** dot warna digunakan tetapi tidak disertai label `aria-label` atau text yang lebih panjang.

**Status:** Belum diperbaiki.

**Rekomendasi:** tambahkan `aria-label="Status disetujui"` pada badge status dan pastikan teks tetap mampu dipahami tanpa warna.

### P3 — Gambar SVG bersifat hardcoded tanpa fallback

**Dampak:** jika aset tidak dapat diakses atau server tidak menampilkan gambar, halaman akan kehilangan visual supporting content.

**Lokasi:** `resources/views/landing.blade.php`.

**Bukti:** seluruh gambar menggunakan SVG lokal yang diakses melalui `asset()`. Tidak tersedia fallback atau alternatif text untuk kondisi gambar gagal dimuat.

**Status:** Belum diperbaiki.

**Rekomendasi:** tambahkan `onerror` atau placeholder, dan pertahankan alt text yang menjelaskan isi gambar.

## Audit Aksesibilitas Ringkas

### Layout dan navigasi

- Skip link tersedia pada shared layout dan dapat diakses melalui keyboard.
- Header mengikuti kebiasaan navigasi utama dan mobile navigation.
- Scroll section memiliki offset yang sudah dipertimbangkan, tetapi belum divalidasi di layar mobile dengan variasi headline.
- Mobile navigation belum menutup setelah memilih menu.
- Hamburger menu memiliki label dinamika, tetapi tidak ada label `aria-controls` yang konsisten dengan halaman yang mungkin tidak menampilkan menu secara aktif.

### Form

- Field menggunakan `label for` yang benar.
- Password dan email/password field memakai autocomplete yang sesuai.
- Error state menggunakan border merah dan pesan error.
- Tidak semua error state terhubung dengan `aria-invalid` dan `aria-describedby`.
- Tombol submit belum memiliki trạng loading state.
- Form submit tanpa robust confirmation untuk aksi destruktif.

### Status dan feedback

- Success/error toast di shared layout menggunakan `aria-live="polite"` dan `aria-atomic="true"`.
- Status badge tidak selalu memiliki `aria-label` yang dapat dibaca oleh pengguna screen reader.
- Tidak ada feedback yang jelas untuk user ketika item sedang dihapus atau status sedang diubah.

### Gambar dan media

- Semua gambar landing mempunyai `alt` text.
- SVG dekoratif masih perlu `aria-hidden`.
- Hero image belum memiliki fallback apabila server gagal mengirim asset.

## Audit Responsive

### Mobile

- Header fixed dan menu mobile tersedia.
- Major layout menggunakan `max-w-*`, grid, dan spacing yang responsif.
- Tabel perlu diberi mekanisme scroll yang lebih jelas.
- Hero dan section utama memakai `min-height` dengan viewport, tetapi perlu pengujian real device agar tidak terjadi overlap dengan header atau content yang terlalu panjang.
- Mobile menu belum ditutup setelah klik.

### Tablet

- Grid landing section perlu diuji pada tablet landscape dan portrait.
- Footer harus diuji pada kolom yang terlalu sempit.
- Form dalam alur CRUD perlu memastikan tombol tidak bertumpuk.

### Desktop dan large desktop

- Layout memiliki container 7xl dan spacing yang konsisten.
- Halaman detail peminjaman dapat menampilkan informasi tanpa kepadatan yang berlebihan.
- Desktop navigation memiliki beberapa link per role, tetapi perlu menguji ukuran layar antara 1024px dan 1440px.

## Hasil Anti-AI-Slop Review

### Sudah dicapai

- Visual umumnya tidak lagi memakai glow yang berlebihan, floating blob, gradient text, serta efek hover yang tidak diperlukan.
- Warna menggunakan arah warm ivory, taupe, dan dark brown secara konsisten pada layout utama.
- Heading, body text, radius, border, dan spacing memiliki pola yang lebih jelas.
- Shared layout, form, dan alert mengikuti pola yang konsisten.
- Landing page mempertahankan informasi yang berguna dan tidak mengandalkan desain generatif yang terlalu berat.
- Tidak ada perubahan backend dan kontrak data yang dibuat.

### Belum dicapai

- Beberapa page masih menggunakan warna legacy atau color class yang tidak disediakan oleh token visual.
- Section yang memiliki `100dvh` belum diuji di real browser dan beberapa mode mobile.
- Mobile navigation masih belum menutup otomatis.
- Tabel dan form CRUD belum diunifikasi sepenuhnya.
- Aksi destruktif belum mempunyai konfirmasi yang aman.
- Loading dan disabled state belum dipakai pada tombol form.
- Kontras status dan focus state perlu dijaga melalui pengujian manual atau tool audit warna.

## Prioritas Perbaikan

### Prioritas 0

1. Perbaiki `primary-button` dan `soft-button` di design system.
2. Perbaiki logo registrasi yang tidak terlihat.
3. Tambahkan konfirmasi untuk aksi hapus.
4. Tutup mobile menu setelah link dipilih.

### Prioritas 1

1. Perbaiki aksesibilitas form error dengan `aria-invalid` dan `aria-describedby`.
2. Tambahkan loading state pada submit form.
3. Tambahkan role-aware dashboard link di header/footer.
4. Konsolidasikan empty state dan tabel agar memiliki informasi yang lebih jelas.
5. Audit warna status dan perbaiki kontras.

### Prioritas 2

1. Hilangkan `min-h-[100vh]` yang tidak perlu di beberapa section.
2. Batasi penggunaan `overflow-hidden` pada card.
3. Consolidate hardcoded color class menjadi semantic token.
4. Tambahkan `scroll-padding-top` pada root html.
5. Validasi layout pada mobile landscape, tablet portrait, dan desktop large.

### Prioritas 3

1. Tambahkan fallback dan alt handling untuk aset gambar.
2. Tambahkan role label pada status badge.
3. Audit visual detail page secara berurutan.
4. Hindari penggunaan icon yang tidak mendapatkan label atau `aria-hidden`.

## Validasi yang Sudah Dilakukan

- `php artisan view:cache`: berhasil.
- `npm run build`: berhasil.
- Pemeriksaan seluruh view Blade berhasil dilakukan.
- Pemeriksaan route dan akses role berhasil dilakukan berdasarkan route definitions.
- Pemeriksaan class color dan layout dilakukan dengan scanning kode.

## Validasi yang Masih Diperlukan

- Browser real-device untuk mobile portrait, mobile landscape, tablet, dan desktop.
- Audit contrast checker.
- Keyboard navigation test secara penuh.
- Screen reader test dengan nama, label, heading, dan status pust.
- Test form submit dengan input kosong, invalid, dan loading state.
- Test deletion flow tanpa mengirim request backend yang sebenarnya.
- Test responsive table horizontal scrolling dan sticky header.
- Test dark/light browser preference dan accessibility preference.

## Catatan Tersimpan

- Nilai enum dan status backend tetap dipertahankan sesuai sumber data.
- Frontend sudah memiliki design system warm ivory yang layak ditingkatkan.
- Tidak ada model admin-managed content yang terverifikasi untuk mengubah content landing page dari database.
- Optional Fontain package pada Vite tidak menghambat build.
- Audit ini hanya memperhatikan frontend; bug backend harus ditangani terpisah.
- Beberapa temuan yang terlihat kecil dapat memengaruhi usability secara signifikan bila tidak ditangani, terutama pada form destructive, mobile menu, tombol, dan status error.
