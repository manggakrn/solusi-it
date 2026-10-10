<?php
session_start();
if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit(); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config.php';
    $stmt = $pdo->prepare("SELECT * FROM tb_users WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($u && password_verify($_POST['password'], $u['password'])) {
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['username'] = $u['username'];
        $_SESSION['nama'] = $u['nama'];
        $_SESSION['role'] = $u['role'];
        header('Location: index.php'); exit();
    }
    $error = 'Username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - OpsVault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body{background:#f1f5f9;font-family:'Inter',sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;}
        .login-card{background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:2.5rem;width:100%;max-width:420px;box-shadow:0 4px 24px rgba(0,0,0,.06);}
        .brand-logo{font-size:1.4rem;font-weight:800;color:#1e293b;display:flex;align-items:center;gap:.6rem;margin-bottom:2rem;}
        .form-control{border-radius:10px;border:1px solid #e2e8f0;padding:.7rem 1rem;}
        .form-control:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.1);}
        .btn-login{background:#2563eb;color:#fff;border-radius:10px;padding:.75rem;font-weight:700;width:100%;border:none;font-size:1rem;cursor:pointer;}
        .btn-login:hover{background:#1d4ed8;}
    </style>
</head>
<body>
<div class="login-card">
    <div class="brand-logo">
        <div class="bg-primary text-white rounded-3 p-2"><i class="fa-solid fa-shield-halved"></i></div>
        Ops<span class="text-primary">Vault</span>
    </div>
    <h5 class="fw-bold mb-1">Selamat Datang</h5>
    <p class="text-muted small mb-4">Silakan login untuk melanjutkan</p>
    <?php if ($error): ?>
        <div class="alert alert-danger rounded-3 small py-2"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label class="fw-semibold small text-muted d-block mb-1">USERNAME</label>
            <input type="text" name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-4">
            <label class="fw-semibold small text-muted d-block mb-1">PASSWORD</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn-login">Masuk</button>
    </form>
</div>
</body>
</html>