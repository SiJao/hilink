-- =====================================================================
--  HISADA — SISTEM INFORMASI MANAJEMEN TERPADU
--  Pondok Pesantren Daarul 'Uluum Lido
--  Skema database final (3NF) — versi rapi, mengatasi celah dari
--  rancangan sebelumnya: riwayat kamar/kelas, role bergilir (time-bound),
--  kategori pelanggaran terpisah dari tingkat hukuman, korespondensi,
--  kalender, shortcut, dan audit log.
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS
    audit_logs, push_subscriptions, shortcuts, calendar_events,
    correspondence, achievements, leave_permits, violations,
    violation_categories, medical_records, attendances,
    riwayat_kamar, riwayat_kelas, students, families, rooms, classes,
    role_assignments, roles, users;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 1. USERS & ROLE (Multi-Auth + Role Bergilir/Time-bound)
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    google_id   VARCHAR(255) UNIQUE NULL      COMMENT 'OAuth ID Google SSO (Ustadz/Pengurus)',
    email       VARCHAR(150) UNIQUE NULL      COMMENT 'Wajib @daarululuumlido.com utk staf',
    phone       VARCHAR(20)  UNIQUE NULL      COMMENT 'No. WA — jalur login Wali Santri',
    password    VARCHAR(255) NULL             COMMENT 'Bcrypt hash (dipakai selama SSO belum aktif)',
    name        VARCHAR(150) NOT NULL,
    photo       VARCHAR(255) NULL,
    family_id   INT NULL                      COMMENT 'Diisi jika akun ini adalah Wali Santri',
    is_active   BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE roles (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) UNIQUE NOT NULL COMMENT
        'admin, sekretaris, pengurus, wali_kelas, wali_kamar, hakim, dokter, asisten, wali_santri'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Jabatan bergilir: satu user bisa punya BEBERAPA role, masing-masing
