<?php
/**
 * HISADA — Halaman Login
 * Satu file utuh: PHP (autentikasi) + HTML + CSS + JS.
 *
 * Sebelum dipakai, ubah 4 konstanta DB di bawah sesuai hosting kamu.
 * Login mendukung dua jalur (sesuai keputusan Pembina):
 *   - Staf/Ustadz/Pengurus  -> masukkan EMAIL (nanti diganti Google SSO domain @daarululuumlido.com)
 *   - Wali Santri           -> masukkan NOMOR WHATSAPP yang terdaftar
 * Password demo untuk seluruh akun contoh di hisada_database.sql: hisada123
 */

// ------------------------------------------------------------------
// KONFIGURASI DATABASE — GANTI SESUAI HOSTING
// ------------------------------------------------------------------
const DB_HOST = 'localhost';
const DB_NAME = 'hisada_db';
const DB_USER = 'root';
const DB_PASS = '';

// PHP 8.1+ membuat mysqli melempar exception secara default alih-alih
// mengembalikan false pada error. Baris ini mengembalikan ke perilaku lama
// (return false + property ->error) supaya blok try/catch & pengecekan
// connect_errno di bawah benar-benar tertangkap, bukan jadi HTTP 500 mentah.
mysqli_report(MYSQLI_REPORT_OFF);

session_start();

