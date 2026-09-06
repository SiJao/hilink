# HISADA — Blueprint Skema & Arsitektur (v2)
### Sistem Informasi Manajemen Terpadu Pondok Pesantren Daarul 'Uluum Lido

> Versi ini mencerminkan kondisi kode & database yang sudah dibangun sejauh ini (`hisada_database.sql`, `index.php`, `dashboard.php`, `poskestren.php`, `mahkamah.php`, `portalwali.php`), bukan lagi rancangan di atas kertas.

---

## 1. Peta Arsitektur File

```
index.php  ──(login)──►  cek role aktif  ──►  redirect sesuai tugas
                                │
        ┌───────────────┬──────┴───────┬──────────────────┐
        ▼                ▼              ▼                  ▼
 dashboard.php      poskestren.php  mahkamah.php      portalwali.php
 admin/sekretaris/   dokter/         hakim              wali_santri
 pengurus/wali        asisten
 kelas/wali kamar
```

Setiap file `.php` berdiri sendiri (PHP+HTML+CSS+JS dalam satu file), tapi memakai desain visual yang sama (emerald/gold, Fraunces+Inter) dan skema database yang sama. `dashboard.php` tetap memuat modul Poskestren & Mahkamah juga (untuk admin yang perlu mengawasi semua), sedangkan `poskestren.php`/`mahkamah.php` adalah versi ringkas khusus role tersebut.

---

## 2. Skema Database (ERD per Domain)

### 2.1 Identitas & Akses
```
users              (id, google_id, email, phone, password, name, photo,
                    family_id → families, teacher_id → teachers, is_active)
roles              (id, name)
role_assignments   (id, user_id, role_id, mulai_berlaku, selesai_berlaku)
```
- Satu user bisa punya **banyak role**, masing-masing dengan periode aktif sendiri (jabatan bergilir).
- `users.teacher_id` menaut ke direktori guru **hanya kalau** guru itu juga punya akun login — kebanyakan guru tidak perlu ini.

### 2.2 Direktori Guru/Asatidz (baru — terpisah dari akun login)
```
teachers  (id, code, name, gender, phone, email, position, subject,
           photo, join_date, status, user_id → users NULL)
```
- `classes.teacher_id` dan `rooms.supervisor_id` menunjuk ke sini, **bukan** ke `users` — supaya Wali Kelas/Wali Kamar bisa dicatat meski tidak punya login sistem.

### 2.3 Master Data & Santri
```
classes       (id, name, level, teacher_id → teachers)
rooms         (id, name, building, gender, supervisor_id → teachers)
families      (id, no_kk, father_name, father_phone, mother_name, mother_phone)
students      (id, nis, nisn, name, gender, birth_date, origin, photo,
               class_id, room_id, family_id, status)
riwayat_kelas (id, student_id, class_id, tahun_ajaran, semester)
riwayat_kamar (id, student_id, room_id, tahun_ajaran, semester)
```
- `students.class_id`/`room_id` = posisi **terkini**; setiap kali berubah (lewat form Edit Santri), baris baru otomatis masuk ke `riwayat_*` — histori lama tidak tertimpa.

### 2.4 Absensi (kini bersesi)
```
attendances (id, student_id, date, session_type, status, notes, created_by)
             UNIQUE (student_id, date, session_type)
```
- `session_type`: `kamar_pagi`, `kamar_malam`, `kbm`, `kegiatan` — satu santri bisa punya beberapa baris absensi per hari (satu per sesi).

### 2.5 Poskestren
```
medical_records (id, student_id, complaint, diagnosis, prescription,
                  status, handled_by_assistant, handled_by_doctor)
```
- Alur: Asisten input keluhan → Dokter melengkapi diagnosa/resep → status `rawat_inap` otomatis mencerminkan ke `attendances` sebagai "sakit".

### 2.6 Mahkamah (kategori & katalog hukuman terpisah)
```
violation_categories (id, name)
punishments           (id, label, severity_hint)
violations            (id, student_id, category_id, description, severity,
                        punishment_given, verdict, revocation_reason,
                        recorded_by, judged_by, judged_at, deleted_at)
```
- Sekretaris brute-input pelanggaran → Hakim vonis dengan **centang katalog hukuman** + kolom manual → hasil digabung ke `punishment_given`.
- Pemutihan = soft delete berjejak (`deleted_at` + `revocation_reason`), lewat popup alasan.

### 2.7 Perizinan
```
leave_permits (id, student_id, type, start_date, end_date,
                actual_return_date, reason, status, approved_by)
```
- Menerbitkan izin otomatis menulis `attendances` hari itu jadi "izin", dan menyediakan link cetak surat A6.

