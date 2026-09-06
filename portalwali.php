<?php
/**
 * HISADA — Portal Wali Santri
 * Satu file utuh: PHP + HTML + CSS + JS. Hanya menampilkan data anak
 * dari akun Wali Santri yang sedang login (family_id).
 */
const DB_HOST = 'localhost';
const DB_NAME = 'cpnpmuy3608_hisada_database';
const DB_USER = 'cpnpmuy3608_hisada';
const DB_PASS = 'Dulido1996';

mysqli_report(MYSQLI_REPORT_OFF);
session_start();
if (empty($_SESSION['uid'])) { header('Location: index.php'); exit; }

$ROLES = $_SESSION['roles'] ?? [];
if (!in_array('wali_santri', $ROLES, true)) { header('Location: dashboard.php'); exit; }

$mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_errno) { die('Tidak bisa terhubung ke database (' . $mysqli->connect_error . ').'); }
$mysqli->set_charset('utf8mb4');

function h(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function safePrepare(mysqli $mysqli, string $sql): mysqli_stmt {
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { die('Query gagal disiapkan: ' . h($mysqli->error)); }
    return $stmt;
}

$NAME = $_SESSION['name'];
$FAMILY_ID = $_SESSION['family_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'logout') {
    session_destroy(); header('Location: index.php'); exit;
}

$children = [];
if ($FAMILY_ID) {
    $stmt = safePrepare($mysqli, 'SELECT s.*, c.name class_name, r.name room_name FROM students s JOIN classes c ON c.id=s.class_id JOIN rooms r ON r.id=s.room_id WHERE s.family_id=?');
    $stmt->bind_param('i', $FAMILY_ID);
    $stmt->execute();
    $children = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$upcoming = $mysqli->query("SELECT * FROM calendar_events WHERE start_date >= CURDATE() AND visibility='public' ORDER BY start_date LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Portal Wali Santri — HISADA</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root{ --ink:#16241c; --emerald-900:#0b3d2e; --emerald-700:#166247; --gold:#c9a13b; --gold-soft:#e6cf8f; --cream:#f7f4ec; --paper:#fff; --line:#e3ddc9; --muted:#6b7a70; --red:#b3432b; --amber:#a9791f; }
    *{box-sizing:border-box;} body{margin:0;font-family:'Inter',sans-serif;color:var(--ink);background:var(--cream);}
    .topbar{ background:var(--emerald-900); color:#f4efe1; padding:18px 28px; display:flex; justify-content:space-between; align-items:center; }
    .topbar .brand{display:flex; align-items:center; gap:10px;}
    .topbar .seal{ width:34px;height:34px;border-radius:10px; background:rgba(244,239,225,.12); display:flex;align-items:center;justify-content:center; font-family:'Fraunces',serif; color:var(--gold-soft); font-weight:600; }
    .topbar h1{font-family:'Fraunces',serif; font-size:16px; margin:0;}
    .topbar span{font-size:11px; color:#cddbd2;}
    .btn-logout{ background:none; border:1px solid rgba(244,239,225,.3); color:#f4efe1; padding:7px 14px; border-radius:8px; font-size:12.5px; cursor:pointer; }
    .content{max-width:920px; margin:0 auto; padding:28px 20px 60px;}
    .panel{ background:var(--paper); border:1px solid var(--line); border-radius:14px; padding:22px; margin-bottom:20px; }
    .panel h2{font-family:'Fraunces',serif; font-size:16.5px; margin:0 0 4px;}
    .panel h3{font-size:13px; font-weight:600; margin:0 0 12px; color:var(--muted); text-transform:uppercase; letter-spacing:.03em;}
    .two-col{display:grid; grid-template-columns:1.3fr 1fr; gap:16px;} @media (max-width:760px){.two-col{grid-template-columns:1fr;}}
    .agenda-item{padding:10px 0; border-bottom:1px solid var(--line); font-size:13px;} .agenda-item:last-child{border:none;}
    .agenda-item .date{font-size:11px; color:var(--gold); font-weight:600; text-transform:uppercase;}
    .status-pill{font-size:10.5px; padding:2px 9px; border-radius:20px; font-weight:600;}
    .status-pill.hadir, .status-pill.divonis{background:#e2f0e6; color:var(--emerald-700);}
    .status-pill.sakit, .status-pill.proses{background:#fbeee0; color:var(--amber);}
    .status-pill.izin{background:#eef1ea; color:#5b6b60;}
    .status-pill.alpha, .status-pill.pemutihan{background:#fbeae5; color:var(--red);}
    .empty-state{padding:16px; text-align:center; color:var(--muted); font-size:13px;}
</style>
</head>
<body>
    <div class="topbar">
        <div class="brand"><div class="seal">DU</div><div><h1>Portal Wali Santri — HISADA</h1><span><?= h($NAME) ?></span></div></div>
        <form method="POST"><input type="hidden" name="action" value="logout"><button class="btn-logout" type="submit">Keluar</button></form>
    </div>
    <div class="content">
        <?php if (empty($children)): ?>
            <p class="empty-state">Tidak ada data santri terhubung ke akun ini. Hubungi Admin jika ini keliru.</p>
        <?php endif; ?>

        <?php foreach ($children as $kid):
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

            $leave = safePrepare($mysqli, "SELECT * FROM leave_permits WHERE student_id=? ORDER BY start_date DESC LIMIT 1");
            $leave->bind_param('i', $sid); $leave->execute();
            $leaveRow = $leave->get_result()->fetch_assoc(); $leave->close();
            ?>
            <div class="panel">
                <h2><?= h($kid['name']) ?></h2>
                <p style="font-size:12px;color:var(--muted); margin:0 0 16px;">NIS <?= h($kid['nis']) ?> · <?= h($kid['class_name']) ?> · Kamar <?= h($kid['room_name']) ?></p>
                <div class="two-col">
                    <div>
                        <h3>Absensi Terakhir</h3>
                        <?php foreach ($attRows as $a): ?><div class="agenda-item"><?= date('d M', strtotime($a['date'])) ?> (<?= h($a['session_type']) ?>) — <span class="status-pill <?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span></div><?php endforeach; ?>
                        <?php if (empty($attRows)): ?><p class="empty-state">Belum ada data absensi.</p><?php endif; ?>
                        <?php if ($leaveRow): ?>
                            <h3 style="margin-top:16px;">Izin Terakhir</h3>
                            <div class="agenda-item"><?= $leaveRow['type'] === 'pulang' ? 'Pulang' : 'Izin Keluar' ?> — <span class="status-pill <?= $leaveRow['status'] ?>"><?= ucfirst($leaveRow['status']) ?></span><br><span style="color:var(--muted);font-size:11px;">Kembali: <?= date('d M Y H:i', strtotime($leaveRow['end_date'])) ?></span></div>
                        <?php endif; ?>
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
        <?php endforeach; ?>

        <div class="panel">
            <h2>Agenda Pondok Mendatang</h2>
            <?php if (empty($upcoming)): ?><p class="empty-state">Belum ada agenda.</p><?php endif; ?>
            <?php foreach ($upcoming as $ev): ?>
                <div class="agenda-item"><div class="date"><?= date('d M Y', strtotime($ev['start_date'])) ?></div><strong><?= h($ev['title']) ?></strong></div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
