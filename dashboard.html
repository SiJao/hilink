<?php
/**
 * HISADA — Dashboard Utama (Admin/Pengurus/Sekretaris/Wali Kelas/Wali Kamar)
 * Satu file utuh: PHP (logika + query) + HTML + CSS + JS.
 *
 * Navigasi antar-modul memakai query string (?tab=...) yang dirender ulang
 * di server. Live search (Direktori Santri/Guru) memakai AJAX fragment
 * (?ajax=1) supaya tidak reload seluruh halaman.
 *
 * Role dokter/asisten, hakim, dan wali_santri diarahkan index.php ke file
 * terpisah (poskestren.php, mahkamah.php, portalwali.php). File ini tetap
 * memuat modul yang sama untuk admin/pengurus yang perlu mengawasi semuanya.
 */

// ------------------------------------------------------------------
// KONFIGURASI DATABASE — GANTI SESUAI HOSTING (samakan di semua file .php)
// ------------------------------------------------------------------
const DB_HOST = 'localhost';
const DB_NAME = 'cpnpmuy3608_hisada_database';
const DB_USER = 'cpnpmuy3608_hisada';
const DB_PASS = 'Dulido1996';

mysqli_report(MYSQLI_REPORT_OFF);
session_start();

if (empty($_SESSION['uid'])) {
    header('Location: index.php');
    exit;
}

$mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_errno) {
    die('Tidak bisa terhubung ke database (' . $mysqli->connect_error . '). Periksa konfigurasi DB_HOST/DB_NAME/DB_USER/DB_PASS di dashboard.php.');
}
$mysqli->set_charset('utf8mb4');

function h(?string $v): string
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}
function safePrepare(mysqli $mysqli, string $sql): mysqli_stmt
{
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        die('Query gagal disiapkan: ' . h($mysqli->error) . '<br><small>SQL: ' . h($sql) . '</small>');
    }
    return $stmt;
}
function hasRole(array $needed, array $userRoles): bool
{
    return count(array_intersect($needed, $userRoles)) > 0;
}
function isStaffOnly(array $userRoles): bool
{
    return count(array_diff($userRoles, ['wali_santri'])) > 0;
}

$UID       = (int) $_SESSION['uid'];
$NAME      = $_SESSION['name'];
$ROLES     = $_SESSION['roles'] ?? [];
$FAMILY_ID = $_SESSION['family_id'] ?? null;

$IS_WALI_ONLY   = !isStaffOnly($ROLES) && in_array('wali_santri', $ROLES, true);
$EDITOR_ROLES   = ['admin','sekretaris','pengurus','wali_kelas','wali_kamar','hakim','dokter','asisten'];
$DATA_MGMT_ROLES = ['admin','sekretaris']; // boleh tambah/edit/hapus data santri & guru

// ------------------------------------------------------------------
// DEFINISI TAB & HAK AKSES
// ------------------------------------------------------------------
$TABS = [
    'beranda'       => ['label' => 'Beranda',          'mark' => 'B', 'roles' => null],
    'direktori'     => ['label' => 'Direktori Santri',  'mark' => 'D', 'roles' => ['admin','sekretaris','pengurus','wali_kelas','wali_kamar','hakim','dokter','asisten']],
    'guru'          => ['label' => 'Direktori Guru',    'mark' => 'G', 'roles' => ['admin','sekretaris','pengurus','wali_kelas','wali_kamar']],
    'absensi'       => ['label' => 'Absensi',           'mark' => 'A', 'roles' => ['admin','pengurus','wali_kelas','wali_kamar']],
    'poskestren'    => ['label' => 'Poskestren',        'mark' => 'K', 'roles' => ['admin','dokter','asisten']],
    'mahkamah'      => ['label' => 'Mahkamah Santri',   'mark' => 'M', 'roles' => ['admin','sekretaris','pengurus','hakim']],
    'perizinan'     => ['label' => 'Perizinan',         'mark' => 'I', 'roles' => ['admin','pengurus']],
    'prestasi'      => ['label' => 'Prestasi',          'mark' => 'P', 'roles' => ['admin','pengurus']],
    'korespondensi' => ['label' => 'Korespondensi',     'mark' => 'S', 'roles' => ['admin','pengurus','sekretaris']],
    'kalender'      => ['label' => 'Kalender',          'mark' => 'T', 'roles' => null],
    'anak_saya'     => ['label' => 'Data Anak Saya',    'mark' => 'N', 'roles' => ['wali_santri']],
    'pengguna'      => ['label' => 'Kelola Pengguna',   'mark' => 'U', 'roles' => ['admin']],
];

function tabAllowed(string $key, array $tabs, array $userRoles): bool
{
    if (!isset($tabs[$key])) return false;
    $need = $tabs[$key]['roles'];
    if ($need === null) return true;
    return hasRole($need, $userRoles);
}