### 2.8 Prestasi, Korespondensi, Kalender, Pendukung
```
achievements      (id, student_id, event_name, rank_achieved, event_date,
                    location, scope, description, created_by)
correspondence    (id, direction, code, from_position, letter_type,
                    destination, status, attachment_url, disposisi, created_by)
calendar_events   (id, title, description, start_date, end_date,
                    category, visibility, created_by)
shortcuts         (id, role_name, label, drive_url)
push_subscriptions(id, user_id, endpoint, p256dh, auth, device_info)
audit_logs        (id, target_table, target_id, action, old_data, new_data,
                    user_id, reason)
```

---

## 3. Alur Login & Routing per Role

| Role | Redirect setelah login | Modul yang bisa diakses |
|---|---|---|
| admin | `dashboard.php` | Semua modul + Kelola Pengguna, CRUD Santri/Guru |
| sekretaris | `dashboard.php` | Absensi²*, Mahkamah (input), Korespondensi, CRUD Santri/Guru |
| pengurus | `dashboard.php` | Absensi, Perizinan, Prestasi, Mahkamah (input), Korespondensi |
| wali_kelas / wali_kamar | `dashboard.php` | Absensi, Direktori, Guru (lihat) |
| hakim | `mahkamah.php` | Vonis, hukuman, pemutihan |
| dokter / asisten | `poskestren.php` | Input pasien, diagnosa, check-out |
| wali_santri | `portalwali.php` | Data anak sendiri (read-only), agenda publik |

*Prioritas redirect: role staf umum (admin/sekretaris/pengurus/wali kelas/wali kamar) menang lebih dulu kalau seseorang punya kombinasi role, karena mereka butuh akses dashboard penuh.

---

## 4. Status Modul Saat Ini

| Modul | Status |
|---|---|
| Login multi-role + redirect per tugas | ✅ |
| Direktori Santri (live search 2 detik, CRUD, riwayat kelas/kamar) | ✅ |
| Direktori Guru (CRUD, terpisah dari akun login) | ✅ |
| Absensi (kartu sesi: pagi/malam/KBM/kegiatan) | ✅ |
| Poskestren (estafet Asisten→Dokter, checkout) | ✅ (dashboard.php & poskestren.php) |
| Mahkamah (katalog hukuman, pemutihan via popup) | ✅ (dashboard.php & mahkamah.php) |
| Perizinan (cetak surat A6) | ✅ |
| Prestasi | ✅ |
| Korespondensi (nomor otomatis, validasi lampiran) | ✅ |
| Kalender (grid bulanan, 1/2 bulan/semester/tahun ajaran) | ✅ |
| Kelola Pengguna (edit penuh + role time-bound) | ✅ |
| Portal Wali Santri terpisah | ✅ |
| Modul Kegiatan (Tahsin/Muhadhoroh/Pramuka/Ekskul) | ⛔ belum dirancang |
| Google SSO domain | 🟡 skema siap (`google_id`), implementasi OAuth belum |
| Notifikasi Web Push | 🟡 tabel siap, pengiriman belum diimplementasi |
| Audit log global | 🟡 tabel siap, belum ditulis otomatis oleh aksi-aksi sensitif |

---

## 5. Yang Masih Perlu Diperhatikan

1. **Modul Kegiatan** (Tahsin, Muhadhoroh, Pramuka, Ekskul) masih kosong total — ini area terbesar yang belum tersentuh dari seluruh rencana awal.
2. **Audit log** — perubahan sensitif (edit user, hapus santri/guru, pemutihan) belum otomatis tercatat ke `audit_logs` meski tabelnya sudah ada.
3. **Google SSO** — kolom `google_id` sudah disiapkan, tapi login staf masih pakai email+password manual sampai OAuth benar-benar dipasang.
4. **Notifikasi real-time** (Asisten→Dokter, Sekretaris→Hakim) masih manual (staf harus membuka halaman), belum ada push notification aktif.
5. **Multi-anak per Wali Santri** sudah didukung (`portalwali.php` menampilkan semua anak dari satu `family_id`), tapi belum ada uji dengan lebih dari 2 anak dalam satu keluarga.

---

*Dokumen ini menggantikan blueprint sebelumnya (`HISADA_Blueprint_Rapih.md`) dan mengikuti kondisi kode terbaru per revisi ke-3 (CRUD Santri/Guru, redirect multi-file, live search, kalender grid).*
