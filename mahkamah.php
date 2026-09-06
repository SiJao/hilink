<?php
/**
 * HISADA — Mahkamah Santri (halaman khusus Hakim)
 * Satu file utuh: PHP + HTML + CSS + JS.
 */
const DB_HOST = 'localhost';
const DB_NAME = 'cpnpmuy3608_hisada_database';
const DB_USER = 'cpnpmuy3608_hisada';
const DB_PASS = 'Dulido1996';

mysqli_report(MYSQLI_REPORT_OFF);
session_start();
if (empty($_SESSION['uid'])) { header('Location: index.php'); exit; }

$ROLES = $_SESSION['roles'] ?? [];
if (count(array_intersect(['admin','hakim'], $ROLES)) === 0) { header('Location: dashboard.php'); exit; }

$mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_errno) { die('Tidak bisa terhubung ke database (' . $mysqli->connect_error . ').'); }
$mysqli->set_charset('utf8mb4');

function h(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function safePrepare(mysqli $mysqli, string $sql): mysqli_stmt {
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { die('Query gagal disiapkan: ' . h($mysqli->error)); }
    return $stmt;
}

$UID = (int) $_SESSION['uid'];
$NAME = $_SESSION['name'];

$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'judge_violation':
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
                $stmt->execute(); $stmt->close();
                $flash = 'Vonis & hukuman tersimpan.';
                break;
            case 'pemutihan_violation':
                $violationId = (int) $_POST['violation_id'];
                $reason = trim($_POST['revocation_reason'] ?? '');
                if ($reason === '') throw new Exception('Alasan pemutihan wajib diisi.');
                $stmt = safePrepare($mysqli, 'UPDATE violations SET verdict="pemutihan", revocation_reason=?, deleted_at=NOW() WHERE id=?');
                $stmt->bind_param('si', $reason, $violationId);
                $stmt->execute(); $stmt->close();
                $flash = 'Pelanggaran diputihkan (riwayat tetap tersimpan).';
                break;
            case 'logout':
                session_destroy(); header('Location: index.php'); exit;
            default:
                throw new Exception('Aksi tidak dikenal.');
        }
    } catch (Exception $e) {
        $flash = 'GAGAL: ' . $e->getMessage();
    }
    header('Location: mahkamah.php?msg=' . urlencode($flash ?? '') . (isset($_GET['category_id']) ? '&category_id=' . (int)$_GET['category_id'] : ''));
    exit;
}
if (isset($_GET['msg']) && $_GET['msg'] !== '') { $flash = $_GET['msg']; }

$categories = $mysqli->query('SELECT * FROM violation_categories ORDER BY name')->fetch_all(MYSQLI_ASSOC);
$punishmentCatalog = $mysqli->query('SELECT * FROM punishments ORDER BY FIELD(severity_hint,"ringan","sedang","berat")')->fetch_all(MYSQLI_ASSOC);
$filterCat = $_GET['category_id'] ?? '';
$sql = "SELECT v.*, s.name student_name, s.nis, vc.name category_name FROM violations v
        JOIN students s ON s.id = v.student_id JOIN violation_categories vc ON vc.id = v.category_id
        WHERE v.deleted_at IS NULL";