// ------------------------------------------------------------------
// FRAGMEN AJAX — live search Direktori Santri & Guru (dipanggil via fetch())
// ------------------------------------------------------------------
function studentQuery(mysqli $mysqli, string $search, string $filterClass, string $filterRoom): array
{
    $sql = "SELECT s.*, c.name class_name, r.name room_name FROM students s
            JOIN classes c ON c.id = s.class_id JOIN rooms r ON r.id = s.room_id
            WHERE s.status='aktif'";
    $params = []; $types = '';
    if ($search !== '') { $sql .= " AND (s.name LIKE ? OR s.nis LIKE ?)"; $like = "%$search%"; $params[] = $like; $params[] = $like; $types .= 'ss'; }
    if ($filterClass !== '') { $sql .= " AND s.class_id = ?"; $params[] = $filterClass; $types .= 'i'; }
    if ($filterRoom !== '') { $sql .= " AND s.room_id = ?"; $params[] = $filterRoom; $types .= 'i'; }
    $sql .= " ORDER BY s.name";
    $stmt = safePrepare($mysqli, $sql);
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function renderStudentGrid(array $students, bool $canManage): void
{
    if (empty($students)) { echo '<p class="empty-state">Tidak ada santri yang cocok dengan pencarian.</p>'; return; }
    foreach ($students as $s) {
        echo '<div class="student-card" onclick="openIdCard(this)"'
            . ' data-id="' . (int)$s['id'] . '"'
            . ' data-name="' . h($s['name']) . '"'
            . ' data-nis="' . h($s['nis']) . '"'
            . ' data-nisn="' . h($s['nisn']) . '"'
            . ' data-class="' . h($s['class_name']) . '"'
            . ' data-room="' . h($s['room_name']) . '"'
            . ' data-class-id="' . (int)$s['class_id'] . '"'
            . ' data-room-id="' . (int)$s['room_id'] . '"'
            . ' data-gender="' . ($s['gender'] === 'L' ? 'Putra' : 'Putri') . '"'
            . ' data-origin="' . h($s['origin']) . '"'
            . ' data-dob="' . h($s['birth_date']) . '"'
            . ' data-status="' . h($s['status']) . '">';
        echo '<div class="top"><div class="initial">' . h(mb_substr($s['name'], 0, 1)) . '</div><div>'
            . '<div class="name">' . h($s['name']) . '</div><div class="nis">NIS ' . h($s['nis']) . '</div></div></div>';
        echo '<div class="tags"><span class="tag">' . h($s['class_name']) . '</span><span class="tag">' . h($s['room_name']) . '</span></div>';
        echo '</div>';
    }
}

function teacherQuery(mysqli $mysqli, string $search): array
{
    $sql = "SELECT * FROM teachers WHERE status='aktif'";
    $params = []; $types = '';
    if ($search !== '') { $sql .= " AND (name LIKE ? OR position LIKE ? OR subject LIKE ?)"; $like = "%$search%"; $params = [$like,$like,$like]; $types = 'sss'; }
    $sql .= " ORDER BY name";
    $stmt = safePrepare($mysqli, $sql);
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function renderTeacherGrid(array $teachers, bool $canManage): void
{
    if (empty($teachers)) { echo '<p class="empty-state">Belum ada data guru.</p>'; return; }
    foreach ($teachers as $t) {
        echo '<div class="student-card" onclick="openTeacherCard(this)"'
            . ' data-id="' . (int)$t['id'] . '"'
            . ' data-name="' . h($t['name']) . '"'
            . ' data-code="' . h($t['code']) . '"'
            . ' data-gender="' . ($t['gender'] === 'L' ? 'Laki-laki' : 'Perempuan') . '"'
            . ' data-phone="' . h($t['phone']) . '"'
            . ' data-email="' . h($t['email']) . '"'
            . ' data-position="' . h($t['position']) . '"'
            . ' data-subject="' . h($t['subject']) . '"'
            . ' data-join="' . h($t['join_date']) . '">';
        echo '<div class="top"><div class="initial">' . h(mb_substr($t['name'], 0, 1)) . '</div><div>'
            . '<div class="name">' . h($t['name']) . '</div><div class="nis">' . h($t['position'] ?: '-') . '</div></div></div>';
        echo '<div class="tags"><span class="tag">' . h($t['subject'] ?: '-') . '</span></div>';
        echo '</div>';
    }
}

if (isset($_GET['ajax'])) {
    if ($_GET['ajax'] === 'students' && tabAllowed('direktori', $TABS, $ROLES)) {
        $rows = studentQuery($mysqli, trim($_GET['q'] ?? ''), $_GET['class_id'] ?? '', $_GET['room_id'] ?? '');
        renderStudentGrid($rows, hasRole($DATA_MGMT_ROLES, $ROLES));
        exit;
    }
    if ($_GET['ajax'] === 'teachers' && tabAllowed('guru', $TABS, $ROLES)) {
        $rows = teacherQuery($mysqli, trim($_GET['q'] ?? ''));
        renderTeacherGrid($rows, hasRole($DATA_MGMT_ROLES, $ROLES));
        exit;
    }
}

// ------------------------------------------------------------------
// CETAK SURAT IZIN (A6) — halaman berdiri sendiri, bukan bagian layout utama
// ------------------------------------------------------------------
if (isset($_GET['print_leave']) && tabAllowed('perizinan', $TABS, $ROLES)) {
    $leaveId = (int) $_GET['print_leave'];
    $stmt = safePrepare($mysqli, "SELECT l.*, s.name student_name, s.nis, c.name class_name, r.name room_name
                                   FROM leave_permits l
                                   JOIN students s ON s.id = l.student_id
                                   JOIN classes c ON c.id = s.class_id
                                   JOIN rooms r ON r.id = s.room_id
                                   WHERE l.id = ?");
    $stmt->bind_param('i', $leaveId);
    $stmt->execute();
    $leave = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$leave) { die('Data izin tidak ditemukan.'); }
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
    <meta charset="UTF-8">
    <title>Surat Izin — <?= h($leave['student_name']) ?></title>
    <style>
        @page { size: 105mm 148mm; margin: 6mm; }
        body{ font-family: 'Times New Roman', serif; font-size: 10.5px; color:#111; margin:0; }
        .head{ text-align:center; border-bottom:2px solid #111; padding-bottom:6px; margin-bottom:10px; }
        .head h1{ font-size:13px; margin:0; }
        .head p{ margin:1px 0; font-size:9px; }
        .title{ text-align:center; text-decoration:underline; font-weight:bold; margin:10px 0; font-size:11px; }
        table.info{ width:100%; border-collapse:collapse; font-size:10px; margin-bottom:10px; }
        table.info td{ padding:2px 0; vertical-align:top; }
        table.info td.k{ width:32%; }
        .reason{ font-size:10px; margin-bottom:16px; }
        .sign{ display:flex; justify-content:space-between; font-size:10px; margin-top:24px; }
        .sign div{ text-align:center; width:45%; }
        .sign .line{ margin-top:34px; border-top:1px solid #111; }
        .no-print{ text-align:center; margin-top:14px; }
        @media print{ .no-print{ display:none; } }
    </style>
    </head>
    <body onload="window.print()">
        <div class="head">
            <h1>PONDOK PESANTREN DAARUL 'ULUUM LIDO</h1>
            <p>Surat Izin Santri — HISADA</p>
        </div>
        <div class="title"><?= $leave['type'] === 'pulang' ? 'SURAT IZIN PULANG' : 'SURAT IZIN KELUAR' ?></div>
        <table class="info">
            <tr><td class="k">Nama Santri</td><td>: <?= h($leave['student_name']) ?></td></tr>
            <tr><td class="k">NIS</td><td>: <?= h($leave['nis']) ?></td></tr>
            <tr><td class="k">Kelas / Kamar</td><td>: <?= h($leave['class_name']) ?> / <?= h($leave['room_name']) ?></td></tr>
            <tr><td class="k">Waktu Mulai</td><td>: <?= date('d M Y H:i', strtotime($leave['start_date'])) ?></td></tr>
            <tr><td class="k">Rencana Kembali</td><td>: <?= date('d M Y H:i', strtotime($leave['end_date'])) ?></td></tr>
        </table>
        <div class="reason"><strong>Alasan:</strong><br><?= h($leave['reason']) ?></div>
        <div class="sign">
            <div>Wali Santri<div class="line">&nbsp;</div></div>
            <div>Pengurus<div class="line">&nbsp;</div></div>
        </div>
        <div class="no-print"><button onclick="window.print()">Cetak Ulang</button></div>
    </body>
    </html>
    <?php
    exit;
}

// ------------------------------------------------------------------
// PROSES AKSI (POST) — dieksekusi sebelum output apa pun (PRG pattern)
// ------------------------------------------------------------------
$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $backTab = $_POST['tab'] ?? 'beranda';
    $backExtra = '';

    try {
        switch ($action) {

            // ---- ABSENSI: simpan absensi massal per sesi+kamar/kelas ----
            case 'add_attendance_bulk':
                if (!hasRole(['admin','pengurus','wali_kelas','wali_kamar'], $ROLES)) throw new Exception('Tidak berhak.');
                $date = $_POST['date'] ?? date('Y-m-d');
                $session = $_POST['session_type'] ?? 'kamar_malam';
                $statuses = $_POST['status'] ?? [];
                $stmt = safePrepare($mysqli,
                    'INSERT INTO attendances (student_id, date, session_type, status, created_by)
                     VALUES (?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE status = VALUES(status), created_by = VALUES(created_by), updated_at = NOW()'
                );
                foreach ($statuses as $studentId => $status) {
                    $studentId = (int) $studentId;
                    $stmt->bind_param('isssi', $studentId, $date, $session, $status, $UID);
                    $stmt->execute();
                }
                $stmt->close();
                $flash = 'Absensi (' . h($session) . ') tanggal ' . h($date) . ' berhasil disimpan.';
                break;

            // ---- POSKESTREN ----
            case 'add_medical':
                if (!hasRole(['admin','asisten'], $ROLES)) throw new Exception('Tidak berhak.');
                $studentId = (int) $_POST['student_id'];
                $complaint = trim($_POST['complaint'] ?? '');
                if ($studentId <= 0 || $complaint === '') throw new Exception('Santri dan keluhan wajib diisi.');
                $stmt = safePrepare($mysqli, 'INSERT INTO medical_records (student_id, complaint, status, handled_by_assistant) VALUES (?, ?, "rawat_jalan", ?)');
                $stmt->bind_param('isi', $studentId, $complaint, $UID);
                $stmt->execute();
                $stmt->close();
                $flash = 'Kasus baru diteruskan ke Dokter.';
                break;

            case 'update_medical_doctor':
                if (!hasRole(['admin','dokter'], $ROLES)) throw new Exception('Tidak berhak.');
                $recordId = (int) $_POST['record_id'];
                $diagnosis = trim($_POST['diagnosis'] ?? '');
                $prescription = trim($_POST['prescription'] ?? '');
                $status = $_POST['status'] ?? 'rawat_jalan';
                if ($diagnosis === '' || $prescription === '') throw new Exception('Diagnosa dan resep wajib diisi.');
                $stmt = safePrepare($mysqli, 'UPDATE medical_records SET diagnosis=?, prescription=?, status=?, handled_by_doctor=? WHERE id=?');
                $stmt->bind_param('sssii', $diagnosis, $prescription, $status, $UID, $recordId);
                $stmt->execute();
                $stmt->close();
                if ($status === 'rawat_inap') {
                    $studentStmt = safePrepare($mysqli, 'SELECT student_id FROM medical_records WHERE id=?');
                    $studentStmt->bind_param('i', $recordId);
                    $studentStmt->execute();
                    $sid = $studentStmt->get_result()->fetch_assoc()['student_id'] ?? null;
                    $studentStmt->close();
                    if ($sid) {
                        $today = date('Y-m-d');
                        $attStmt = safePrepare($mysqli,
                            'INSERT INTO attendances (student_id, date, session_type, status, created_by) VALUES (?, ?, "kamar_pagi", "sakit", ?)
                             ON DUPLICATE KEY UPDATE status="sakit", updated_at=NOW()'
                        );
                        $attStmt->bind_param('isi', $sid, $today, $UID);
                        $attStmt->execute();
                        $attStmt->close();
                    }
                }
                $flash = 'Rekam medis diperbarui.';
                break;

            case 'checkout_medical':
                if (!hasRole(['admin','dokter','asisten'], $ROLES)) throw new Exception('Tidak berhak.');
                $recordId = (int) $_POST['record_id'];
                $stmt = safePrepare($mysqli, 'UPDATE medical_records SET status="sembuh" WHERE id=?');
                $stmt->bind_param('i', $recordId);
                $stmt->execute();
                $stmt->close();
                $flash = 'Pasien ditandai sembuh (check-out).';
                break;

            // ---- MAHKAMAH ----
            case 'add_violation':
                if (!hasRole(['admin','sekretaris','pengurus'], $ROLES)) throw new Exception('Tidak berhak.');
                $studentId = (int) ($_POST['student_id'] ?? 0);
                $categoryId = (int) $_POST['category_id'];
                $description = trim($_POST['description'] ?? '');
                if ($studentId <= 0 || $description === '') throw new Exception('Santri dan keterangan wajib diisi.');
                $stmt = safePrepare($mysqli, 'INSERT INTO violations (student_id, category_id, description, recorded_by) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('iisi', $studentId, $categoryId, $description, $UID);
                $stmt->execute();
                $stmt->close();
                $flash = 'Laporan pelanggaran dicatat, menunggu vonis Hakim.';
                break;

            case 'judge_violation':
                if (!hasRole(['admin','hakim'], $ROLES)) throw new Exception('Tidak berhak.');
                $violationId = (int) $_POST['violation_id'];
                $severity = $_POST['severity'] ?? 'ringan';
                $checked = $_POST['punishment_ids'] ?? [];
                $customText = trim($_POST['punishment_custom'] ?? '');
                $labels = [];
                if (!empty($checked)) {
                    $ids = array_map('intval', $checked);
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $catStmt = safePrepare($mysqli, "SELECT label FROM punishments WHERE id IN ($placeholders)");
                    $catStmt->bind_param(str_repeat('i', count($ids)), ...$ids);
                    $catStmt->execute();
                    foreach ($catStmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) { $labels[] = $row['label']; }
                    $catStmt->close();
                }
                if ($customText !== '') { $labels[] = $customText; }
                $punishmentGiven = implode('; ', $labels);
                if ($punishmentGiven === '') throw new Exception('Pilih minimal satu hukuman atau isi hukuman manual.');
                $stmt = safePrepare($mysqli, 'UPDATE violations SET severity=?, punishment_given=?, verdict="divonis", judged_by=?, judged_at=NOW() WHERE id=?');
                $stmt->bind_param('ssii', $severity, $punishmentGiven, $UID, $violationId);
                $stmt->execute();
                $stmt->close();
                $flash = 'Vonis & hukuman tersimpan.';
                break;

            case 'pemutihan_violation':
                if (!hasRole(['admin','hakim'], $ROLES)) throw new Exception('Tidak berhak.');
                $violationId = (int) $_POST['violation_id'];
                $reason = trim($_POST['revocation_reason'] ?? '');
                if ($reason === '') throw new Exception('Alasan pemutihan wajib diisi.');
                $stmt = safePrepare($mysqli, 'UPDATE violations SET verdict="pemutihan", revocation_reason=?, deleted_at=NOW() WHERE id=?');
                $stmt->bind_param('si', $reason, $violationId);
                $stmt->execute();
                $stmt->close();
                $flash = 'Pelanggaran diputihkan (riwayat tetap tersimpan).';
                break;

            // ---- PERIZINAN ----
            case 'add_leave':
                if (!hasRole(['admin','pengurus'], $ROLES)) throw new Exception('Tidak berhak.');
                $studentId = (int) ($_POST['student_id'] ?? 0);
                $type = $_POST['type'];
                $start = $_POST['start_date'];
                $end = $_POST['end_date'];
                $reason = trim($_POST['reason'] ?? '');
                if ($studentId <= 0 || $reason === '') throw new Exception('Santri dan alasan wajib diisi.');
                $stmt = safePrepare($mysqli, 'INSERT INTO leave_permits (student_id, type, start_date, end_date, reason, status, approved_by) VALUES (?, ?, ?, ?, ?, "active", ?)');
                $stmt->bind_param('issssi', $studentId, $type, $start, $end, $reason, $UID);
                $stmt->execute();
                $newLeaveId = $stmt->insert_id;
                $stmt->close();
                $today = date('Y-m-d');
                $attStmt = safePrepare($mysqli,
                    'INSERT INTO attendances (student_id, date, session_type, status, created_by) VALUES (?, ?, "kamar_malam", "izin", ?)
                     ON DUPLICATE KEY UPDATE status="izin", updated_at=NOW()'
                );
                $attStmt->bind_param('isi', $studentId, $today, $UID);
                $attStmt->execute();
                $attStmt->close();
                $flash = 'Izin diterbitkan & absensi hari ini otomatis mengikuti. <a href="dashboard.php?print_leave=' . $newLeaveId . '" target="_blank" style="text-decoration:underline;">Cetak surat (A6)</a>';
                break;

            case 'confirm_return':
                if (!hasRole(['admin','pengurus'], $ROLES)) throw new Exception('Tidak berhak.');
                $leaveId = (int) $_POST['leave_id'];
                $stmt = safePrepare($mysqli, 'UPDATE leave_permits SET actual_return_date = NOW(), status = "completed" WHERE id = ?');
                $stmt->bind_param('i', $leaveId);
                $stmt->execute();
                $stmt->close();
                $flash = 'Kembalinya santri dikonfirmasi.';
                break;

            // ---- PRESTASI ----
            case 'add_achievement':
                if (!hasRole(['admin','pengurus'], $ROLES)) throw new Exception('Tidak berhak.');
                $studentId = (int) ($_POST['student_id'] ?? 0);
                $event = trim($_POST['event_name'] ?? '');
                $rank = trim($_POST['rank_achieved'] ?? '');
                $date = $_POST['event_date'] ?? date('Y-m-d');
                $location = trim($_POST['location'] ?? '');
                $scope = $_POST['scope'] ?? 'internal';
                $desc = trim($_POST['description'] ?? '');
                if ($studentId <= 0 || $event === '' || $rank === '') throw new Exception('Santri, nama kegiatan, dan capaian wajib diisi.');
                $stmt = safePrepare($mysqli, 'INSERT INTO achievements (student_id, event_name, rank_achieved, event_date, location, scope, description, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('issssssi', $studentId, $event, $rank, $date, $location, $scope, $desc, $UID);
                $stmt->execute();
                $stmt->close();
                $flash = 'Prestasi berhasil dicatat.';
                break;

            // ---- KORESPONDENSI ----
            case 'add_correspondence':
                if (!hasRole(['admin','pengurus','sekretaris'], $ROLES)) throw new Exception('Tidak berhak.');
                $direction = $_POST['direction'];
                $fromPosition = trim($_POST['from_position'] ?? '');
                $letterType = trim($_POST['letter_type'] ?? '');
                $destination = trim($_POST['destination'] ?? '');
                $attachment = trim($_POST['attachment_url'] ?? '');
                $disposisi = trim($_POST['disposisi'] ?? '');
                if ($attachment !== '' && !preg_match('#^https://docs\.google\.com/#', $attachment)) {
                    throw new Exception('Lampiran wajib berupa tautan Google Docs (https://docs.google.com/...).');
                }
                if ($direction === 'keluar') {
                    $countStmt = $mysqli->query("SELECT COUNT(*) c FROM correspondence WHERE direction='keluar' AND YEAR(created_at)=YEAR(CURDATE())");
                    $seq = (int) $countStmt->fetch_assoc()['c'] + 1;
                    $romawi = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][date('n') - 1];
                    $code = sprintf('%03d/HISADA/%s/%s', $seq, $romawi, date('Y'));
                } else {
                    $code = trim($_POST['code'] ?? ('MASUK-' . time()));
                }
                $stmt = safePrepare($mysqli, 'INSERT INTO correspondence (direction, code, from_position, letter_type, destination, status, attachment_url, disposisi, created_by) VALUES (?, ?, ?, ?, ?, "diarsipkan", ?, ?, ?)');
                $stmt->bind_param('sssssssi', $direction, $code, $fromPosition, $letterType, $destination, $attachment, $disposisi, $UID);
                $stmt->execute();
                $stmt->close();
                $flash = 'Surat tercatat dengan nomor ' . h($code) . '.';
                break;

            // ---- KALENDER ----
            case 'add_calendar':
                if (!hasRole($EDITOR_ROLES, $ROLES)) throw new Exception('Tidak berhak.');
                $title = trim($_POST['title'] ?? '');
                $desc = trim($_POST['description'] ?? '');
                $start = $_POST['start_date'];
                $end = $_POST['end_date'] ?: null;
                $category = $_POST['category'] ?? 'umum';
                $visibility = $_POST['visibility'] ?? 'public';
                if ($title === '' || $start === '') throw new Exception('Judul dan tanggal mulai wajib diisi.');
                $stmt = safePrepare($mysqli, 'INSERT INTO calendar_events (title, description, start_date, end_date, category, visibility, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('ssssssi', $title, $desc, $start, $end, $category, $visibility, $UID);
                $stmt->execute();
                $stmt->close();
                $flash = 'Agenda ditambahkan ke kalender.';
                break;

            // ---- KELOLA PENGGUNA ----
            case 'add_user':
                if (!hasRole(['admin'], $ROLES)) throw new Exception('Tidak berhak.');
                $name = trim($_POST['name'] ?? '');
                $email = trim($_POST['email'] ?? '') ?: null;
                $phone = trim($_POST['phone'] ?? '') ?: null;
                $password = $_POST['password'] ?? '';
                $roleId = (int) $_POST['role_id'];
                if ($name === '' || ($email === null && $phone === null) || $password === '') {
                    throw new Exception('Nama, (email atau no. WA), dan kata sandi wajib diisi.');
                }
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = safePrepare($mysqli, 'INSERT INTO users (email, phone, password, name) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('ssss', $email, $phone, $hash, $name);
                $stmt->execute();
                $newUserId = $stmt->insert_id;
                $stmt->close();
                $stmt = safePrepare($mysqli, 'INSERT INTO role_assignments (user_id, role_id, mulai_berlaku) VALUES (?, ?, CURDATE())');
                $stmt->bind_param('ii', $newUserId, $roleId);
                $stmt->execute();
                $stmt->close();
                $flash = 'Pengguna baru ditambahkan.';
                break;

            case 'edit_user':
                if (!hasRole(['admin'], $ROLES)) throw new Exception('Tidak berhak.');
                $editId = (int) $_POST['user_id'];
                $name = trim($_POST['name'] ?? '');
                $email = trim($_POST['email'] ?? '') ?: null;
                $phone = trim($_POST['phone'] ?? '') ?: null;
                $newPassword = $_POST['password'] ?? '';
                $roleIds = array_map('intval', $_POST['role_ids'] ?? []);
                if ($name === '') throw new Exception('Nama wajib diisi.');

                if ($newPassword !== '') {
                    $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                    $stmt = safePrepare($mysqli, 'UPDATE users SET name=?, email=?, phone=?, password=? WHERE id=?');
                    $stmt->bind_param('ssssi', $name, $email, $phone, $hash, $editId);
                } else {
                    $stmt = safePrepare($mysqli, 'UPDATE users SET name=?, email=?, phone=? WHERE id=?');
                    $stmt->bind_param('sssi', $name, $email, $phone, $editId);
                }
                $stmt->execute();
                $stmt->close();

                // Tutup semua role lama yang tidak lagi dicentang (selesai_berlaku = hari ini),
                // lalu buka role baru yang belum aktif. Ini menjaga histori jabatan (time-bound).
                $existingStmt = safePrepare($mysqli, 'SELECT role_id FROM role_assignments WHERE user_id=? AND (selesai_berlaku IS NULL OR selesai_berlaku >= CURDATE())');
                $existingStmt->bind_param('i', $editId);
                $existingStmt->execute();
                $existingRoleIds = array_column($existingStmt->get_result()->fetch_all(MYSQLI_ASSOC), 'role_id');
                $existingStmt->close();

                $toClose = array_diff($existingRoleIds, $roleIds);
                $toOpen = array_diff($roleIds, $existingRoleIds);

                foreach ($toClose as $rid) {
                    $closeStmt = safePrepare($mysqli, 'UPDATE role_assignments SET selesai_berlaku = CURDATE() WHERE user_id=? AND role_id=? AND (selesai_berlaku IS NULL OR selesai_berlaku >= CURDATE())');
                    $closeStmt->bind_param('ii', $editId, $rid);
                    $closeStmt->execute();
                    $closeStmt->close();
                }
                foreach ($toOpen as $rid) {
                    $openStmt = safePrepare($mysqli, 'INSERT INTO role_assignments (user_id, role_id, mulai_berlaku) VALUES (?, ?, CURDATE())');
                    $openStmt->bind_param('ii', $editId, $rid);
                    $openStmt->execute();
                    $openStmt->close();
                }
                $flash = 'Data pengguna & role diperbarui.';
                break;

            // ---- DATA MANAJEMEN: SANTRI (admin & sekretaris) ----
            case 'add_student':
                if (!hasRole($DATA_MGMT_ROLES, $ROLES)) throw new Exception('Tidak berhak.');
                $nis = trim($_POST['nis'] ?? '');
                $nisn = trim($_POST['nisn'] ?? '') ?: null;
                $name = trim($_POST['name'] ?? '');
                $gender = $_POST['gender'] ?? 'L';
                $birthDate = $_POST['birth_date'] ?? null;
                $origin = trim($_POST['origin'] ?? '');
                $classId = (int) $_POST['class_id'];
                $roomId = (int) $_POST['room_id'];
                $familyPhone = trim($_POST['family_phone'] ?? '');
                $familyName = trim($_POST['family_name'] ?? '');
                if ($nis === '' || $name === '' || !$birthDate || $classId <= 0 || $roomId <= 0 || $familyPhone === '' || $familyName === '') {
                    throw new Exception('NIS, nama, tanggal lahir, kelas, kamar, dan data wali wajib diisi.');
                }
                // cari atau buat keluarga berdasarkan no. HP ayah/wali
                $famStmt = safePrepare($mysqli, 'SELECT id FROM families WHERE father_phone=?');
                $famStmt->bind_param('s', $familyPhone);
                $famStmt->execute();
                $fam = $famStmt->get_result()->fetch_assoc();
                $famStmt->close();
                if ($fam) {
                    $familyId = $fam['id'];
                } else {
                    $insFam = safePrepare($mysqli, 'INSERT INTO families (father_name, father_phone) VALUES (?, ?)');
                    $insFam->bind_param('ss', $familyName, $familyPhone);
                    $insFam->execute();
                    $familyId = $insFam->insert_id;
                    $insFam->close();
                }
                $stmt = safePrepare($mysqli, 'INSERT INTO students (nis, nisn, name, gender, birth_date, origin, class_id, room_id, family_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('ssssssiii', $nis, $nisn, $name, $gender, $birthDate, $origin, $classId, $roomId, $familyId);
                $stmt->execute();
                $newStudentId = $stmt->insert_id;
                $stmt->close();
                $tahunAjaran = (date('n') >= 7 ? date('Y') : date('Y') - 1) . '/' . (date('n') >= 7 ? date('Y') + 1 : date('Y'));
                $rk = safePrepare($mysqli, 'INSERT INTO riwayat_kelas (student_id, class_id, tahun_ajaran, semester) VALUES (?, ?, ?, 1)');
                $rk->bind_param('iis', $newStudentId, $classId, $tahunAjaran); $rk->execute(); $rk->close();
                $rr = safePrepare($mysqli, 'INSERT INTO riwayat_kamar (student_id, room_id, tahun_ajaran, semester) VALUES (?, ?, ?, 1)');
                $rr->bind_param('iis', $newStudentId, $roomId, $tahunAjaran); $rr->execute(); $rr->close();
                $flash = 'Santri baru berhasil ditambahkan.';
                break;

            case 'edit_student':
                if (!hasRole($DATA_MGMT_ROLES, $ROLES)) throw new Exception('Tidak berhak.');
                $sid = (int) $_POST['student_id'];
                $nis = trim($_POST['nis'] ?? '');
                $nisn = trim($_POST['nisn'] ?? '') ?: null;
                $name = trim($_POST['name'] ?? '');
                $gender = $_POST['gender'] ?? 'L';
                $birthDate = $_POST['birth_date'] ?? null;
                $origin = trim($_POST['origin'] ?? '');
                $classId = (int) $_POST['class_id'];
                $roomId = (int) $_POST['room_id'];
                $status = $_POST['status'] ?? 'aktif';
                if ($nis === '' || $name === '' || !$birthDate || $classId <= 0 || $roomId <= 0) {
                    throw new Exception('NIS, nama, tanggal lahir, kelas, dan kamar wajib diisi.');
                }
                // Cek perpindahan kelas/kamar -> catat riwayat baru supaya histori lama tidak hilang
                $oldStmt = safePrepare($mysqli, 'SELECT class_id, room_id FROM students WHERE id=?');
                $oldStmt->bind_param('i', $sid); $oldStmt->execute();
                $old = $oldStmt->get_result()->fetch_assoc(); $oldStmt->close();
                $tahunAjaran = (date('n') >= 7 ? date('Y') : date('Y') - 1) . '/' . (date('n') >= 7 ? date('Y') + 1 : date('Y'));
                if ($old && (int)$old['class_id'] !== $classId) {
                    $rk = safePrepare($mysqli, 'INSERT INTO riwayat_kelas (student_id, class_id, tahun_ajaran, semester) VALUES (?, ?, ?, 1)');
                    $rk->bind_param('iis', $sid, $classId, $tahunAjaran); $rk->execute(); $rk->close();
                }
                if ($old && (int)$old['room_id'] !== $roomId) {
                    $rr = safePrepare($mysqli, 'INSERT INTO riwayat_kamar (student_id, room_id, tahun_ajaran, semester) VALUES (?, ?, ?, 1)');
                    $rr->bind_param('iis', $sid, $roomId, $tahunAjaran); $rr->execute(); $rr->close();
                }
                $stmt = safePrepare($mysqli, 'UPDATE students SET nis=?, nisn=?, name=?, gender=?, birth_date=?, origin=?, class_id=?, room_id=?, status=? WHERE id=?');
                $stmt->bind_param('ssssssiisi', $nis, $nisn, $name, $gender, $birthDate, $origin, $classId, $roomId, $status, $sid);
                $stmt->execute();
                $stmt->close();
                $flash = 'Data santri diperbarui.';
                break;

            case 'delete_student':
                if (!hasRole($DATA_MGMT_ROLES, $ROLES)) throw new Exception('Tidak berhak.');
                $sid = (int) $_POST['student_id'];
                $stmt = safePrepare($mysqli, 'DELETE FROM students WHERE id=?');
                $stmt->bind_param('i', $sid);
                $stmt->execute();
                $stmt->close();
                $flash = 'Data santri & seluruh riwayat terkait telah dihapus permanen.';
                break;

            // ---- DATA MANAJEMEN: GURU (admin & sekretaris) ----
            case 'add_teacher':
                if (!hasRole($DATA_MGMT_ROLES, $ROLES)) throw new Exception('Tidak berhak.');
                $code = trim($_POST['code'] ?? '') ?: null;
                $name = trim($_POST['name'] ?? '');
                $gender = $_POST['gender'] ?? 'L';
                $phone = trim($_POST['phone'] ?? '') ?: null;
                $email = trim($_POST['email'] ?? '') ?: null;
                $position = trim($_POST['position'] ?? '');
                $subject = trim($_POST['subject'] ?? '');
                $joinDate = $_POST['join_date'] ?: null;
                if ($name === '') throw new Exception('Nama guru wajib diisi.');
                $stmt = safePrepare($mysqli, 'INSERT INTO teachers (code, name, gender, phone, email, position, subject, join_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('ssssssss', $code, $name, $gender, $phone, $email, $position, $subject, $joinDate);
                $stmt->execute();
                $stmt->close();
                $flash = 'Data guru baru ditambahkan.';
                break;

            case 'edit_teacher':
                if (!hasRole($DATA_MGMT_ROLES, $ROLES)) throw new Exception('Tidak berhak.');
                $tid = (int) $_POST['teacher_id'];
                $code = trim($_POST['code'] ?? '') ?: null;
                $name = trim($_POST['name'] ?? '');
                $gender = $_POST['gender'] ?? 'L';
                $phone = trim($_POST['phone'] ?? '') ?: null;
                $email = trim($_POST['email'] ?? '') ?: null;
                $position = trim($_POST['position'] ?? '');
                $subject = trim($_POST['subject'] ?? '');
                $status = $_POST['status'] ?? 'aktif';
                if ($name === '') throw new Exception('Nama guru wajib diisi.');
                $stmt = safePrepare($mysqli, 'UPDATE teachers SET code=?, name=?, gender=?, phone=?, email=?, position=?, subject=?, status=? WHERE id=?');
                $stmt->bind_param('ssssssssi', $code, $name, $gender, $phone, $email, $position, $subject, $status, $tid);
                $stmt->execute();
                $stmt->close();
                $flash = 'Data guru diperbarui.';
                break;

            case 'delete_teacher':
                if (!hasRole($DATA_MGMT_ROLES, $ROLES)) throw new Exception('Tidak berhak.');
                $tid = (int) $_POST['teacher_id'];
                $stmt = safePrepare($mysqli, 'DELETE FROM teachers WHERE id=?');
                $stmt->bind_param('i', $tid);
                $stmt->execute();
                $stmt->close();
                $flash = 'Data guru dihapus. Kelas/kamar yang diampu otomatis dikosongkan wali-nya.';
                break;

            case 'logout':
                session_destroy();
                header('Location: index.php');
                exit;

            default:
                throw new Exception('Aksi tidak dikenal.');
        }
    } catch (Exception $e) {
        $flash = 'GAGAL: ' . $e->getMessage();
    }

    header('Location: dashboard.php?tab=' . urlencode($backTab) . $backExtra . '&msg=' . urlencode($flash ?? ''));
    exit;
}

if (isset($_GET['msg']) && $_GET['msg'] !== '') {
    $flash = $_GET['msg'];
}

// ------------------------------------------------------------------
// TENTUKAN TAB AKTIF
// ------------------------------------------------------------------
$activeTab = $_GET['tab'] ?? ($IS_WALI_ONLY ? 'anak_saya' : 'beranda');
if (!tabAllowed($activeTab, $TABS, $ROLES)) {
    $activeTab = $IS_WALI_ONLY ? 'anak_saya' : 'beranda';
}

// ------------------------------------------------------------------
// DATA UMUM
// ------------------------------------------------------------------
$allClasses = $mysqli->query('SELECT id, name FROM classes ORDER BY name')->fetch_all(MYSQLI_ASSOC);
$allRooms   = $mysqli->query('SELECT id, name, building, gender FROM rooms ORDER BY name')->fetch_all(MYSQLI_ASSOC);
$allRolesList = $mysqli->query('SELECT id, name FROM roles ORDER BY name')->fetch_all(MYSQLI_ASSOC);
$activeStudentsForPicker = $mysqli->query("SELECT id, name, nis FROM students WHERE status='aktif' ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$studentPickerJson = json_encode($activeStudentsForPicker, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($TABS[$activeTab]['label']) ?> — HISADA</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root{
        --ink:#16241c; --emerald-900:#0b3d2e; --emerald-800:#0f4a37; --emerald-700:#166247;
        --gold:#c9a13b; --gold-soft:#e6cf8f; --cream:#f7f4ec; --paper:#ffffff;
        --line:#e3ddc9; --muted:#6b7a70; --red:#b3432b; --amber:#a9791f;
    }
    *{box-sizing:border-box;}
    body{ margin:0; font-family:'Inter',sans-serif; color:var(--ink); background:var(--cream); }
    a{color:inherit;text-decoration:none;}
    .shell{display:flex; min-height:100vh;}

    /* ---------------- SIDEBAR ---------------- */
    .sidebar{
        width:248px; flex-shrink:0; background:var(--emerald-900); color:#eef1ea;
        display:flex; flex-direction:column; padding:26px 18px;
        position:sticky; top:0; height:100vh; overflow-y:auto;
    }
    .side-brand{ display:flex; align-items:center; gap:10px; padding:0 6px 22px; border-bottom:1px solid rgba(238,241,234,.12); margin-bottom:16px; }
    .side-brand .seal{ width:36px;height:36px;border-radius:10px; background:rgba(238,241,234,.1); border:1px solid rgba(238,241,234,.3); display:flex;align-items:center;justify-content:center; font-family:'Fraunces',serif;font-weight:600;font-size:15px;color:var(--gold-soft); }
    .side-brand strong{font-family:'Fraunces',serif;font-size:15px;font-weight:600;display:block;}
    .side-brand span{font-size:10.5px;color:#b9c7bd;}
    .close-sidebar-btn{ display:none; margin-left:auto; background:none; border:none; color:#eef1ea; font-size:20px; cursor:pointer; }

    .nav-group{margin-bottom:4px;}
    .nav-item{ display:flex;align-items:center;gap:11px; padding:9px 10px; border-radius:9px; font-size:13.5px; color:#d7e0da; margin-bottom:2px; }
    .nav-item .mark{ width:24px;height:24px;border-radius:7px; background:rgba(238,241,234,.08); display:flex;align-items:center;justify-content:center; font-size:11px;font-weight:700;color:var(--gold-soft); flex-shrink:0; }
    .nav-item:hover{background:rgba(238,241,234,.07);}
    .nav-item.active{background:var(--gold-soft); color:var(--emerald-900); font-weight:600;}
    .nav-item.active .mark{background:var(--emerald-900); color:var(--gold-soft);}

    .side-foot{ margin-top:auto; padding-top:16px; border-top:1px solid rgba(238,241,234,.12); }
    .who{display:flex; align-items:center; gap:9px; padding:6px; margin-bottom:8px;}
    .who .av{ width:32px;height:32px;border-radius:50%; background:var(--gold-soft); color:var(--emerald-900); display:flex;align-items:center;justify-content:center; font-weight:700; font-size:13px; flex-shrink:0; }
    .who .meta strong{display:block;font-size:12.5px;}
    .who .meta span{font-size:10.5px;color:#b9c7bd; text-transform:capitalize;}
    .btn-logout{ display:block; width:100%; text-align:center; padding:8px; border-radius:8px; border:1px solid rgba(238,241,234,.25); background:none; color:#eef1ea; font-size:12.5px; cursor:pointer; }
    .btn-logout:hover{background:rgba(238,241,234,.08);}

    /* Backdrop buram saat sidebar mobile terbuka */
    .sidebar-backdrop{ display:none; position:fixed; inset:0; background:rgba(11,18,15,.55); backdrop-filter:blur(2px); z-index:39; }
    .sidebar-backdrop.show{ display:block; }

    /* ---------------- MAIN ---------------- */
    .main{flex:1; min-width:0; display:flex; flex-direction:column;}
    .topbar{ padding:20px 32px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid var(--line); background:var(--paper); gap:14px; }
    .topbar h1{font-family:'Fraunces',serif; font-size:21px; font-weight:600; margin:0;}
    .topbar p{margin:2px 0 0; font-size:12.5px; color:var(--muted);}
    .hamburger{ display:none; background:none; border:1px solid var(--line); border-radius:8px; width:38px;height:38px; font-size:16px; cursor:pointer; flex-shrink:0; }

    .content{padding:28px 32px 60px; max-width:1240px;}

    .flash{ padding:12px 16px; border-radius:9px; margin-bottom:22px; font-size:13px; background:#e8f3ec; border:1px solid #bfdccb; color:var(--emerald-700); }
    .flash.gagal{background:#fbeae5; border-color:#e6b7a8; color:var(--red);}
    .flash a{color:inherit; font-weight:600;}

    .stat-grid{display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px; margin-bottom:28px;}
    .stat-card{ background:var(--paper); border:1px solid var(--line); border-radius:14px; padding:18px; }
    .stat-card .num{font-family:'Fraunces',serif; font-size:30px; font-weight:600; color:var(--emerald-900);}
    .stat-card .lbl{font-size:12px; color:var(--muted); margin-top:2px;}

    .panel{ background:var(--paper); border:1px solid var(--line); border-radius:14px; padding:22px; margin-bottom:22px; }
    .panel h2{font-family:'Fraunces',serif; font-size:16.5px; font-weight:600; margin:0 0 16px;}
    .panel h3{font-size:13px; font-weight:600; margin:0 0 12px; color:var(--muted); text-transform:uppercase; letter-spacing:.03em;}

    .two-col{display:grid; grid-template-columns:1.4fr 1fr; gap:20px;}
    @media (max-width:980px){.two-col{grid-template-columns:1fr;}}

    .bar-row{display:flex; align-items:center; gap:10px; margin-bottom:8px; font-size:12px;}
    .bar-row .day{width:70px; color:var(--muted); flex-shrink:0;}
    .bar-track{flex:1; background:#efe9d6; border-radius:5px; height:10px; overflow:hidden; display:flex;}
    .bar-fill{height:100%;}
    .bar-fill.hadir{background:var(--emerald-700);}
    .bar-fill.sakit{background:var(--amber);}
    .bar-fill.izin{background:#8fa791;}
    .bar-fill.alpha{background:var(--red);}
    .legend{display:flex; gap:14px; font-size:11px; color:var(--muted); margin-top:10px;}
    .legend span{display:flex; align-items:center; gap:5px;}
    .legend i{width:8px;height:8px;border-radius:2px;display:inline-block;}

    .agenda-item{padding:10px 0; border-bottom:1px solid var(--line); font-size:13px;}
    .agenda-item:last-child{border:none;}
    .agenda-item .date{font-size:11px; color:var(--gold); font-weight:600; text-transform:uppercase;}
    .agenda-item .badge-internal{font-size:10px; background:#efe9d6; color:var(--amber); padding:1px 6px; border-radius:5px; margin-left:6px;}

    .shortcut-link{display:block; padding:9px 10px; border:1px solid var(--line); border-radius:9px; font-size:12.5px; margin-bottom:8px;}
    .shortcut-link:hover{border-color:var(--emerald-700);}

    .toolbar{display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; align-items:center;}
    .toolbar input[type=text], .toolbar select, .field-input, select.field-input{ padding:9px 12px; border:1.4px solid var(--line); border-radius:9px; font-size:13px; font-family:inherit; background:#fff; }
    .toolbar input[type=text]{flex:1; min-width:180px;}

    .student-grid{display:grid; grid-template-columns:repeat(auto-fill,minmax(230px,1fr)); gap:14px; min-height:80px;}
    .student-card{ border:1px solid var(--line); border-radius:12px; padding:14px; background:var(--paper); cursor:pointer; transition:border-color .15s; }
    .student-card:hover{border-color:var(--emerald-700);}
    .student-card .top{display:flex; gap:10px; align-items:center;}
    .student-card .initial{ width:40px;height:40px;border-radius:10px; background:var(--emerald-900); color:var(--gold-soft); display:flex;align-items:center;justify-content:center; font-family:'Fraunces',serif; font-weight:600; flex-shrink:0; }
    .student-card .name{font-size:13.5px; font-weight:600;}
    .student-card .nis{font-size:11px; color:var(--muted);}
    .student-card .tags{display:flex; gap:6px; margin-top:10px; flex-wrap:wrap;}
    .tag{font-size:10.5px; background:#efe9d6; color:#5b6b60; padding:2px 8px; border-radius:6px;}
    .search-spinner{font-size:11px; color:var(--muted); display:none;}
    .search-spinner.show{display:inline;}

    /* Kartu pilihan sesi (Absensi) */
    .choice-grid{display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:14px; margin-bottom:22px;}
    .choice-card{ display:block; border:1.6px solid var(--line); border-radius:14px; padding:18px; background:var(--paper); text-align:center; transition:.15s; }
    .choice-card:hover{border-color:var(--emerald-700);}
    .choice-card.active{border-color:var(--emerald-900); background:var(--emerald-900); color:#f4efe1;}
    .choice-card .mark2{ width:38px;height:38px;border-radius:10px; background:var(--gold-soft); color:var(--emerald-900); display:flex;align-items:center;justify-content:center; font-weight:700; margin:0 auto 10px; }
    .choice-card .lbl2{font-size:13px; font-weight:600;}

    table.data{width:100%; border-collapse:collapse; font-size:13px;}
    table.data th{text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.03em; color:var(--muted); padding:8px 10px; border-bottom:1.4px solid var(--line);}
    table.data td{padding:9px 10px; border-bottom:1px solid var(--line); vertical-align:top;}
    table.data tr:last-child td{border-bottom:none;}
    .status-pill{font-size:10.5px; padding:2px 9px; border-radius:20px; font-weight:600; display:inline-block;}
    .status-pill.hadir, .status-pill.sembuh, .status-pill.completed, .status-pill.divonis, .status-pill.approved, .status-pill.active, .status-pill.aktif{background:#e2f0e6; color:var(--emerald-700);}
    .status-pill.sakit, .status-pill.rawat_inap, .status-pill.proses, .status-pill.pending, .status-pill.overdue{background:#fbeee0; color:var(--amber);}
    .status-pill.izin, .status-pill.rujuk{background:#eef1ea; color:#5b6b60;}
    .status-pill.alpha, .status-pill.pemutihan, .status-pill.keluar{background:#fbeae5; color:var(--red);}

    .field-group{margin-bottom:14px;}
    .field-group label{display:block; font-size:12px; font-weight:600; margin-bottom:5px; color:#3d4a41;}
    .form-grid{display:grid; grid-template-columns:1fr 1fr; gap:14px;}
    @media (max-width:700px){.form-grid{grid-template-columns:1fr;}}
    textarea.field-input{width:100%; min-height:70px; font-family:inherit; resize:vertical;}
    input.field-input{width:100%;}
    .checklist{display:flex; flex-direction:column; gap:8px; margin-bottom:12px;}
    .checklist label{display:flex; align-items:center; gap:8px; font-size:13px; font-weight:400;}

    .btn{ display:inline-block; padding:9px 18px; border-radius:9px; border:none; background:var(--emerald-900); color:#f4efe1; font-size:13px; font-weight:600; cursor:pointer; }
    .btn:hover{background:var(--emerald-700);}
    .btn.secondary{background:none; border:1px solid var(--line); color:var(--ink);}
    .btn.secondary:hover{border-color:var(--emerald-700);}
    .btn.small{padding:5px 12px; font-size:11.5px;}
    .btn.danger{background:var(--red);}

    details.form-toggle{margin-bottom:20px;}
    details.form-toggle summary{ list-style:none; cursor:pointer; display:inline-flex; align-items:center; gap:8px; padding:9px 16px; border:1.4px dashed var(--emerald-700); border-radius:9px; color:var(--emerald-700); font-size:13px; font-weight:600; }
    details.form-toggle summary::-webkit-details-marker{display:none;}
    details.form-toggle .panel{margin-top:14px;}

    .empty-state{padding:30px; text-align:center; color:var(--muted); font-size:13px;}

    /* ---- Modal generik ---- */
    .modal-backdrop{ display:none; position:fixed; inset:0; background:rgba(11,61,46,.55); align-items:center; justify-content:center; z-index:50; padding:20px; }
    .modal-backdrop.show{display:flex;}
    .id-card{ width:340px; background:var(--emerald-900); border-radius:18px; padding:26px; color:#f4efe1; position:relative; font-family:'Inter',sans-serif; max-height:90vh; overflow-y:auto; }
    .id-card .close-modal{position:absolute; top:14px; right:16px; background:none; border:none; color:#f4efe1; font-size:18px; cursor:pointer;}
    .id-card .id-initial{ width:64px;height:64px;border-radius:16px; background:var(--gold-soft); color:var(--emerald-900); display:flex;align-items:center;justify-content:center; font-family:'Fraunces',serif; font-size:26px; font-weight:600; margin-bottom:14px; }
    .id-card h3{font-family:'Fraunces',serif; margin:0 0 2px; font-size:19px;}
    .id-card .id-nis{font-size:12px; color:var(--gold-soft); margin-bottom:16px;}
    .id-card .id-row{display:flex; justify-content:space-between; font-size:12.5px; padding:7px 0; border-bottom:1px solid rgba(244,239,225,.15);}
    .id-card .id-row span:first-child{color:#c4d3ca;}
    .id-card .id-actions{display:flex; gap:8px; margin-top:16px;}

    .simple-modal{ width:420px; max-width:92vw; background:var(--paper); border-radius:16px; padding:24px; position:relative; }
    .simple-modal h3{font-family:'Fraunces',serif; margin:0 0 14px;}
    .simple-modal .close-modal{position:absolute; top:14px; right:16px; background:none; border:none; color:var(--muted); font-size:18px; cursor:pointer;}

    /* ---- Searchable select (typeahead) ---- */
    .searchable{position:relative;}
    .searchable-results{ position:absolute; top:calc(100% + 4px); left:0; right:0; background:#fff; border:1px solid var(--line); border-radius:10px; max-height:220px; overflow-y:auto; z-index:30; display:none; box-shadow:0 8px 24px rgba(11,61,46,.12); }
    .searchable-results.show{display:block;}
    .searchable-results .opt{padding:9px 12px; font-size:13px; cursor:pointer;}
    .searchable-results .opt:hover{background:var(--cream);}
    .searchable-results .opt small{display:block; color:var(--muted); font-size:11px;}

    /* ---- Kalender grid ---- */
    .cal-toolbar{display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap:wrap; gap:10px;}
    .cal-nav{display:flex; gap:8px; align-items:center;}
    .month-grids{display:grid; gap:20px;}
    .month-grids.n1{grid-template-columns:1fr;}
    .month-grids.n2{grid-template-columns:repeat(2,1fr);}
    .month-grids.n6, .month-grids.n12{grid-template-columns:repeat(3,1fr);}
    @media (max-width:900px){ .month-grids.n2, .month-grids.n6, .month-grids.n12{grid-template-columns:repeat(2,1fr);} }
    @media (max-width:600px){ .month-grids.n2, .month-grids.n6, .month-grids.n12{grid-template-columns:1fr;} }
    .mini-month{border:1px solid var(--line); border-radius:12px; padding:12px; background:var(--paper);}
    .mini-month .mm-title{font-family:'Fraunces',serif; font-weight:600; font-size:13.5px; margin-bottom:8px; text-align:center;}
    .mm-grid{display:grid; grid-template-columns:repeat(7,1fr); gap:3px; font-size:10.5px;}
    .mm-grid .mm-dow{text-align:center; color:var(--muted); font-weight:600; padding-bottom:3px;}
    .mm-grid .mm-day{ text-align:center; padding:5px 0; border-radius:6px; position:relative; color:var(--ink); }
    .mm-grid .mm-day.blank{visibility:hidden;}
    .mm-grid .mm-day.today{background:var(--gold-soft); font-weight:700;}
    .mm-grid .mm-day.has-event::after{ content:""; position:absolute; bottom:2px; left:50%; transform:translateX(-50%); width:4px;height:4px;border-radius:50%; background:var(--emerald-700); }
    .mm-grid .mm-day.has-internal::after{ background:var(--amber); }

    @media (max-width:880px){
        .sidebar{position:fixed; z-index:40; height:100vh; transform:translateX(-100%); transition:transform .25s ease; top:0; left:0;}
        .sidebar.open{transform:translateX(0);}
        .close-sidebar-btn{display:block;}
        .hamburger{display:block;}
        .content{padding:20px 16px;}
        .topbar{padding:16px 18px;}
        .form-grid{grid-template-columns:1fr;}
    }
</style>
</head>
<body>
<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeSidebar()"></div>
<div class="shell">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="side-brand">
            <div class="seal">DU</div>
            <div><strong>HISADA</strong><span>Daarul 'Uluum Lido</span></div>
            <button class="close-sidebar-btn" onclick="closeSidebar()">&times;</button>
        </div>

        <nav class="nav-group">
            <?php foreach ($TABS as $key => $tab):
                if (!tabAllowed($key, $TABS, $ROLES)) continue;
            ?>
                <a class="nav-item <?= $key === $activeTab ? 'active' : '' ?>" href="?tab=<?= $key ?>">
                    <span class="mark"><?= $tab['mark'] ?></span> <?= h($tab['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="side-foot">
            <div class="who">
                <div class="av"><?= h(mb_substr($NAME, 0, 1)) ?></div>
                <div class="meta"><strong><?= h($NAME) ?></strong><span><?= h(implode(', ', $ROLES)) ?></span></div>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="btn-logout">Keluar</button>
            </form>
        </div>
    </aside>

    <!-- MAIN -->
    <div class="main">
        <div class="topbar">
            <button class="hamburger" onclick="openSidebar()">&#9776;</button>
            <div>
                <h1><?= h($TABS[$activeTab]['label']) ?></h1>
                <p><?= date('l, d F Y') ?></p>
            </div>
        </div>

        <div class="content">
            <?php if ($flash): ?>
                <div class="flash <?= str_starts_with($flash, 'GAGAL') ? 'gagal' : '' ?>"><?= $flash ?></div>
            <?php endif; ?>

            <?php
            // ============================================================
            //  RENDER PER TAB
            // ============================================================
            switch ($activeTab):

                // ---------------------------------------------------------
                case 'beranda':
                // ---------------------------------------------------------
                if ($IS_WALI_ONLY):
                    $children = safePrepare($mysqli, 'SELECT s.*, c.name class_name, r.name room_name FROM students s JOIN classes c ON c.id=s.class_id JOIN rooms r ON r.id=s.room_id WHERE s.family_id=?');
                    $children->bind_param('i', $FAMILY_ID);
                    $children->execute();
                    $kids = $children->get_result()->fetch_all(MYSQLI_ASSOC);
                    $children->close();
                    ?>
                    <div class="panel">
                        <h2>Selamat datang, <?= h($NAME) ?></h2>
                        <p style="font-size:13px;color:var(--muted);margin:0;">Ringkasan singkat anak Anda.</p>
                    </div>
                    <div class="student-grid">
                        <?php foreach ($kids as $kid): ?>
                            <div class="panel">
                                <h3 style="margin-bottom:4px;"><?= h($kid['name']) ?></h3>
                                <p style="font-size:12px;color:var(--muted);margin:0;">NIS <?= h($kid['nis']) ?> · <?= h($kid['class_name']) ?> · Kamar <?= h($kid['room_name']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else:
                    $genderCount = $mysqli->query("SELECT gender, COUNT(*) c FROM students WHERE status='aktif' GROUP BY gender")->fetch_all(MYSQLI_ASSOC);
                    $putra = 0; $putri = 0;
                    foreach ($genderCount as $g) { if ($g['gender'] === 'L') $putra = (int)$g['c']; else $putri = (int)$g['c']; }
                    $sakitHariIni = $mysqli->query("SELECT COUNT(DISTINCT student_id) c FROM attendances WHERE date=CURDATE() AND status='sakit'")->fetch_assoc()['c'];
                    $izinAktif = $mysqli->query("SELECT COUNT(*) c FROM leave_permits WHERE status='active'")->fetch_assoc()['c'];
                    $pelanggaranProses = $mysqli->query("SELECT COUNT(*) c FROM violations WHERE verdict='proses'")->fetch_assoc()['c'];

                    $attWeek = $mysqli->query(
                        "SELECT date, status, COUNT(DISTINCT student_id) c FROM attendances
                         WHERE date >= CURDATE() - INTERVAL 6 DAY GROUP BY date, status ORDER BY date"
                    )->fetch_all(MYSQLI_ASSOC);
                    $weekMap = [];
                    foreach ($attWeek as $row) { $weekMap[$row['date']][$row['status']] = (int) $row['c']; }

                    $internalAllowed = hasRole($EDITOR_ROLES, $ROLES);
                    $agendaQuery = $internalAllowed
                        ? "SELECT * FROM calendar_events WHERE start_date >= CURDATE() ORDER BY start_date LIMIT 5"
                        : "SELECT * FROM calendar_events WHERE start_date >= CURDATE() AND visibility='public' ORDER BY start_date LIMIT 5";
                    $agenda = $mysqli->query($agendaQuery)->fetch_all(MYSQLI_ASSOC);

                    $bindRoles = count($ROLES) ? $ROLES : ['__none__'];
                    $shortcutStmt = safePrepare($mysqli, "SELECT * FROM shortcuts WHERE role_name='*' OR role_name IN (" . implode(',', array_fill(0, count($bindRoles), '?')) . ")");
                    $shortcutStmt->bind_param(str_repeat('s', count($bindRoles)), ...$bindRoles);
                    $shortcutStmt->execute();
                    $shortcuts = $shortcutStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    $shortcutStmt->close();
                    ?>
                    <div class="stat-grid">
                        <div class="stat-card"><div class="num"><?= $putra + $putri ?></div><div class="lbl">Total Santri Aktif</div></div>
                        <div class="stat-card"><div class="num"><?= $putra ?></div><div class="lbl">Santri Putra</div></div>
                        <div class="stat-card"><div class="num"><?= $putri ?></div><div class="lbl">Santri Putri</div></div>
                        <div class="stat-card"><div class="num"><?= $sakitHariIni ?></div><div class="lbl">Sakit Hari Ini</div></div>
                        <div class="stat-card"><div class="num"><?= $izinAktif ?></div><div class="lbl">Sedang Izin/Pulang</div></div>
                        <div class="stat-card"><div class="num"><?= $pelanggaranProses ?></div><div class="lbl">Pelanggaran Menunggu Vonis</div></div>
                    </div>
                    <div class="two-col">
                        <div class="panel">
                            <h2>Absensi 7 Hari Terakhir</h2>
                            <?php for ($i = 6; $i >= 0; $i--):
                                $d = date('Y-m-d', strtotime("-$i day"));
                                $row = $weekMap[$d] ?? [];
                                $total = array_sum($row) ?: 1;
                                ?>
                                <div class="bar-row">
                                    <div class="day"><?= date('d M', strtotime($d)) ?></div>
                                    <div class="bar-track">
                                        <?php foreach (['hadir','sakit','izin','alpha'] as $st): if (!empty($row[$st])): ?>
                                            <div class="bar-fill <?= $st ?>" style="width:<?= ($row[$st] / $total) * 100 ?>%"></div>
                                        <?php endif; endforeach; ?>
                                    </div>
                                </div>
                            <?php endfor; ?>
                            <div class="legend">
                                <span><i style="background:var(--emerald-700)"></i>Hadir</span>
                                <span><i style="background:var(--amber)"></i>Sakit</span>
                                <span><i style="background:#8fa791"></i>Izin</span>
                                <span><i style="background:var(--red)"></i>Alpha</span>
                            </div>
                        </div>
                        <div class="panel">
                            <h2>Agenda Terdekat</h2>
                            <?php if (empty($agenda)): ?><p class="empty-state">Belum ada agenda mendatang.</p><?php endif; ?>
                            <?php foreach ($agenda as $ev): ?>
                                <div class="agenda-item">
                                    <div class="date"><?= date('d M Y', strtotime($ev['start_date'])) ?><?= $ev['visibility'] === 'internal' ? '<span class="badge-internal">Internal</span>' : '' ?></div>
                                    <div><?= h($ev['title']) ?></div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (!empty($shortcuts)): ?>
                                <h3 style="margin-top:20px;">Pintasan Drive</h3>
                                <?php foreach ($shortcuts as $sc): ?><a class="shortcut-link" href="<?= h($sc['drive_url']) ?>" target="_blank"><?= h($sc['label']) ?></a><?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php break;

                // ---------------------------------------------------------
                case 'direktori':
                // ---------------------------------------------------------
                $canManageStudents = hasRole($DATA_MGMT_ROLES, $ROLES);
                $search = trim($_GET['q'] ?? '');
                $filterClass = $_GET['class_id'] ?? '';
                $filterRoom = $_GET['room_id'] ?? '';
                $students = studentQuery($mysqli, $search, $filterClass, $filterRoom);
                $editStudent = null;
                if ($canManageStudents && isset($_GET['edit'])) {
                    $es = safePrepare($mysqli, 'SELECT * FROM students WHERE id=?');
                    $eid = (int) $_GET['edit'];
                    $es->bind_param('i', $eid); $es->execute();
                    $editStudent = $es->get_result()->fetch_assoc(); $es->close();
                }
                ?>
                <?php if ($canManageStudents): ?>
                <details class="form-toggle" <?= $editStudent ? 'open' : '' ?>>
                    <summary><?= $editStudent ? '✎ Edit Santri: ' . h($editStudent['name']) : '+ Tambah Santri Baru' ?></summary>
                    <div class="panel">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?= $editStudent ? 'edit_student' : 'add_student' ?>">
                            <input type="hidden" name="tab" value="direktori">
                            <?php if ($editStudent): ?><input type="hidden" name="student_id" value="<?= $editStudent['id'] ?>"><?php endif; ?>
                            <div class="form-grid">
                                <div class="field-group"><label>NIS</label><input type="text" name="nis" class="field-input" required value="<?= h($editStudent['nis'] ?? '') ?>"></div>
                                <div class="field-group"><label>NISN</label><input type="text" name="nisn" class="field-input" value="<?= h($editStudent['nisn'] ?? '') ?>"></div>
                                <div class="field-group"><label>Nama Lengkap</label><input type="text" name="name" class="field-input" required value="<?= h($editStudent['name'] ?? '') ?>"></div>
                                <div class="field-group"><label>Jenis Kelamin</label>
                                    <select name="gender" class="field-input">
                                        <option value="L" <?= ($editStudent['gender'] ?? '') === 'L' ? 'selected' : '' ?>>Putra</option>
                                        <option value="P" <?= ($editStudent['gender'] ?? '') === 'P' ? 'selected' : '' ?>>Putri</option>
                                    </select>
                                </div>
                                <div class="field-group"><label>Tanggal Lahir</label><input type="date" name="birth_date" class="field-input" required value="<?= h($editStudent['birth_date'] ?? '') ?>"></div>
                                <div class="field-group"><label>Asal Daerah</label><input type="text" name="origin" class="field-input" value="<?= h($editStudent['origin'] ?? '') ?>"></div>
                                <div class="field-group"><label>Kelas</label>
                                    <select name="class_id" class="field-input" required>
                                        <?php foreach ($allClasses as $c): ?><option value="<?= $c['id'] ?>" <?= ($editStudent['class_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="field-group"><label>Kamar</label>
                                    <select name="room_id" class="field-input" required>
                                        <?php foreach ($allRooms as $r): ?><option value="<?= $r['id'] ?>" <?= ($editStudent['room_id'] ?? 0) == $r['id'] ? 'selected' : '' ?>><?= h($r['name']) ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                                <?php if ($editStudent): ?>
                                <div class="field-group"><label>Status</label>
                                    <select name="status" class="field-input">
                                        <?php foreach (['aktif','alumni','keluar'] as $st): ?><option value="<?= $st ?>" <?= $editStudent['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                                <?php else: ?>
                                <div class="field-group"><label>No. WA Wali</label><input type="text" name="family_phone" class="field-input" required></div>
                                <div class="field-group"><label>Nama Wali</label><input type="text" name="family_name" class="field-input" required></div>
                                <?php endif; ?>
                            </div>
                            <button class="btn" type="submit"><?= $editStudent ? 'Simpan Perubahan' : 'Tambahkan Santri' ?></button>
                            <?php if ($editStudent): ?><a href="?tab=direktori" class="btn secondary">Batal</a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin menghapus PERMANEN data santri ini beserta seluruh riwayatnya?');">
                                <input type="hidden" name="action" value="delete_student">
                                <input type="hidden" name="tab" value="direktori">
                                <input type="hidden" name="student_id" value="<?= $editStudent['id'] ?>">
                                <button type="submit" class="btn danger">Hapus Permanen</button>
                            </form>
                            <?php endif; ?>
                        </form>
                    </div>
                </details>
                <?php endif; ?>

                <form class="toolbar" method="GET" id="directoriFilterForm" onsubmit="return false;">
                    <input type="hidden" name="tab" value="direktori">
                    <input type="text" id="qStudent" placeholder="Cari nama atau NIS… (jeda 2 detik)" value="<?= h($search) ?>">
                    <span class="search-spinner" id="qStudentSpinner">mencari…</span>
                    <select name="class_id" id="filterClass" class="field-input">
                        <option value="">Semua Kelas</option>
                        <?php foreach ($allClasses as $c): ?><option value="<?= $c['id'] ?>" <?= $filterClass == $c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option><?php endforeach; ?>
                    </select>
                    <select name="room_id" id="filterRoom" class="field-input">
                        <option value="">Semua Kamar</option>
                        <?php foreach ($allRooms as $r): ?><option value="<?= $r['id'] ?>" <?= $filterRoom == $r['id'] ? 'selected' : '' ?>><?= h($r['name']) ?></option><?php endforeach; ?>
                    </select>
                </form>

                <div class="student-grid" id="studentGrid">
                    <?php renderStudentGrid($students, $canManageStudents); ?>
                </div>

                <div class="modal-backdrop" id="idModal">
                    <div class="id-card">
                        <button class="close-modal" onclick="closeIdCard()">&times;</button>
                        <div class="id-initial" id="mInitial"></div>
                        <h3 id="mName"></h3>
                        <div class="id-nis" id="mNis"></div>
                        <div class="id-row"><span>Kelas</span><span id="mClass"></span></div>
                        <div class="id-row"><span>Kamar</span><span id="mRoom"></span></div>
                        <div class="id-row"><span>Jenis Kelamin</span><span id="mGender"></span></div>
                        <div class="id-row"><span>Asal Daerah</span><span id="mOrigin"></span></div>
                        <div class="id-row"><span>Tanggal Lahir</span><span id="mDob"></span></div>
                        <?php if ($canManageStudents): ?>
                        <div class="id-actions">
                            <a class="btn small" id="mEditLink" href="#">Edit</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php break;

                // ---------------------------------------------------------
                case 'guru':
                // ---------------------------------------------------------
                $canManageTeachers = hasRole($DATA_MGMT_ROLES, $ROLES);
                $searchT = trim($_GET['q'] ?? '');
                $teachers = teacherQuery($mysqli, $searchT);
                $editTeacher = null;
                if ($canManageTeachers && isset($_GET['edit'])) {
                    $et = safePrepare($mysqli, 'SELECT * FROM teachers WHERE id=?');
                    $etid = (int) $_GET['edit'];
                    $et->bind_param('i', $etid); $et->execute();
                    $editTeacher = $et->get_result()->fetch_assoc(); $et->close();
                }
                ?>
                <?php if ($canManageTeachers): ?>
                <details class="form-toggle" <?= $editTeacher ? 'open' : '' ?>>
                    <summary><?= $editTeacher ? '✎ Edit Guru: ' . h($editTeacher['name']) : '+ Tambah Guru Baru' ?></summary>
                    <div class="panel">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?= $editTeacher ? 'edit_teacher' : 'add_teacher' ?>">
                            <input type="hidden" name="tab" value="guru">
                            <?php if ($editTeacher): ?><input type="hidden" name="teacher_id" value="<?= $editTeacher['id'] ?>"><?php endif; ?>
                            <div class="form-grid">
                                <div class="field-group"><label>Kode/NIP (opsional)</label><input type="text" name="code" class="field-input" value="<?= h($editTeacher['code'] ?? '') ?>"></div>
                                <div class="field-group"><label>Nama Lengkap</label><input type="text" name="name" class="field-input" required value="<?= h($editTeacher['name'] ?? '') ?>"></div>
                                <div class="field-group"><label>Jenis Kelamin</label>
                                    <select name="gender" class="field-input">
                                        <option value="L" <?= ($editTeacher['gender'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                        <option value="P" <?= ($editTeacher['gender'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                                    </select>
                                </div>
                                <div class="field-group"><label>No. HP</label><input type="text" name="phone" class="field-input" value="<?= h($editTeacher['phone'] ?? '') ?>"></div>
                                <div class="field-group"><label>Email</label><input type="email" name="email" class="field-input" value="<?= h($editTeacher['email'] ?? '') ?>"></div>
                                <div class="field-group"><label>Jabatan</label><input type="text" name="position" class="field-input" placeholder="Wali Kelas, Pelatih Pramuka, dll" value="<?= h($editTeacher['position'] ?? '') ?>"></div>
                                <div class="field-group"><label>Bidang/Mapel</label><input type="text" name="subject" class="field-input" value="<?= h($editTeacher['subject'] ?? '') ?>"></div>
                                <?php if (!$editTeacher): ?>
                                <div class="field-group"><label>Tanggal Bergabung</label><input type="date" name="join_date" class="field-input"></div>
                                <?php else: ?>
                                <div class="field-group"><label>Status</label>
                                    <select name="status" class="field-input">
                                        <option value="aktif" <?= $editTeacher['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                        <option value="nonaktif" <?= $editTeacher['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                                    </select>
                                </div>
                                <?php endif; ?>
                            </div>
                            <button class="btn" type="submit"><?= $editTeacher ? 'Simpan Perubahan' : 'Tambahkan Guru' ?></button>
                            <?php if ($editTeacher): ?><a href="?tab=guru" class="btn secondary">Batal</a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin menghapus data guru ini? Kelas/kamar yang diampu akan otomatis kosong walinya.');">
                                <input type="hidden" name="action" value="delete_teacher">
                                <input type="hidden" name="tab" value="guru">
                                <input type="hidden" name="teacher_id" value="<?= $editTeacher['id'] ?>">
                                <button type="submit" class="btn danger">Hapus</button>
                            </form>
                            <?php endif; ?>
                        </form>
                    </div>
                </details>
                <?php endif; ?>

                <form class="toolbar" method="GET" id="teacherFilterForm" onsubmit="return false;">
                    <input type="hidden" name="tab" value="guru">
                    <input type="text" id="qTeacher" placeholder="Cari nama, jabatan, atau bidang… (jeda 2 detik)" value="<?= h($searchT) ?>">
                    <span class="search-spinner" id="qTeacherSpinner">mencari…</span>
                </form>

                <div class="student-grid" id="teacherGrid">
                    <?php renderTeacherGrid($teachers, $canManageTeachers); ?>
                </div>

                <div class="modal-backdrop" id="teacherModal">
                    <div class="id-card">
                        <button class="close-modal" onclick="closeTeacherCard()">&times;</button>
                        <div class="id-initial" id="tInitial"></div>
                        <h3 id="tName"></h3>
                        <div class="id-nis" id="tCode"></div>
                        <div class="id-row"><span>Jabatan</span><span id="tPosition"></span></div>
                        <div class="id-row"><span>Bidang</span><span id="tSubject"></span></div>
                        <div class="id-row"><span>Jenis Kelamin</span><span id="tGender"></span></div>
                        <div class="id-row"><span>No. HP</span><span id="tPhone"></span></div>
                        <div class="id-row"><span>Email</span><span id="tEmail"></span></div>
                        <div class="id-row"><span>Bergabung</span><span id="tJoin"></span></div>
                        <?php if ($canManageTeachers): ?>
                        <div class="id-actions"><a class="btn small" id="tEditLink" href="#">Edit</a></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php break;

                // ---------------------------------------------------------
                case 'absensi':
                // ---------------------------------------------------------
                $sessionLabels = [
                    'kamar_pagi'  => ['label' => 'Kamar (Pagi)',   'mark' => 'AM', 'basis' => 'room'],
                    'kamar_malam' => ['label' => 'Kamar (Malam)',  'mark' => 'PM', 'basis' => 'room'],
                    'kbm'         => ['label' => 'Kelas (KBM)',    'mark' => 'KB', 'basis' => 'class'],
                    'kegiatan'    => ['label' => 'Kegiatan/Ekskul','mark' => 'KG', 'basis' => 'class'],
                ];
                $session = $_GET['session'] ?? 'kamar_pagi';
                if (!isset($sessionLabels[$session])) $session = 'kamar_pagi';
                $basis = $sessionLabels[$session]['basis'];
                $date = $_GET['date'] ?? date('Y-m-d');
                ?>
                <div class="choice-grid">
                    <?php foreach ($sessionLabels as $key => $s): ?>
                        <a class="choice-card <?= $key === $session ? 'active' : '' ?>" href="?tab=absensi&session=<?= $key ?>&date=<?= h($date) ?>">
                            <div class="mark2"><?= $s['mark'] ?></div>
                            <div class="lbl2"><?= h($s['label']) ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php
                if ($basis === 'room') {
                    $groupId = $_GET['room_id'] ?? ($allRooms[0]['id'] ?? 0);
                    $stmt = safePrepare($mysqli, "SELECT s.id, s.name, s.nis, a.status FROM students s
                        LEFT JOIN attendances a ON a.student_id = s.id AND a.date = ? AND a.session_type = ?
                        WHERE s.room_id = ? AND s.status='aktif' ORDER BY s.name");
                    $stmt->bind_param('ssi', $date, $session, $groupId);
                } else {
                    $groupId = $_GET['class_id'] ?? ($allClasses[0]['id'] ?? 0);
                    $stmt = safePrepare($mysqli, "SELECT s.id, s.name, s.nis, a.status FROM students s
                        LEFT JOIN attendances a ON a.student_id = s.id AND a.date = ? AND a.session_type = ?
                        WHERE s.class_id = ? AND s.status='aktif' ORDER BY s.name");
                    $stmt->bind_param('ssi', $date, $session, $groupId);
                }
                $stmt->execute();
                $roster = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
                ?>
                <form class="toolbar" method="GET">
                    <input type="hidden" name="tab" value="absensi">
                    <input type="hidden" name="session" value="<?= $session ?>">
                    <input type="date" name="date" class="field-input" value="<?= h($date) ?>" onchange="this.form.submit()">
                    <?php if ($basis === 'room'): ?>
                        <select name="room_id" class="field-input" onchange="this.form.submit()">
                            <?php foreach ($allRooms as $r): ?><option value="<?= $r['id'] ?>" <?= $groupId == $r['id'] ? 'selected' : '' ?>><?= h($r['name']) ?> (<?= $r['gender'] === 'L' ? 'Putra' : 'Putri' ?>)</option><?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <select name="class_id" class="field-input" onchange="this.form.submit()">
                            <?php foreach ($allClasses as $c): ?><option value="<?= $c['id'] ?>" <?= $groupId == $c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option><?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </form>

                <div class="panel">
                    <h2><?= h($sessionLabels[$session]['label']) ?> — <?= date('d F Y', strtotime($date)) ?></h2>
                    <?php if (empty($roster)): ?>
                        <p class="empty-state">Tidak ada santri pada pilihan ini.</p>
                    <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="add_attendance_bulk">
                        <input type="hidden" name="tab" value="absensi">
                        <input type="hidden" name="date" value="<?= h($date) ?>">
                        <input type="hidden" name="session_type" value="<?= $session ?>">
                        <table class="data">
                            <thead><tr><th>Nama</th><th>NIS</th><th>Status</th></tr></thead>
                            <tbody>
                            <?php foreach ($roster as $r): ?>
                                <tr>
                                    <td><?= h($r['name']) ?></td>
                                    <td><?= h($r['nis']) ?></td>
                                    <td>
                                        <select name="status[<?= $r['id'] ?>]" class="field-input">
                                            <?php foreach (['hadir','sakit','izin','pulang','alpha'] as $st): ?>
                                                <option value="<?= $st ?>" <?= ($r['status'] ?? 'hadir') === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div style="margin-top:16px;"><button class="btn" type="submit">Simpan Absensi</button></div>
                    </form>
                    <?php endif; ?>
                </div>
                <?php break;

                // ---------------------------------------------------------
                case 'poskestren':
                // ---------------------------------------------------------
                $activePatients = $mysqli->query("SELECT m.*, s.name student_name, s.nis FROM medical_records m JOIN students s ON s.id = m.student_id WHERE m.status IN ('rawat_inap','rujuk') ORDER BY m.created_at DESC")->fetch_all(MYSQLI_ASSOC);
                $needsDoctor = $mysqli->query("SELECT m.*, s.name student_name, s.nis FROM medical_records m JOIN students s ON s.id = m.student_id WHERE m.handled_by_doctor IS NULL ORDER BY m.created_at ASC")->fetch_all(MYSQLI_ASSOC);
                ?>
                <?php if (hasRole(['admin','asisten'], $ROLES)): ?>
                <details class="form-toggle">
                    <summary>+ Input Pasien Baru</summary>
                    <div class="panel">
                        <form method="POST">
                            <input type="hidden" name="action" value="add_medical">
                            <input type="hidden" name="tab" value="poskestren">
                            <div class="form-grid">
                                <div class="field-group searchable">
                                    <label>Santri</label>
                                    <input type="text" class="field-input js-picker-text" placeholder="Ketik nama atau NIS…" autocomplete="off">
                                    <input type="hidden" name="student_id" class="js-picker-value" required>
                                    <div class="searchable-results"></div>
                                </div>
                                <div class="field-group"><label>Keluhan</label><input type="text" name="complaint" class="field-input" required></div>
                            </div>
                            <button class="btn" type="submit">Teruskan ke Dokter</button>
                        </form>
                    </div>
                </details>
                <?php endif; ?>

                <?php if (hasRole(['admin','dokter'], $ROLES) && !empty($needsDoctor)): ?>
                <div class="panel">
                    <h2>Menunggu Pemeriksaan Dokter</h2>
                    <?php foreach ($needsDoctor as $m): ?>
                        <form method="POST" style="border-bottom:1px solid var(--line); padding:14px 0;">
                            <input type="hidden" name="action" value="update_medical_doctor">
                            <input type="hidden" name="tab" value="poskestren">
                            <input type="hidden" name="record_id" value="<?= $m['id'] ?>">
                            <strong><?= h($m['student_name']) ?></strong> <span style="color:var(--muted); font-size:12px;">(NIS <?= h($m['nis']) ?>)</span>
                            <p style="font-size:13px; margin:6px 0;">Keluhan: <?= h($m['complaint']) ?></p>
                            <div class="form-grid">
                                <div class="field-group"><label>Diagnosa</label><input type="text" name="diagnosis" class="field-input" required></div>
                                <div class="field-group"><label>Resep</label><input type="text" name="prescription" class="field-input" required></div>
                            </div>
                            <div class="field-group"><label>Status Tindak Lanjut</label>
                                <select name="status" class="field-input">
                                    <option value="rawat_jalan">Rawat Jalan</option><option value="rawat_inap">Rawat Inap</option>
                                    <option value="rujuk">Rujuk RS</option><option value="sembuh">Sembuh</option>
                                </select>
                            </div>
                            <button class="btn small" type="submit">Simpan Diagnosa</button>
                        </form>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="panel">
                    <h2>Daftar Pasien Aktif</h2>
                    <?php if (empty($activePatients)): ?><p class="empty-state">Tidak ada pasien aktif saat ini.</p><?php else: ?>
                    <table class="data">
                        <thead><tr><th>Santri</th><th>Diagnosa</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($activePatients as $m): ?>
                            <tr>
                                <td><?= h($m['student_name']) ?><br><span style="color:var(--muted);font-size:11px;">NIS <?= h($m['nis']) ?></span></td>
                                <td><?= h($m['diagnosis'] ?: '—') ?></td>
                                <td><span class="status-pill <?= $m['status'] ?>"><?= ucfirst(str_replace('_',' ',$m['status'])) ?></span></td>
                                <td>
                                    <?php if (hasRole(['admin','dokter','asisten'], $ROLES)): ?>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="checkout_medical">
                                        <input type="hidden" name="tab" value="poskestren">
                                        <input type="hidden" name="record_id" value="<?= $m['id'] ?>">
                                        <button class="btn small secondary" type="submit">Check-out</button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
                <?php break;

                // ---------------------------------------------------------
                case 'mahkamah':
                // ---------------------------------------------------------
                $categories = $mysqli->query('SELECT * FROM violation_categories ORDER BY name')->fetch_all(MYSQLI_ASSOC);
                $punishmentCatalog = $mysqli->query('SELECT * FROM punishments ORDER BY FIELD(severity_hint,"ringan","sedang","berat")')->fetch_all(MYSQLI_ASSOC);
                $filterCat = $_GET['category_id'] ?? '';
                $sql = "SELECT v.*, s.name student_name, s.nis, vc.name category_name FROM violations v
                        JOIN students s ON s.id = v.student_id JOIN violation_categories vc ON vc.id = v.category_id
                        WHERE v.deleted_at IS NULL";
                $params = []; $types = '';
                if ($filterCat !== '') { $sql .= " AND v.category_id=?"; $params[] = $filterCat; $types = 'i'; }
                $sql .= " ORDER BY v.created_at DESC";
                $stmt = safePrepare($mysqli, $sql);
                if ($types) $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $violations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
                ?>
                <?php if (hasRole(['admin','sekretaris','pengurus'], $ROLES)): ?>
                <details class="form-toggle">
                    <summary>+ Catat Pelanggaran (Brute Input)</summary>
                    <div class="panel">
                        <form method="POST">
                            <input type="hidden" name="action" value="add_violation">
                            <input type="hidden" name="tab" value="mahkamah">
                            <div class="form-grid">
                                <div class="field-group"><label>Kategori</label>
                                    <select name="category_id" class="field-input" required>
                                        <?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>"><?= h($c['name']) ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="field-group searchable">
                                    <label>Santri</label>
                                    <input type="text" class="field-input js-picker-text" placeholder="Ketik nama atau NIS…" autocomplete="off">
                                    <input type="hidden" name="student_id" class="js-picker-value" required>
                                    <div class="searchable-results"></div>
                                </div>
                            </div>
                            <div class="field-group"><label>Keterangan</label><textarea name="description" class="field-input" required></textarea></div>
                            <button class="btn" type="submit">Catat</button>
                        </form>
                    </div>
                </details>
                <?php endif; ?>

                <form class="toolbar" method="GET">
                    <input type="hidden" name="tab" value="mahkamah">
                    <select name="category_id" class="field-input" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= $filterCat == $c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option><?php endforeach; ?>
                    </select>
                </form>

                <div class="panel">
                    <?php if (empty($violations)): ?><p class="empty-state">Tidak ada catatan.</p><?php endif; ?>
                    <?php foreach ($violations as $v): ?>
                        <div style="border-bottom:1px solid var(--line); padding:14px 0;">
                            <div style="display:flex; justify-content:space-between; align-items:start; gap:10px; flex-wrap:wrap;">
                                <div>
                                    <strong><?= h($v['student_name']) ?></strong> <span style="color:var(--muted); font-size:12px;">NIS <?= h($v['nis']) ?> · <?= h($v['category_name']) ?></span>
                                    <p style="font-size:13px; margin:6px 0;"><?= h($v['description']) ?></p>
                                    <?php if ($v['punishment_given']): ?><p style="font-size:12px; color:var(--emerald-700);">Hukuman: <?= h($v['punishment_given']) ?></p><?php endif; ?>
                                    <?php if ($v['verdict'] === 'pemutihan'): ?><p style="font-size:12px; color:var(--red);">Alasan pemutihan: <?= h($v['revocation_reason']) ?></p><?php endif; ?>
                                </div>
                                <span class="status-pill <?= $v['verdict'] ?>"><?= ucfirst($v['verdict']) ?><?= $v['severity'] ? ' · '.ucfirst($v['severity']) : '' ?></span>
                            </div>
                            <?php if ($v['verdict'] === 'proses' && hasRole(['admin','hakim'], $ROLES)): ?>
                                <div style="display:flex; gap:8px; margin-top:10px; flex-wrap:wrap;">
                                    <button type="button" class="btn small" onclick="openJudgeModal(<?= $v['id'] ?>, '<?= h($v['student_name']) ?>')">Vonis & Hukuman</button>
                                    <button type="button" class="btn small secondary" onclick="openPemutihanModal(<?= $v['id'] ?>)">Putihkan</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Modal Vonis & Hukuman -->
                <div class="modal-backdrop" id="judgeModal">
                    <div class="simple-modal">
                        <button class="close-modal" onclick="closeModal('judgeModal')">&times;</button>
                        <h3>Vonis & Hukuman — <span id="judgeStudentName"></span></h3>
                        <form method="POST">
                            <input type="hidden" name="action" value="judge_violation">
                            <input type="hidden" name="tab" value="mahkamah">
                            <input type="hidden" name="violation_id" id="judgeViolationId">
                            <div class="field-group"><label>Tingkat</label>
                                <select name="severity" class="field-input">
                                    <option value="ringan">Ringan</option><option value="sedang">Sedang</option><option value="berat">Berat</option>
                                </select>
                            </div>
                            <div class="field-group"><label>Hukuman (centang yang berlaku)</label>
                                <div class="checklist">
                                    <?php foreach ($punishmentCatalog as $p): ?>
                                        <label><input type="checkbox" name="punishment_ids[]" value="<?= $p['id'] ?>"> <?= h($p['label']) ?> <span style="color:var(--muted);font-size:11px;">(<?= h($p['severity_hint']) ?>)</span></label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="field-group"><label>Hukuman lain (jika tidak ada di daftar)</label><input type="text" name="punishment_custom" class="field-input" placeholder="Tulis manual di sini…"></div>
                            <button class="btn" type="submit">Simpan Vonis</button>
                        </form>
                    </div>
                </div>

                <!-- Modal Pemutihan -->
                <div class="modal-backdrop" id="pemutihanModal">
                    <div class="simple-modal">
                        <button class="close-modal" onclick="closeModal('pemutihanModal')">&times;</button>
                        <h3>Pemutihan Pelanggaran</h3>
                        <form method="POST" onsubmit="return confirm('Yakin memutihkan pelanggaran ini?');">
                            <input type="hidden" name="action" value="pemutihan_violation">
                            <input type="hidden" name="tab" value="mahkamah">
                            <input type="hidden" name="violation_id" id="pemutihanViolationId">
                            <div class="field-group"><label>Alasan Pemutihan</label><textarea name="revocation_reason" class="field-input" required></textarea></div>
                            <button class="btn" type="submit">Putihkan</button>
                        </form>
                    </div>
                </div>
                <?php break;

                // ---------------------------------------------------------
                case 'perizinan':
                // ---------------------------------------------------------
                $leaves = $mysqli->query("SELECT l.*, s.name student_name, s.nis FROM leave_permits l JOIN students s ON s.id = l.student_id ORDER BY l.start_date DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);
                ?>
                <details class="form-toggle">
                    <summary>+ Terbitkan Izin Baru</summary>
                    <div class="panel">
                        <form method="POST">
                            <input type="hidden" name="action" value="add_leave">
                            <input type="hidden" name="tab" value="perizinan">
                            <div class="form-grid">
                                <div class="field-group searchable">
                                    <label>Santri</label>
                                    <input type="text" class="field-input js-picker-text" placeholder="Ketik nama atau NIS…" autocomplete="off">
                                    <input type="hidden" name="student_id" class="js-picker-value" required>
                                    <div class="searchable-results"></div>
                                </div>
                                <div class="field-group"><label>Jenis</label>
                                    <select name="type" class="field-input"><option value="izin_keluar">Izin Keluar</option><option value="pulang">Pulang</option></select>
                                </div>
                                <div class="field-group"><label>Mulai</label><input type="datetime-local" name="start_date" class="field-input" required></div>
                                <div class="field-group"><label>Rencana Kembali</label><input type="datetime-local" name="end_date" class="field-input" required></div>
                            </div>
                            <div class="field-group"><label>Alasan</label><textarea name="reason" class="field-input" required></textarea></div>
                            <button class="btn" type="submit">Terbitkan</button>
                        </form>
                    </div>
                </details>

                <div class="panel">
                    <table class="data">
                        <thead><tr><th>Santri</th><th>Jenis</th><th>Rencana Kembali</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($leaves as $l): ?>
                            <tr>
                                <td><?= h($l['student_name']) ?><br><span style="color:var(--muted);font-size:11px;">NIS <?= h($l['nis']) ?></span></td>
                                <td><?= $l['type'] === 'pulang' ? 'Pulang' : 'Izin Keluar' ?></td>
                                <td><?= date('d M Y H:i', strtotime($l['end_date'])) ?></td>
                                <td><span class="status-pill <?= $l['status'] ?>"><?= ucfirst($l['status']) ?></span></td>
                                <td style="display:flex; gap:6px; flex-wrap:wrap;">
                                    <a class="btn small secondary" href="dashboard.php?print_leave=<?= $l['id'] ?>" target="_blank">Cetak (A6)</a>
                                    <?php if ($l['status'] === 'active'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="confirm_return">
                                        <input type="hidden" name="tab" value="perizinan">
                                        <input type="hidden" name="leave_id" value="<?= $l['id'] ?>">
                                        <button class="btn small secondary" type="submit">Konfirmasi Kembali</button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php break;

                // ---------------------------------------------------------
                case 'prestasi':
                // ---------------------------------------------------------
                $achievements = $mysqli->query("SELECT a.*, s.name student_name, s.nis FROM achievements a JOIN students s ON s.id = a.student_id ORDER BY a.event_date DESC")->fetch_all(MYSQLI_ASSOC);
                ?>
                <details class="form-toggle">
                    <summary>+ Catat Prestasi</summary>
                    <div class="panel">
                        <form method="POST">
                            <input type="hidden" name="action" value="add_achievement">
                            <input type="hidden" name="tab" value="prestasi">
                            <div class="form-grid">
                                <div class="field-group searchable">
                                    <label>Santri</label>
                                    <input type="text" class="field-input js-picker-text" placeholder="Ketik nama atau NIS…" autocomplete="off">
                                    <input type="hidden" name="student_id" class="js-picker-value" required>
                                    <div class="searchable-results"></div>
                                </div>
                                <div class="field-group"><label>Nama Kegiatan</label><input type="text" name="event_name" class="field-input" required></div>
                                <div class="field-group"><label>Capaian</label><input type="text" name="rank_achieved" class="field-input" placeholder="Juara 1, Finalis, dll" required></div>
                                <div class="field-group"><label>Tanggal</label><input type="date" name="event_date" class="field-input" required></div>
                                <div class="field-group"><label>Lokasi</label><input type="text" name="location" class="field-input"></div>
                                <div class="field-group"><label>Tingkat</label>
                                    <select name="scope" class="field-input"><option value="internal">Internal Pondok</option><option value="external">Eksternal</option></select>
                                </div>
                            </div>
                            <div class="field-group"><label>Keterangan</label><textarea name="description" class="field-input"></textarea></div>
                            <button class="btn" type="submit">Simpan</button>
                        </form>
                    </div>
                </details>

                <div class="panel">
                    <table class="data">
                        <thead><tr><th>Santri</th><th>Kegiatan</th><th>Capaian</th><th>Tanggal</th><th>Tingkat</th></tr></thead>
                        <tbody>
                        <?php foreach ($achievements as $a): ?>
                            <tr>
                                <td><?= h($a['student_name']) ?></td><td><?= h($a['event_name']) ?></td><td><?= h($a['rank_achieved']) ?></td>
                                <td><?= date('d M Y', strtotime($a['event_date'])) ?></td><td><?= $a['scope'] === 'external' ? 'Eksternal' : 'Internal' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php break;

                // ---------------------------------------------------------
                case 'korespondensi':
                // ---------------------------------------------------------
                $letters = $mysqli->query('SELECT * FROM correspondence ORDER BY created_at DESC')->fetch_all(MYSQLI_ASSOC);
                ?>
                <details class="form-toggle">
                    <summary>+ Catat Surat</summary>
                    <div class="panel">
                        <form method="POST">
                            <input type="hidden" name="action" value="add_correspondence">
                            <input type="hidden" name="tab" value="korespondensi">
                            <div class="form-grid">
                                <div class="field-group"><label>Arah Surat</label>
                                    <select name="direction" class="field-input"><option value="keluar">Surat Keluar (nomor otomatis)</option><option value="masuk">Surat Masuk</option></select>
                                </div>
                                <div class="field-group"><label>Jabatan Pengaju</label><input type="text" name="from_position" class="field-input" required></div>
                                <div class="field-group"><label>Jenis Surat</label><input type="text" name="letter_type" class="field-input" required></div>
                                <div class="field-group"><label>Tujuan</label><input type="text" name="destination" class="field-input" required></div>
                                <div class="field-group"><label>Nomor (khusus surat masuk)</label><input type="text" name="code" class="field-input"></div>
                                <div class="field-group"><label>Disposisi (khusus surat masuk)</label><input type="text" name="disposisi" class="field-input"></div>
                            </div>
                            <div class="field-group"><label>Lampiran (wajib tautan Google Docs)</label><input type="text" name="attachment_url" class="field-input" placeholder="https://docs.google.com/..."></div>
                            <button class="btn" type="submit">Simpan</button>
                        </form>
                    </div>
                </details>
                <div class="panel">
                    <table class="data">
                        <thead><tr><th>Nomor</th><th>Arah</th><th>Jenis</th><th>Tujuan/Asal</th><th>Tanggal</th></tr></thead>
                        <tbody>
                        <?php foreach ($letters as $l): ?>
                            <tr>
                                <td><?= h($l['code']) ?></td><td><?= $l['direction'] === 'keluar' ? 'Keluar' : 'Masuk' ?></td>
                                <td><?= h($l['letter_type']) ?></td><td><?= h($l['destination']) ?></td><td><?= date('d M Y', strtotime($l['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php break;

                // ---------------------------------------------------------
                case 'kalender':
                // ---------------------------------------------------------
                function renderMonthGrid(mysqli $mysqli, int $year, int $month, bool $seeInternal): void
                {
                    $first = mktime(0,0,0,$month,1,$year);
                    $daysInMonth = (int) date('t', $first);
                    $startDow = (int) date('w', $first); // 0=Minggu
                    $monthName = date('F Y', $first);
                    $todayStr = date('Y-m-d');

                    $from = date('Y-m-01', $first);
                    $to = date('Y-m-t', $first);
                    $evStmt = $mysqli->prepare("SELECT start_date, visibility FROM calendar_events WHERE start_date BETWEEN ? AND ?" . ($seeInternal ? '' : " AND visibility='public'"));
                    $evStmt->bind_param('ss', $from, $to);
                    $evStmt->execute();
                    $evRows = $evStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    $evStmt->close();
                    $eventDays = [];
                    foreach ($evRows as $e) {
                        $day = (int) date('j', strtotime($e['start_date']));
                        $eventDays[$day] = ($eventDays[$day] ?? 'public') === 'internal' || $e['visibility'] === 'internal' ? 'internal' : 'public';
                    }

                    echo '<div class="mini-month"><div class="mm-title">' . h($monthName) . '</div><div class="mm-grid">';
                    foreach (['Mg','Sn','Sl','Rb','Km','Jm','Sb'] as $dow) echo '<div class="mm-dow">' . $dow . '</div>';
                    for ($i = 0; $i < $startDow; $i++) echo '<div class="mm-day blank"></div>';
                    for ($d = 1; $d <= $daysInMonth; $d++) {
                        $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                        $cls = 'mm-day';
                        if ($dateStr === $todayStr) $cls .= ' today';
                        if (isset($eventDays[$d])) $cls .= $eventDays[$d] === 'internal' ? ' has-event has-internal' : ' has-event';
                        echo '<div class="' . $cls . '">' . $d . '</div>';
                    }
                    echo '</div></div>';
                }

                $canEditCalendar = hasRole($EDITOR_ROLES, $ROLES);
                $view = $_GET['view'] ?? 'bulan';
                $monthCount = ['bulan' => 1, '2bulan' => 2, 'semester' => 6, 'tahun' => 12][$view] ?? 1;
                $baseYear = (int) ($_GET['year'] ?? date('Y'));
                $baseMonth = (int) ($_GET['month'] ?? date('n'));

                $prevMonth = $baseMonth - $monthCount; $prevYear = $baseYear;
                while ($prevMonth < 1) { $prevMonth += 12; $prevYear--; }
                $nextMonth = $baseMonth + $monthCount; $nextYear = $baseYear;
                while ($nextMonth > 12) { $nextMonth -= 12; $nextYear++; }

                $agendaQ = $canEditCalendar
                    ? "SELECT * FROM calendar_events WHERE start_date >= CURDATE() ORDER BY start_date LIMIT 8"
                    : "SELECT * FROM calendar_events WHERE start_date >= CURDATE() AND visibility='public' ORDER BY start_date LIMIT 8";
                $upcoming = $mysqli->query($agendaQ)->fetch_all(MYSQLI_ASSOC);
                ?>
                <?php if ($canEditCalendar): ?>
                <details class="form-toggle">
                    <summary>+ Tambah Agenda</summary>
                    <div class="panel">
                        <form method="POST">
                            <input type="hidden" name="action" value="add_calendar">
                            <input type="hidden" name="tab" value="kalender">
                            <div class="form-grid">
                                <div class="field-group"><label>Judul</label><input type="text" name="title" class="field-input" required></div>
                                <div class="field-group"><label>Kategori</label>
                                    <select name="category" class="field-input"><option value="akademik">Akademik</option><option value="pengasuhan">Pengasuhan</option><option value="umum">Umum</option></select>
                                </div>
                                <div class="field-group"><label>Tanggal Mulai</label><input type="date" name="start_date" class="field-input" required></div>
                                <div class="field-group"><label>Tanggal Selesai (opsional)</label><input type="date" name="end_date" class="field-input"></div>
                                <div class="field-group"><label>Visibilitas</label>
                                    <select name="visibility" class="field-input"><option value="public">Publik</option><option value="internal">Internal (staf saja)</option></select>
                                </div>
                            </div>
                            <div class="field-group"><label>Deskripsi</label><textarea name="description" class="field-input"></textarea></div>
                            <button class="btn" type="submit">Tambahkan</button>
                        </form>
                    </div>
                </details>
                <?php endif; ?>

                <div class="two-col">
                    <div>
                        <div class="cal-toolbar">
                            <div class="cal-nav">
                                <a class="btn secondary small" href="?tab=kalender&view=<?= $view ?>&year=<?= $prevYear ?>&month=<?= $prevMonth ?>">&larr;</a>
                                <strong><?= h(date('F Y', mktime(0,0,0,$baseMonth,1,$baseYear))) ?></strong>
                                <a class="btn secondary small" href="?tab=kalender&view=<?= $view ?>&year=<?= $nextYear ?>&month=<?= $nextMonth ?>">&rarr;</a>
                            </div>
                            <form method="GET">
                                <input type="hidden" name="tab" value="kalender">
                                <input type="hidden" name="year" value="<?= $baseYear ?>">
                                <input type="hidden" name="month" value="<?= $baseMonth ?>">
                                <select name="view" class="field-input" onchange="this.form.submit()">
                                    <option value="bulan" <?= $view === 'bulan' ? 'selected' : '' ?>>1 Bulan</option>
                                    <option value="2bulan" <?= $view === '2bulan' ? 'selected' : '' ?>>2 Bulan</option>
                                    <option value="semester" <?= $view === 'semester' ? 'selected' : '' ?>>1 Semester</option>
                                    <option value="tahun" <?= $view === 'tahun' ? 'selected' : '' ?>>1 Tahun Ajaran</option>
                                </select>
                            </form>
                        </div>
                        <div class="month-grids n<?= $monthCount ?>">
                            <?php
                            $y = $baseYear; $m = $baseMonth;
                            for ($i = 0; $i < $monthCount; $i++) {
                                renderMonthGrid($mysqli, $y, $m, $canEditCalendar);
                                $m++; if ($m > 12) { $m = 1; $y++; }
                            }
                            ?>
                        </div>
                        <div class="legend" style="margin-top:14px;">
                            <span><i style="background:var(--emerald-700)"></i>Agenda Publik</span>
                            <span><i style="background:var(--amber)"></i>Agenda Internal</span>
                            <span><i style="background:var(--gold-soft)"></i>Hari Ini</span>
                        </div>
                    </div>
                    <div class="panel">
                        <h2>Agenda Mendatang</h2>
                        <?php if (empty($upcoming)): ?><p class="empty-state">Belum ada agenda.</p><?php endif; ?>
                        <?php foreach ($upcoming as $ev): ?>
                            <div class="agenda-item">
                                <div class="date"><?= date('d M Y', strtotime($ev['start_date'])) ?><?= $ev['visibility'] === 'internal' ? '<span class="badge-internal">Internal</span>' : '' ?></div>
                                <strong><?= h($ev['title']) ?></strong>
                                <p style="font-size:12.5px; color:var(--muted); margin:4px 0 0;"><?= h($ev['description']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php break;

                // ---------------------------------------------------------
                case 'anak_saya':
                // ---------------------------------------------------------
                $children = safePrepare($mysqli, 'SELECT s.*, c.name class_name, r.name room_name FROM students s JOIN classes c ON c.id=s.class_id JOIN rooms r ON r.id=s.room_id WHERE s.family_id=?');
                $children->bind_param('i', $FAMILY_ID);
                $children->execute();
                $kids = $children->get_result()->fetch_all(MYSQLI_ASSOC);
                $children->close();

                foreach ($kids as $kid):
                    $sid = $kid['id'];
                    $att = safePrepare($mysqli, "SELECT date, session_type, status FROM attendances WHERE student_id=? ORDER BY date DESC, session_type LIMIT 7");
                    $att->bind_param('i', $sid); $att->execute();
                    $attRows = $att->get_result()->fetch_all(MYSQLI_ASSOC); $att->close();

                    $ach = safePrepare($mysqli, "SELECT * FROM achievements WHERE student_id=? ORDER BY event_date DESC LIMIT 3");
                    $ach->bind_param('i', $sid); $ach->execute();
                    $achRows = $ach->get_result()->fetch_all(MYSQLI_ASSOC); $ach->close();

                    $viol = safePrepare($mysqli, "SELECT vc.name category_name, v.verdict FROM violations v JOIN violation_categories vc ON vc.id=v.category_id WHERE v.student_id=? AND v.deleted_at IS NULL ORDER BY v.created_at DESC LIMIT 3");
                    $viol->bind_param('i', $sid); $viol->execute();
                    $violRows = $viol->get_result()->fetch_all(MYSQLI_ASSOC); $viol->close();
                    ?>
                    <div class="panel">
                        <h2><?= h($kid['name']) ?> <span style="font-weight:400; color:var(--muted); font-size:13px;">NIS <?= h($kid['nis']) ?> · <?= h($kid['class_name']) ?> · Kamar <?= h($kid['room_name']) ?></span></h2>
                        <div class="two-col">
                            <div>
                                <h3>Absensi Terakhir</h3>
                                <?php foreach ($attRows as $a): ?><div class="agenda-item"><?= date('d M', strtotime($a['date'])) ?> (<?= h($a['session_type']) ?>) — <span class="status-pill <?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span></div><?php endforeach; ?>
                                <?php if (empty($attRows)): ?><p class="empty-state">Belum ada data absensi.</p><?php endif; ?>
                            </div>
                            <div>
                                <h3>Prestasi Terbaru</h3>
                                <?php foreach ($achRows as $a): ?><div class="agenda-item"><?= h($a['event_name']) ?> — <?= h($a['rank_achieved']) ?></div><?php endforeach; ?>
                                <?php if (empty($achRows)): ?><p class="empty-state">Belum ada prestasi tercatat.</p><?php endif; ?>
                                <h3 style="margin-top:16px;">Catatan Mahkamah</h3>
                                <?php foreach ($violRows as $v): ?><div class="agenda-item"><?= h($v['category_name']) ?> — <span class="status-pill <?= $v['verdict'] ?>"><?= ucfirst($v['verdict']) ?></span></div><?php endforeach; ?>
                                <?php if (empty($violRows)): ?><p class="empty-state">Tidak ada catatan.</p><?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach;
                if (empty($kids)): ?><p class="empty-state">Tidak ada data santri terhubung ke akun ini.</p><?php endif;
                break;

                // ---------------------------------------------------------
                case 'pengguna':
                // ---------------------------------------------------------
                $users = $mysqli->query(
                    "SELECT u.id, u.name, u.email, u.phone FROM users u ORDER BY u.name"
                )->fetch_all(MYSQLI_ASSOC);
                // role aktif per user (untuk badge & untuk pre-fill form edit)
                $roleMap = [];
                $rmRes = $mysqli->query("SELECT ra.user_id, r.id role_id, r.name role_name FROM role_assignments ra JOIN roles r ON r.id=ra.role_id WHERE ra.mulai_berlaku <= CURDATE() AND (ra.selesai_berlaku IS NULL OR ra.selesai_berlaku >= CURDATE())");
                foreach ($rmRes->fetch_all(MYSQLI_ASSOC) as $row) { $roleMap[$row['user_id']][] = $row; }

                $editUser = null;
                if (isset($_GET['edit_user'])) {
                    $euId = (int) $_GET['edit_user'];
                    foreach ($users as $u) { if ($u['id'] == $euId) { $editUser = $u; break; } }
                }
                ?>
                <details class="form-toggle" <?= $editUser ? 'open' : '' ?>>
                    <summary><?= $editUser ? '✎ Edit Pengguna: ' . h($editUser['name']) : '+ Tambah Pengguna' ?></summary>
                    <div class="panel">
                        <?php if ($editUser): ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="edit_user">
                            <input type="hidden" name="tab" value="pengguna">
                            <input type="hidden" name="user_id" value="<?= $editUser['id'] ?>">
                            <div class="form-grid">
                                <div class="field-group"><label>Nama</label><input type="text" name="name" class="field-input" required value="<?= h($editUser['name']) ?>"></div>
                                <div class="field-group"><label>Email</label><input type="email" name="email" class="field-input" value="<?= h($editUser['email'] ?? '') ?>"></div>
                                <div class="field-group"><label>No. WhatsApp</label><input type="text" name="phone" class="field-input" value="<?= h($editUser['phone'] ?? '') ?>"></div>
                                <div class="field-group"><label>Kata Sandi Baru (kosongkan jika tidak diganti)</label><input type="text" name="password" class="field-input"></div>
                            </div>
                            <div class="field-group"><label>Role Aktif</label>
                                <div class="checklist">
                                    <?php $activeRoleIds = array_column($roleMap[$editUser['id']] ?? [], 'role_id'); ?>
                                    <?php foreach ($allRolesList as $r): ?>
                                        <label><input type="checkbox" name="role_ids[]" value="<?= $r['id'] ?>" <?= in_array($r['id'], $activeRoleIds) ? 'checked' : '' ?>> <?= h($r['name']) ?></label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <button class="btn" type="submit">Simpan Perubahan</button>
                            <a href="?tab=pengguna" class="btn secondary">Batal</a>
                        </form>
                        <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="add_user">
                            <input type="hidden" name="tab" value="pengguna">
                            <div class="form-grid">
                                <div class="field-group"><label>Nama</label><input type="text" name="name" class="field-input" required></div>
                                <div class="field-group"><label>Peran (Role) Awal</label>
                                    <select name="role_id" class="field-input" required>
                                        <?php foreach ($allRolesList as $r): ?><option value="<?= $r['id'] ?>"><?= h($r['name']) ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="field-group"><label>Email (staf @daarululuumlido.com)</label><input type="email" name="email" class="field-input"></div>
                                <div class="field-group"><label>No. WhatsApp (Wali Santri)</label><input type="text" name="phone" class="field-input"></div>
                                <div class="field-group"><label>Kata Sandi Sementara</label><input type="text" name="password" class="field-input" required></div>
                            </div>
                            <button class="btn" type="submit">Simpan</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </details>

                <div class="panel">
                    <table class="data">
                        <thead><tr><th>Nama</th><th>Email</th><th>No. WA</th><th>Role Aktif</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= h($u['name']) ?></td>
                                <td><?= h($u['email'] ?: '—') ?></td>
                                <td><?= h($u['phone'] ?: '—') ?></td>
                                <td><?= h(implode(', ', array_column($roleMap[$u['id']] ?? [], 'role_name')) ?: 'Tanpa role aktif') ?></td>
                                <td><a class="btn small secondary" href="?tab=pengguna&edit_user=<?= $u['id'] ?>">Edit</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php break;

            endswitch;
            ?>
        </div>
    </div>
</div>

<script>
    // ============================================================
    // DATA SANTRI UNTUK SEARCHABLE-SELECT (typeahead di semua form)
    // ============================================================
    const STUDENT_PICKER_DATA = <?= $studentPickerJson ?: '[]' ?>;

    // ============================================================
    // SIDEBAR MOBILE — perbaikan: bisa dibuka & ditutup lagi + backdrop buram
    // ============================================================
    function openSidebar() {
        document.getElementById('sidebar').classList.add('open');
        document.getElementById('sidebarBackdrop').classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarBackdrop').classList.remove('show');
        document.body.style.overflow = '';
    }
    // Tutup otomatis kalau layar dilebarkan lagi ke ukuran desktop
    window.addEventListener('resize', function () {
        if (window.innerWidth > 880) closeSidebar();
    });

    // ============================================================
    // MODAL GENERIK
    // ============================================================
    function closeModal(id) { document.getElementById(id).classList.remove('show'); }
    function openModalById(id) { document.getElementById(id).classList.add('show'); }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.show').forEach(m => m.classList.remove('show'));
        }
    });
    document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
        backdrop.addEventListener('click', function (e) {
            if (e.target === backdrop) backdrop.classList.remove('show');
        });
    });

    // ---- Kartu ID Santri ----
    function openIdCard(card) {
        document.getElementById('mInitial').textContent = card.dataset.name.charAt(0);
        document.getElementById('mName').textContent = card.dataset.name;
        document.getElementById('mNis').textContent = 'NIS ' + card.dataset.nis;
        document.getElementById('mClass').textContent = card.dataset.class;
        document.getElementById('mRoom').textContent = card.dataset.room;
        document.getElementById('mGender').textContent = card.dataset.gender;
        document.getElementById('mOrigin').textContent = card.dataset.origin || '—';
        document.getElementById('mDob').textContent = card.dataset.dob;
        const editLink = document.getElementById('mEditLink');
        if (editLink) editLink.href = '?tab=direktori&edit=' + card.dataset.id;
        openModalById('idModal');
    }
    function closeIdCard() { closeModal('idModal'); }

    // ---- Kartu Guru ----
    function openTeacherCard(card) {
        document.getElementById('tInitial').textContent = card.dataset.name.charAt(0);
        document.getElementById('tName').textContent = card.dataset.name;
        document.getElementById('tCode').textContent = card.dataset.code || '—';
        document.getElementById('tPosition').textContent = card.dataset.position || '—';
        document.getElementById('tSubject').textContent = card.dataset.subject || '—';
        document.getElementById('tGender').textContent = card.dataset.gender;
        document.getElementById('tPhone').textContent = card.dataset.phone || '—';
        document.getElementById('tEmail').textContent = card.dataset.email || '—';
        document.getElementById('tJoin').textContent = card.dataset.join || '—';
        const editLink = document.getElementById('tEditLink');
        if (editLink) editLink.href = '?tab=guru&edit=' + card.dataset.id;
        openModalById('teacherModal');
    }
    function closeTeacherCard() { closeModal('teacherModal'); }

    // ---- Modal Vonis & Pemutihan (Mahkamah) ----
    function openJudgeModal(violationId, studentName) {
        document.getElementById('judgeViolationId').value = violationId;
        document.getElementById('judgeStudentName').textContent = studentName;
        openModalById('judgeModal');
    }
    function openPemutihanModal(violationId) {
        document.getElementById('pemutihanViolationId').value = violationId;
        openModalById('pemutihanModal');
    }

    // ============================================================
    // SEARCHABLE SELECT (typeahead santri) — dipakai di Poskestren,
    // Mahkamah, Perizinan, Prestasi. Menggantikan <select> panjang.
    // ============================================================
    document.querySelectorAll('.searchable').forEach(function (wrap) {
        const textInput = wrap.querySelector('.js-picker-text');
        const hiddenInput = wrap.querySelector('.js-picker-value');
        const resultsBox = wrap.querySelector('.searchable-results');
        if (!textInput || !hiddenInput || !resultsBox) return;

        function renderOptions(list) {
            resultsBox.innerHTML = '';
            if (list.length === 0) {
                resultsBox.innerHTML = '<div class="opt" style="color:var(--muted);">Tidak ditemukan</div>';
            } else {
                list.slice(0, 20).forEach(function (s) {
                    const opt = document.createElement('div');
                    opt.className = 'opt';
                    opt.innerHTML = s.name + '<small>NIS ' + s.nis + '</small>';
                    opt.addEventListener('click', function () {
                        hiddenInput.value = s.id;
                        textInput.value = s.name + ' — ' + s.nis;
                        resultsBox.classList.remove('show');
                    });
                    resultsBox.appendChild(opt);
                });
            }
            resultsBox.classList.add('show');
        }

        textInput.addEventListener('input', function () {
            hiddenInput.value = ''; // batalkan pilihan lama selama masih mengetik
            const kw = textInput.value.trim().toLowerCase();
            if (kw === '') { resultsBox.classList.remove('show'); return; }
            const filtered = STUDENT_PICKER_DATA.filter(function (s) {
                return s.name.toLowerCase().includes(kw) || s.nis.toLowerCase().includes(kw);
            });
            renderOptions(filtered);
        });
        textInput.addEventListener('focus', function () {
            if (textInput.value.trim() !== '') textInput.dispatchEvent(new Event('input'));
        });
        document.addEventListener('click', function (e) {
            if (!wrap.contains(e.target)) resultsBox.classList.remove('show');
        });
    });

    // ============================================================
    // LIVE SEARCH — Direktori Santri & Guru (AJAX, jeda 2 detik)
    // ============================================================
    function debounce(fn, delay) {
        let t;
        return function (...args) { clearTimeout(t); t = setTimeout(() => fn.apply(this, args), delay); };
    }

    const qStudent = document.getElementById('qStudent');
    if (qStudent) {
        const grid = document.getElementById('studentGrid');
        const spinner = document.getElementById('qStudentSpinner');
        const classSel = document.getElementById('filterClass');
        const roomSel = document.getElementById('filterRoom');

        function fetchStudents() {
            spinner.classList.add('show');
            const params = new URLSearchParams({
                tab: 'direktori', ajax: 'students',
                q: qStudent.value.trim(), class_id: classSel.value, room_id: roomSel.value
            });
            fetch('dashboard.php?' + params.toString())
                .then(r => r.text())
                .then(html => { grid.innerHTML = html; spinner.classList.remove('show'); })
                .catch(() => { spinner.classList.remove('show'); });
        }
        qStudent.addEventListener('input', debounce(fetchStudents, 2000));
        classSel.addEventListener('change', fetchStudents);
        roomSel.addEventListener('change', fetchStudents);
    }

    const qTeacher = document.getElementById('qTeacher');
    if (qTeacher) {
        const grid = document.getElementById('teacherGrid');
        const spinner = document.getElementById('qTeacherSpinner');
        function fetchTeachers() {
            spinner.classList.add('show');
            const params = new URLSearchParams({ tab: 'guru', ajax: 'teachers', q: qTeacher.value.trim() });
            fetch('dashboard.php?' + params.toString())
                .then(r => r.text())
                .then(html => { grid.innerHTML = html; spinner.classList.remove('show'); })
                .catch(() => { spinner.classList.remove('show'); });
        }
        qTeacher.addEventListener('input', debounce(fetchTeachers, 2000));
    }
</script>
</body>
</html>
