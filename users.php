<?php
require_once 'auth.php';
require_once 'config.php';

// Hanya admin yang boleh akses
if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: index.php'); exit();
}

$error = '';
$success = '';

// Tambah user baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $uname = trim($_POST['username'] ?? '');
    $nama  = trim($_POST['nama'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $role  = in_array($_POST['role'], ['admin','user']) ? $_POST['role'] : 'user';

    if ($uname && $nama && strlen($pass) >= 6) {
        try {
            $stmt = $pdo->prepare("INSERT INTO tb_users (username, nama, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$uname, $nama, password_hash($pass, PASSWORD_DEFAULT), $role]);
            $success = "User <strong>$uname</strong> berhasil ditambahkan.";
        } catch (PDOException $e) {
            $error = 'Username sudah digunakan.';
        }
    } else {
        $error = 'Semua field wajib diisi. Password minimal 6 karakter.';
    }
}

// Hapus user
if (isset($_GET['hapus']) && (int)$_GET['hapus'] > 0) {
    $del_id = (int)$_GET['hapus'];
    if ($del_id !== (int)$_SESSION['user_id']) { // tidak bisa hapus diri sendiri
        $pdo->prepare("DELETE FROM tb_users WHERE id = ? AND username != 'admin'")->execute([$del_id]);
    }
    header('Location: users.php'); exit();
}

$users = $pdo->query("SELECT id, username, nama, role, created_at FROM tb_users ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - OpsVault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2563eb; }
        body { background: linear-gradient(135deg, #f1f5f9 0%, #ffffff 100%); background-attachment: fixed; color: #1e293b; font-family: 'Inter', sans-serif; min-height: 100vh; }
        .sidebar { width: 260px; height: 100vh; position: fixed; background: #fff; border-right: 1px solid #e2e8f0; padding: 2rem; z-index: 1000; display: flex; flex-direction: column; }
        .main-content { margin-left: 260px; padding: 2.5rem; }
        .nav-link-custom { color: #64748b; padding: 0.8rem 1rem; border-radius: 8px; display: block; text-decoration: none; font-weight: 500; font-size: 0.9rem; transition: 0.2s; }
        .nav-link-custom:hover, .nav-link-custom.active { background: #eff6ff; color: var(--primary); }
        .brand-logo { font-size: 1.25rem; font-weight: 800; color: #1e293b; text-decoration: none; display: flex; align-items: center; gap: 0.5rem; }
        .badge-role-admin { background: #fef3c7; color: #b45309; font-size: .72rem; padding: 3px 10px; border-radius: 20px; font-weight: 600; }
        .badge-role-user  { background: #eff6ff; color: #2563eb; font-size: .72rem; padding: 3px 10px; border-radius: 20px; font-weight: 600; }
        .card-form { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.75rem; }
        .mobile-topbar { display: none; }
        .sidebar-backdrop { display: none; }
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); transition: transform .25s ease; z-index: 1040; box-shadow: 0 0 40px rgba(0,0,0,.15); }
            .sidebar.show { transform: none; }
            .main-content { margin-left: 0; padding: 1.25rem; padding-top: 4.75rem; }
            .mobile-topbar { display: flex; align-items: center; gap: .75rem; position: fixed; top: 0; left: 0; right: 0; height: 3.75rem; background: #fff; border-bottom: 1px solid #e2e8f0; padding: 0 1rem; z-index: 1010; }
            .sidebar-backdrop.show { display: block; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 1000; }
        }
    </style>
</head>
<body>
<div class="mobile-topbar">
    <button class="btn btn-light border" id="sidebarToggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
    <a class="brand-logo" href="index.php" style="font-size:1.05rem;">
        <div class="bg-primary text-white rounded-3 p-2"><i class="fa-solid fa-shield-halved"></i></div>
        Ops<span class="text-primary">Vault</span>
    </a>
</div>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
<!-- Sidebar -->
<div class="sidebar">
    <div class="brand-wrapper mb-4">
        <a class="brand-logo" href="index.php">
            <div class="bg-primary text-white rounded-3 p-2"><i class="fa-solid fa-shield-halved"></i></div>
            Ops<span class="text-primary">Vault</span>
        </a>
    </div>
    <nav class="nav flex-column flex-grow-1">
        <a class="nav-link-custom" href="index.php"><i class="fa-solid fa-list me-2"></i> Semua Data</a>
        <a class="nav-link-custom active" href="users.php"><i class="fa-solid fa-users-gear me-2"></i> Manajemen User</a>
    </nav>
    <div style="border-top:1px solid #e2e8f0;padding-top:1rem;margin-top:0.5rem;margin-bottom:0.75rem;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:0.75rem;padding:0.6rem 1rem;background:#f8fafc;border-radius:8px;">
            <i class="fa-solid fa-circle-user" style="color:#2563eb;font-size:1.1rem;"></i>
            <span style="font-size:.85rem;font-weight:600;color:#334155;"><?= htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? 'User') ?></span>
        </div>
        <a href="logout.php" onclick="return confirm('Yakin ingin logout?')" class="btn btn-outline-danger w-100 rounded-3 fw-semibold" style="font-size:.85rem;">
            <i class="fa-solid fa-right-from-bracket me-2"></i>Logout
        </a>
    </div>
    <a href="tambah.php" class="btn btn-primary w-100 fw-bold rounded-3 py-2"><i class="fa-solid fa-plus me-2"></i>Tambah Solusi</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="fa-solid fa-users-gear me-2 text-primary"></i>Manajemen User</h3>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger rounded-3"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success rounded-3"><?= $success ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Daftar User -->
        <div class="col-lg-8">
            <div class="card-form">
                <h6 class="fw-bold mb-3">Daftar User</h6>
                <div class="table-responsive">
                <table class="table table-borderless align-middle">
                    <thead style="background:#f8fafc;border-radius:8px;">
                        <tr>
                            <th class="text-muted small fw-semibold">#</th>
                            <th class="text-muted small fw-semibold">Username</th>
                            <th class="text-muted small fw-semibold">Nama</th>
                            <th class="text-muted small fw-semibold">Role</th>
                            <th class="text-muted small fw-semibold">Dibuat</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="text-muted small"><?= $u['id'] ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= htmlspecialchars($u['nama']) ?></td>
                            <td>
                                <span class="badge-role-<?= $u['role'] ?>"><?= strtoupper($u['role']) ?></span>
                            </td>
                            <td class="text-muted small"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                            <td>
                                <?php if ($u['username'] !== 'admin' && $u['id'] != $_SESSION['user_id']): ?>
                                    <a href="?hapus=<?= $u['id'] ?>" class="btn btn-sm btn-light border text-danger"
                                       onclick="return confirm('Hapus user <?= htmlspecialchars($u['username']) ?>?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <!-- Form Tambah User -->
        <div class="col-lg-4">
            <div class="card-form">
                <h6 class="fw-bold mb-3"><i class="fa-solid fa-user-plus me-2 text-primary"></i>Tambah User Baru</h6>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Username</label>
                        <input type="text" name="username" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Password <span class="text-muted">(min. 6 karakter)</span></label>
                        <input type="password" name="password" class="form-control rounded-3" required minlength="6">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-muted">Role</label>
                        <select name="role" class="form-select rounded-3">
                            <option value="user">User — hanya lihat data sendiri</option>
                            <option value="admin">Admin — lihat semua data</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-3 fw-bold">
                        <i class="fa-solid fa-plus me-2"></i>Tambah User
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    (function(){
        const tgl = document.getElementById('sidebarToggle');
        const sb = document.querySelector('.sidebar');
        const bd = document.getElementById('sidebarBackdrop');
        function close(){ sb.classList.remove('show'); bd.classList.remove('show'); }
        if (tgl) tgl.addEventListener('click', () => { sb.classList.toggle('show'); bd.classList.toggle('show'); });
        if (bd) bd.addEventListener('click', close);
    })();
</script>
</body>
</html>