$params = []; $types = '';
if ($filterCat !== '') { $sql .= " AND v.category_id=?"; $params[] = $filterCat; $types = 'i'; }
$sql .= " ORDER BY (v.verdict='proses') DESC, v.created_at DESC";
$stmt = safePrepare($mysqli, $sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$violations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mahkamah Santri — HISADA</title>
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
    .flash{ padding:12px 16px; border-radius:9px; margin-bottom:20px; font-size:13px; background:#e8f3ec; border:1px solid #bfdccb; color:var(--emerald-700); }
    .flash.gagal{background:#fbeae5; border-color:#e6b7a8; color:var(--red);}
    .panel{ background:var(--paper); border:1px solid var(--line); border-radius:14px; padding:22px; margin-bottom:20px; }
    .toolbar{margin-bottom:16px;}
    .field-input{ padding:9px 12px; border:1.4px solid var(--line); border-radius:9px; font-size:13px; font-family:inherit; }
    .btn{display:inline-block; padding:9px 18px; border-radius:9px; border:none; background:var(--emerald-900); color:#f4efe1; font-size:13px; font-weight:600; cursor:pointer;}
    .btn.small{padding:5px 12px; font-size:11.5px;} .btn.secondary{background:none; border:1px solid var(--line); color:var(--ink);}
    .status-pill{font-size:10.5px; padding:2px 9px; border-radius:20px; font-weight:600;}
    .status-pill.proses{background:#fbeee0; color:var(--amber);}
    .status-pill.divonis{background:#e2f0e6; color:var(--emerald-700);}
    .status-pill.pemutihan{background:#fbeae5; color:var(--red);}
    .empty-state{padding:20px; text-align:center; color:var(--muted); font-size:13px;}
    .modal-backdrop{ display:none; position:fixed; inset:0; background:rgba(11,61,46,.55); align-items:center; justify-content:center; z-index:50; padding:20px; }
    .modal-backdrop.show{display:flex;}
    .simple-modal{ width:420px; max-width:92vw; background:var(--paper); border-radius:16px; padding:24px; position:relative; }
    .simple-modal h3{font-family:'Fraunces',serif; margin:0 0 14px;}
    .simple-modal .close-modal{position:absolute; top:14px; right:16px; background:none; border:none; color:var(--muted); font-size:18px; cursor:pointer;}
    .field-group{margin-bottom:14px;} .field-group label{display:block; font-size:12px; font-weight:600; margin-bottom:5px;}
    .checklist{display:flex; flex-direction:column; gap:8px; margin-bottom:12px;}
    .checklist label{display:flex; align-items:center; gap:8px; font-size:13px; font-weight:400;}
    textarea.field-input, input.field-input{width:100%; font-family:inherit;}
    textarea.field-input{min-height:70px; resize:vertical;}
</style>
</head>
<body>
    <div class="topbar">
        <div class="brand"><div class="seal">DU</div><div><h1>Mahkamah Santri — HISADA</h1><span><?= h($NAME) ?> · Hakim</span></div></div>
        <form method="POST"><input type="hidden" name="action" value="logout"><button class="btn-logout" type="submit">Keluar</button></form>
    </div>
    <div class="content">
        <?php if ($flash): ?><div class="flash <?= str_starts_with($flash,'GAGAL') ? 'gagal' : '' ?>"><?= h($flash) ?></div><?php endif; ?>

        <form class="toolbar" method="GET">
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
                    <?php if ($v['verdict'] === 'proses'): ?>
                        <div style="display:flex; gap:8px; margin-top:10px; flex-wrap:wrap;">
                            <button type="button" class="btn small" onclick="openJudgeModal(<?= $v['id'] ?>, '<?= h($v['student_name']) ?>')">Vonis & Hukuman</button>
                            <button type="button" class="btn small secondary" onclick="openPemutihanModal(<?= $v['id'] ?>)">Putihkan</button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="modal-backdrop" id="judgeModal">
            <div class="simple-modal">
                <button class="close-modal" onclick="closeModal('judgeModal')">&times;</button>
                <h3>Vonis & Hukuman — <span id="judgeStudentName"></span></h3>
                <form method="POST">
                    <input type="hidden" name="action" value="judge_violation">
                    <input type="hidden" name="violation_id" id="judgeViolationId">
                    <div class="field-group"><label>Tingkat</label>
                        <select name="severity" class="field-input"><option value="ringan">Ringan</option><option value="sedang">Sedang</option><option value="berat">Berat</option></select>
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

        <div class="modal-backdrop" id="pemutihanModal">
            <div class="simple-modal">
                <button class="close-modal" onclick="closeModal('pemutihanModal')">&times;</button>
                <h3>Pemutihan Pelanggaran</h3>
                <form method="POST" onsubmit="return confirm('Yakin memutihkan pelanggaran ini?');">
                    <input type="hidden" name="action" value="pemutihan_violation">
                    <input type="hidden" name="violation_id" id="pemutihanViolationId">
                    <div class="field-group"><label>Alasan Pemutihan</label><textarea name="revocation_reason" class="field-input" required></textarea></div>
                    <button class="btn" type="submit">Putihkan</button>
                </form>
            </div>
        </div>
    </div>

<script>
    function closeModal(id) { document.getElementById(id).classList.remove('show'); }
    function openModalById(id) { document.getElementById(id).classList.add('show'); }
    function openJudgeModal(id, name) { document.getElementById('judgeViolationId').value = id; document.getElementById('judgeStudentName').textContent = name; openModalById('judgeModal'); }
    function openPemutihanModal(id) { document.getElementById('pemutihanViolationId').value = id; openModalById('pemutihanModal'); }
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') document.querySelectorAll('.modal-backdrop.show').forEach(m => m.classList.remove('show')); });
    document.querySelectorAll('.modal-backdrop').forEach(function (b) { b.addEventListener('click', function (e) { if (e.target === b) b.classList.remove('show'); }); });
</script>
</body>
</html>