-- dengan periode berlaku sendiri. Ini menggantikan kolom `role` ENUM
-- statis yang jadi celah di rancangan sebelumnya.
CREATE TABLE role_assignments (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT NOT NULL,
    role_id        INT NOT NULL,
    mulai_berlaku  DATE NOT NULL,
    selesai_berlaku DATE NULL COMMENT 'NULL = masih aktif tanpa batas',
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    INDEX idx_role_active (user_id, selesai_berlaku)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 2. MASTER DATA: KELAS, KAMAR, KELUARGA
-- ---------------------------------------------------------------------
CREATE TABLE classes (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(50) NOT NULL COMMENT 'Contoh: 1 A SMP, 5 A MIA',
    level      VARCHAR(20) NULL COMMENT 'SMP, MTs, SMA, MA, TMI',
    teacher_id INT NULL COMMENT 'Wali Kelas',
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE rooms (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL COMMENT 'Contoh: Ibnu Rusyd-01',
    building      VARCHAR(50)  NOT NULL,
    gender        ENUM('L','P') NOT NULL,
    supervisor_id INT NULL COMMENT 'Wali Kamar',
    FOREIGN KEY (supervisor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE families (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    no_kk         VARCHAR(20)  NULL,
    father_nik    VARCHAR(20)  NULL,
    father_name   VARCHAR(150) NOT NULL,
    father_phone  VARCHAR(20)  NOT NULL UNIQUE,
    mother_name   VARCHAR(150) NULL,
    mother_phone  VARCHAR(20)  NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE users
    ADD CONSTRAINT fk_users_family FOREIGN KEY (family_id) REFERENCES families(id) ON DELETE SET NULL;

-- ---------------------------------------------------------------------
-- 3. SANTRI (Data Induk) + RIWAYAT (celah yang diperbaiki)
-- ---------------------------------------------------------------------
CREATE TABLE students (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nis         VARCHAR(30) UNIQUE NOT NULL,
    nisn        VARCHAR(30) NULL,
    name        VARCHAR(150) NOT NULL,
    gender      ENUM('L','P') NOT NULL,
    birth_date  DATE NOT NULL,
    origin      VARCHAR(100) NULL COMMENT 'Asal daerah',
    photo       VARCHAR(255) NULL,
    class_id    INT NOT NULL COMMENT 'Kelas SAAT INI (posisi terkini)',
    room_id     INT NOT NULL COMMENT 'Kamar SAAT INI (posisi terkini)',
    family_id   INT NOT NULL,
    status      ENUM('aktif','alumni','keluar') DEFAULT 'aktif',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id)  REFERENCES classes(id),
    FOREIGN KEY (room_id)   REFERENCES rooms(id),
    FOREIGN KEY (family_id) REFERENCES families(id),
    INDEX idx_nis (nis),
    INDEX idx_student_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Riwayat perpindahan kamar & kelas per tahun ajaran/semester.
-- students.class_id / room_id di atas TETAP dipertahankan sebagai
-- "posisi terkini" untuk mempercepat query harian (absensi, direktori);
-- setiap kali posisi berubah, baris baru ditambahkan di sini oleh aplikasi
-- sehingga histori lama TIDAK tertimpa.
CREATE TABLE riwayat_kelas (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    student_id    INT NOT NULL,
    class_id      INT NOT NULL,
    tahun_ajaran  VARCHAR(9) NOT NULL COMMENT 'Contoh: 2026/2027',
    semester      TINYINT NOT NULL COMMENT '1 atau 2',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE riwayat_kamar (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    student_id    INT NOT NULL,
    room_id       INT NOT NULL,
    tahun_ajaran  VARCHAR(9) NOT NULL,
    semester      TINYINT NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 4. ABSENSI
-- ---------------------------------------------------------------------
CREATE TABLE attendances (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    date       DATE NOT NULL,
    status     ENUM('hadir','sakit','izin','pulang','alpha') NOT NULL DEFAULT 'hadir',
    notes      VARCHAR(255) NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    UNIQUE KEY unique_student_date (student_id, date),
    INDEX idx_attendance_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 5. POSKESTREN
-- ---------------------------------------------------------------------
CREATE TABLE medical_records (
    id                    INT AUTO_INCREMENT PRIMARY KEY,
    student_id            INT NOT NULL,
    complaint             TEXT NOT NULL,
    diagnosis             TEXT NULL,
    prescription          TEXT NULL,
    status                ENUM('rawat_jalan','rawat_inap','rujuk','sembuh') DEFAULT 'rawat_jalan',
    handled_by_assistant  INT NOT NULL,
    handled_by_doctor     INT NULL,
    created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (handled_by_assistant) REFERENCES users(id),
    FOREIGN KEY (handled_by_doctor) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 6. MAHKAMAH — kategori bidang dipisah dari tingkat hukuman
-- ---------------------------------------------------------------------
CREATE TABLE violation_categories (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL COMMENT 'Keamanan, Peribadatan, Kebersihan, Bahasa, dll'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE violations (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    student_id         INT NOT NULL,
    category_id        INT NOT NULL,
    description        TEXT NOT NULL,
    severity           ENUM('ringan','sedang','berat') NULL COMMENT 'Diisi Hakim saat vonis',
    verdict            ENUM('proses','divonis','pemutihan') DEFAULT 'proses',
    revocation_reason  TEXT NULL COMMENT 'Wajib diisi jika verdict = pemutihan',
    recorded_by        INT NOT NULL COMMENT 'Sekretaris (brute input)',
    judged_by          INT NULL COMMENT 'Hakim yang memvonis',
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    judged_at          TIMESTAMP NULL,
    deleted_at         TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES violation_categories(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id),
    FOREIGN KEY (judged_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 7. PERIZINAN
-- ---------------------------------------------------------------------
CREATE TABLE leave_permits (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    student_id          INT NOT NULL,
    type                ENUM('izin_keluar','pulang') NOT NULL,
    start_date          DATETIME NOT NULL,
    end_date            DATETIME NOT NULL,
    actual_return_date  DATETIME NULL,
    reason              TEXT NOT NULL,
    status              ENUM('pending','approved','active','overdue','completed') DEFAULT 'approved',
    approved_by         INT NOT NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 8. PRESTASI
-- ---------------------------------------------------------------------
CREATE TABLE achievements (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    student_id    INT NOT NULL,
    event_name    VARCHAR(150) NOT NULL,
    rank_achieved VARCHAR(50) NOT NULL,
    event_date    DATE NOT NULL,
    location      VARCHAR(150) NULL,
    scope         ENUM('internal','external') NOT NULL,
    description   TEXT NULL,
    created_by    INT NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 9. KORESPONDENSI (belum ada DDL di rancangan sebelumnya)
-- ---------------------------------------------------------------------
CREATE TABLE correspondence (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    direction      ENUM('masuk','keluar') NOT NULL,
    code           VARCHAR(60) NOT NULL COMMENT 'Auto-generate jika keluar, manual jika masuk',
    from_position  VARCHAR(100) NOT NULL,
    letter_type    VARCHAR(60) NOT NULL,
    destination    VARCHAR(150) NOT NULL,
    status         VARCHAR(30) NOT NULL DEFAULT 'draft' COMMENT 'draft/dikirim/selesai/diarsipkan/belum_dibaca/diteruskan/disetujui',
    attachment_url VARCHAR(255) NULL COMMENT 'Wajib pola https://docs.google.com/document/...',
    disposisi      VARCHAR(150) NULL COMMENT 'Khusus surat masuk',
    created_by     INT NOT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_code (code),
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 10. KALENDER (belum ada DDL di rancangan sebelumnya)
-- ---------------------------------------------------------------------
CREATE TABLE calendar_events (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150) NOT NULL,
    description TEXT NULL,
    start_date  DATE NOT NULL,
    end_date    DATE NULL,
    category    ENUM('akademik','pengasuhan','umum') DEFAULT 'umum',
    visibility  ENUM('public','internal') DEFAULT 'public',
    created_by  INT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 11. SHORTCUT DRIVE PER ROLE (belum ada DDL di rancangan sebelumnya)
-- ---------------------------------------------------------------------
CREATE TABLE shortcuts (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(30) NOT NULL COMMENT '"*" berarti tampil untuk semua role',
    label     VARCHAR(100) NOT NULL,
    drive_url VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 12. NOTIFIKASI WEB PUSH
-- ---------------------------------------------------------------------
CREATE TABLE push_subscriptions (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    endpoint    TEXT NOT NULL,
    p256dh      TEXT NOT NULL,
    auth        TEXT NOT NULL,
    device_info VARCHAR(100) NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 13. AUDIT LOG GLOBAL (non-keuangan: mahkamah, absensi, poskestren, dll)
-- ---------------------------------------------------------------------
CREATE TABLE audit_logs (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    target_table VARCHAR(50) NOT NULL,
    target_id    INT NOT NULL,
    action       ENUM('INSERT','UPDATE','SOFT_DELETE') NOT NULL,
    old_data     JSON NULL,
    new_data     JSON NULL,
    user_id      INT NOT NULL,
    reason       TEXT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
--  SEED DATA — untuk demo/uji coba
--  Password demo semua akun di bawah: hisada123
-- =====================================================================

INSERT INTO roles (name) VALUES
('admin'),('sekretaris'),('pengurus'),('wali_kelas'),('wali_kamar'),
('hakim'),('dokter'),('asisten'),('wali_santri');

-- Password hash bcrypt untuk "hisada123" (dibuat via crypt.CRYPT_BLOWFISH,
-- format $2b$ — kompatibel penuh dengan password_verify() di PHP)
SET @demo_hash = '$2b$12$Lao9JZSZA4HlPL3JlFD7AeUEg/jtCqQFCMRedUHikVQqXaxWgaMgW';

INSERT INTO users (id, email, phone, password, name) VALUES
(1, 'admin@daarululuumlido.com', NULL, @demo_hash, 'Admin Utama'),
(2, 'sekretaris@daarululuumlido.com', NULL, @demo_hash, 'Fulan — Sekretaris'),
(3, 'ustadz.faisal@daarululuumlido.com', NULL, @demo_hash, 'Ust. M. Faisal Suhaemi'),
(4, 'ustadz.hakim@daarululuumlido.com', NULL, @demo_hash, 'Ust. Hakim Mahkamah'),
(5, 'dokter.klinik@daarululuumlido.com', NULL, @demo_hash, 'dr. Ahmad Fauzi'),
(6, 'asisten.poskestren@daarululuumlido.com', NULL, @demo_hash, 'Asisten Poskestren'),
(7, NULL, '081234567890', @demo_hash, 'Bapak Abdullah (Wali Santri)');

INSERT INTO role_assignments (user_id, role_id, mulai_berlaku) VALUES
(1, (SELECT id FROM roles WHERE name='admin'), '2026-07-01'),
(2, (SELECT id FROM roles WHERE name='sekretaris'), '2026-07-01'),
(2, (SELECT id FROM roles WHERE name='pengurus'), '2026-07-01'),
(3, (SELECT id FROM roles WHERE name='wali_kelas'), '2026-07-01'),
(3, (SELECT id FROM roles WHERE name='wali_kamar'), '2026-07-01'),
(4, (SELECT id FROM roles WHERE name='hakim'), '2026-07-01'),
(5, (SELECT id FROM roles WHERE name='dokter'), '2026-07-01'),
(6, (SELECT id FROM roles WHERE name='asisten'), '2026-07-01'),
(7, (SELECT id FROM roles WHERE name='wali_santri'), '2026-07-01');

INSERT INTO classes (id, name, level, teacher_id) VALUES
(1, '1 A MTs', 'MTs', 3),
(2, '5 A MIA', 'MA', 3);

INSERT INTO rooms (id, name, building, gender, supervisor_id) VALUES
(1, 'Ibnu Rusyd-01', 'Ibnu Rusyd', 'L', 3),
(2, 'Hj. Sa''diyah-11', 'Hj. Sa''diyah', 'P', 3);

INSERT INTO families (id, father_name, father_phone, mother_name) VALUES
(1, 'Bapak Abdullah', '081234567890', 'Ibu Aminah');

INSERT INTO students (id, nis, nisn, name, gender, birth_date, origin, class_id, room_id, family_id) VALUES
(1, '2026001', '0051234561', 'Muhammad Zaki', 'L', '2012-05-14', 'Bogor', 1, 1, 1),
(2, '2026002', '0051234562', 'Siti Nurhaliza', 'P', '2011-08-20', 'Sukabumi', 2, 2, 1);

INSERT INTO riwayat_kelas (student_id, class_id, tahun_ajaran, semester) VALUES
(1, 1, '2026/2027', 1), (2, 2, '2026/2027', 1);

INSERT INTO riwayat_kamar (student_id, room_id, tahun_ajaran, semester) VALUES
(1, 1, '2026/2027', 1), (2, 2, '2026/2027', 1);

INSERT INTO attendances (student_id, date, status, created_by) VALUES
(1, CURDATE(), 'hadir', 2),
(2, CURDATE(), 'sakit', 2);

INSERT INTO violation_categories (name) VALUES
('Keamanan'), ('Peribadatan'), ('Kebersihan'), ('Bahasa');

INSERT INTO medical_records (student_id, complaint, status, handled_by_assistant) VALUES
(2, 'Demam tinggi sejak semalam', 'rawat_inap', 6);

INSERT INTO achievements (student_id, event_name, rank_achieved, event_date, scope, created_by) VALUES
(1, 'Olimpiade Matematika Kabupaten', 'Juara 1', '2026-07-15', 'external', 2);

INSERT INTO calendar_events (title, description, start_date, category, visibility, created_by) VALUES
('Ujian Tahriri Semester I', 'Seluruh santri mengikuti ujian tulis', '2026-12-12', 'akademik', 'public', 1),
('Rapat Evaluasi Asatidz', 'Rapat internal bulanan', '2026-09-10', 'pengasuhan', 'internal', 1),
('Perizinan Pulang Massal', 'Santri diizinkan pulang', '2026-12-25', 'pengasuhan', 'public', 1);

INSERT INTO shortcuts (role_name, label, drive_url) VALUES
('*', 'Drive: Informasi & Pengumuman', 'https://drive.google.com/'),
('pengurus', 'Drive: Bagian Bahasa', 'https://drive.google.com/'),
('admin', 'Drive: Arsip Admin', 'https://drive.google.com/');