// Kalau sudah login, langsung lempar ke dashboard
if (!empty($_SESSION['uid'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password   = (string) ($_POST['password'] ?? '');

    if ($identifier === '' || $password === '') {
        $error = 'Email/No. WhatsApp dan kata sandi wajib diisi.';
    } else {
        $mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($mysqli->connect_errno) {
            $error = 'Tidak bisa terhubung ke database (' . $mysqli->connect_error . '). Periksa konfigurasi DB_HOST/DB_NAME/DB_USER/DB_PASS.';
        } else {
            $mysqli->set_charset('utf8mb4');

            $stmt = $mysqli->prepare(
                'SELECT id, name, password, photo, family_id
                 FROM users
                 WHERE (email = ? OR phone = ?) AND is_active = 1
                 LIMIT 1'
            );

            if (!$stmt) {
                // Query gagal disiapkan — hampir selalu berarti tabel `users` belum
                // ada, artinya hisada_database.sql belum diimpor ke database ini.
                $error = 'Query gagal: ' . $mysqli->error . '. Pastikan hisada_database.sql sudah diimpor ke database "' . DB_NAME . '".';
                $stmt = null;
            }

            $user = null;
            if ($stmt) {
                $stmt->bind_param('ss', $identifier, $identifier);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $stmt->close();
            }

            if ($stmt === null && $error !== '') {
                // sudah ada pesan error dari blok prepare() di atas, jangan ditimpa
            } elseif (!$user || !password_verify($password, $user['password'] ?? '')) {
                $error = 'Kredensial tidak cocok. Periksa kembali email/nomor WA dan kata sandi.';
            } else {
                // Ambil daftar role yang SEDANG AKTIF (mendukung jabatan bergilir/time-bound)
                $stmtRole = $mysqli->prepare(
                    "SELECT r.name
                     FROM role_assignments ra
                     JOIN roles r ON r.id = ra.role_id
                     WHERE ra.user_id = ?
                       AND ra.mulai_berlaku <= CURDATE()
                       AND (ra.selesai_berlaku IS NULL OR ra.selesai_berlaku >= CURDATE())"
                );
                $stmtRole->bind_param('i', $user['id']);
                $stmtRole->execute();
                $roleResult = $stmtRole->get_result();
                $roles = [];
                while ($row = $roleResult->fetch_assoc()) {
                    $roles[] = $row['name'];
                }
                $stmtRole->close();

                if (empty($roles)) {
                    $error = 'Akun ini tidak memiliki jabatan/role aktif. Hubungi Admin.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['uid']       = $user['id'];
                    $_SESSION['name']      = $user['name'];
                    $_SESSION['photo']     = $user['photo'];
                    $_SESSION['roles']     = $roles;
                    $_SESSION['family_id'] = $user['family_id'];

                    header('Location: dashboard.php');
                    $mysqli->close();
                    exit;
                }
            }
            $mysqli->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk — HISADA · Daarul 'Uluum Lido</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root{
        --ink:#16241c;
        --emerald-900:#0b3d2e;
        --emerald-800:#0f4a37;
        --emerald-700:#166247;
        --gold:#c9a13b;
        --gold-soft:#e6cf8f;
        --cream:#f7f4ec;
        --line:#e3ddc9;
        --red:#b3432b;
    }
    *{box-sizing:border-box;}
    body{
        margin:0;
        font-family:'Inter',sans-serif;
        color:var(--ink);
        background:var(--cream);
        min-height:100vh;
        display:flex;
    }
    .stage{
        display:flex;
        width:100%;
        min-height:100vh;
    }

    /* ---------- Panel Kiri: Identitas ---------- */
    .brand-panel{
        position:relative;
        flex:1.1;
        background:radial-gradient(circle at 20% 20%, var(--emerald-700), var(--emerald-900) 70%);
        color:#f2ede0;
        padding:64px 56px;
        display:flex;
        flex-direction:column;
        justify-content:space-between;
        overflow:hidden;
    }
    .brand-panel::before{
        content:"";
        position:absolute;
        inset:0;
        opacity:.14;
        background-image:
            repeating-linear-gradient(45deg, transparent 0 22px, var(--gold) 22px 23px),
            repeating-linear-gradient(-45deg, transparent 0 22px, var(--gold) 22px 23px);
        pointer-events:none;
    }
    .brand-top, .brand-bottom{position:relative; z-index:1;}
    .brand-mark{
        display:flex;
        align-items:center;
        gap:14px;
    }
    .brand-mark .seal{
        width:52px;height:52px;border-radius:14px;
        background:rgba(242,237,224,.1);
        border:1px solid rgba(242,237,224,.35);
        display:flex;align-items:center;justify-content:center;
        font-family:'Fraunces',serif;font-weight:600;font-size:22px;color:var(--gold-soft);
    }
    .brand-mark .txt small{display:block;font-size:11px;letter-spacing:.06em;color:var(--gold-soft);opacity:.85;margin-bottom:2px;}
    .brand-mark .txt strong{font-family:'Fraunces',serif;font-size:19px;font-weight:600;}

    .brand-headline{
        font-family:'Fraunces',serif;
        font-weight:500;
        font-size:clamp(28px,3.6vw,42px);
        line-height:1.18;
        max-width:460px;
        margin-top:64px;
    }
    .brand-headline em{color:var(--gold-soft); font-style:normal;}
    .brand-sub{
        margin-top:18px;
        max-width:420px;
        font-size:14.5px;
        line-height:1.7;
        color:#d9e4dd;
    }
    .brand-quote{
        font-size:13px;
        color:#bcd0c5;
        border-top:1px solid rgba(242,237,224,.2);
        padding-top:20px;
        max-width:420px;
    }

    /* ---------- Panel Kanan: Form ---------- */
    .form-panel{
        flex:1;
        display:flex;
        align-items:center;
        justify-content:center;
        padding:40px 32px;
    }
    .form-card{
        width:100%;
        max-width:380px;
    }
    .form-card h1{
        font-family:'Fraunces',serif;
        font-weight:600;
        font-size:26px;
        margin:0 0 6px;
    }
    .form-card p.lead{
        margin:0 0 32px;
        font-size:13.5px;
        color:#5b6b60;
    }

    .field{margin-bottom:18px;}
    .field label{
        display:block;
        font-size:12px;
        font-weight:600;
        color:#3d4a41;
        margin-bottom:6px;
    }
    .field .control{
        position:relative;
    }
    .field input{
        width:100%;
        padding:12px 14px;
        border:1.4px solid var(--line);
        border-radius:10px;
        font-size:14px;
        font-family:'Inter',sans-serif;
        background:#fff;
        transition:border-color .15s;
    }
    .field input:focus{
        outline:none;
        border-color:var(--emerald-700);
    }
    .toggle-pass{
        position:absolute;
        right:12px; top:50%; transform:translateY(-50%);
        background:none;border:none;cursor:pointer;
        font-size:11.5px; font-weight:600; color:var(--emerald-700);
        padding:4px;
    }

    .btn-submit{
        width:100%;
        padding:13px;
        margin-top:6px;
        border:none;
        border-radius:10px;
        background:var(--emerald-900);
        color:#f4efe1;
        font-weight:600;
        font-size:14px;
        letter-spacing:.01em;
        cursor:pointer;
        transition:background .15s, transform .1s;
    }
    .btn-submit:hover{background:var(--emerald-700);}
    .btn-submit:active{transform:scale(.99);}
    .btn-submit[disabled]{opacity:.6;cursor:not-allowed;}

    .alert{
        background:#fbeae5;
        border:1px solid #e6b7a8;
        color:var(--red);
        font-size:13px;
        padding:11px 14px;
        border-radius:9px;
        margin-bottom:20px;
    }

    .hint-box{
        margin-top:28px;
        padding:14px 16px;
        border:1px dashed var(--line);
        border-radius:10px;
        font-size:12px;
        color:#6b7a70;
        line-height:1.6;
    }
    .hint-box code{
        background:#efe9d6;
        padding:1px 5px;
        border-radius:4px;
        font-size:11.5px;
    }

    .footnote{
        margin-top:26px;
        font-size:11.5px;
        color:#8a9690;
        text-align:center;
    }

    @media (max-width:860px){
        .brand-panel{display:none;}
        .form-panel{padding:32px 20px;}
    }
</style>
</head>
<body>
<div class="stage">

    <!-- PANEL KIRI -->
    <section class="brand-panel">
        <div class="brand-top">
            <div class="brand-mark">
                <div class="seal">DU</div>
                <div class="txt">
                    <small>PONDOK PESANTREN</small>
                    <strong>Daarul 'Uluum Lido</strong>
                </div>
            </div>
            <h1 class="brand-headline">Satu sistem untuk<br><em>seluruh data santri</em>,<br>dari kamar sampai mahkamah.</h1>
            <p class="brand-sub">
                HISADA merangkum absensi, kesehatan, kedisiplinan, prestasi, perizinan,
                dan korespondensi ke dalam satu pintu masuk — supaya pengurus, asatidz,
                dan wali santri melihat data yang sama, saat itu juga.
            </p>
        </div>
        <p class="brand-quote">Himpunan Santri Daarul 'Uluum Lido (HISADA)</p>
    </section>

    <!-- PANEL KANAN -->
    <section class="form-panel">
        <div class="form-card">
            <h1>Masuk ke HISADA</h1>
            <p class="lead">Gunakan email resmi (Asatidz/Pengurus) atau nomor WhatsApp terdaftar (Wali Santri).</p>

            <?php if ($error): ?>
                <div class="alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" id="loginForm" autocomplete="off">
                <div class="field">
                    <label for="identifier">Email atau No. WhatsApp</label>
                    <div class="control">
                        <input type="text" id="identifier" name="identifier" placeholder="nama@daarululuumlido.com atau 08xxxxxxxxxx" required
                               value="<?= htmlspecialchars($_POST['identifier'] ?? '') ?>">
                    </div>
                </div>
                <div class="field">
                    <label for="password">Kata Sandi</label>
                    <div class="control">
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                        <button type="button" class="toggle-pass" id="togglePass">LIHAT</button>
                    </div>
                </div>
                <button type="submit" class="btn-submit" id="submitBtn">Masuk ke Dashboard</button>
            </form>

            <div class="hint-box">
                Akun contoh (password semua: <code>hisada123</code>)<br>
                Admin — <code>admin@daarululuumlido.com</code><br>
                Sekretaris — <code>sekretaris@daarululuumlido.com</code><br>
                Wali Santri — <code>081234567890</code>
            </div>

            <p class="footnote">© <?= date('Y') ?> HISADA · Daarul 'Uluum Lido</p>
        </div>
    </section>

</div>

<script>
    // Tampilkan/sembunyikan kata sandi
    document.getElementById('togglePass').addEventListener('click', function () {
        const input = document.getElementById('password');
        const isText = input.type === 'text';
        input.type = isText ? 'password' : 'text';
        this.textContent = isText ? 'LIHAT' : 'SEMBUNYIKAN';
    });

    // Cegah double-submit + beri umpan balik visual saat memproses
    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.textContent = 'Memeriksa kredensial…';
    });
</script>
</body>
</html>
