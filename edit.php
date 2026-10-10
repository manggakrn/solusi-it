<?php
require_once 'auth.php';
require_once 'config.php';
$id = isset($_GET['id']) ? $_GET['id'] : null; 
if (!$id) { header("Location: index.php"); exit(); }
$is_admin = ($_SESSION['role'] ?? '') === 'admin';
$stmt = $pdo->prepare("SELECT * FROM tb_troubleshoot WHERE id = ?");
$stmt->execute([$id]); $data = $stmt->fetch();
if ($data && !$is_admin && $data['user_id'] != $_SESSION['user_id']) { header('Location: index.php'); exit(); }
if (!$data) { die("Data tidak ditemukan."); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file_path = $data['file_path'];
    if (!empty($_FILES['pdf_file']['name'])) {
        $allowed_exts = ['pdf', 'jpg', 'jpeg', 'png', 'docx', 'xlsx', 'txt', 'zip'];
        $ext = strtolower(pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_exts)) {
            $filename = time() . '_' . basename($_FILES['pdf_file']['name']);
            $destination = 'uploads/' . $filename;
            if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $destination)) {
                $file_path = $destination;
            }
        }
    }
    $is_admin2 = ($_SESSION['role'] ?? '') === 'admin';
    $upd_sql = $is_admin2 ? "UPDATE tb_troubleshoot SET kategori=?, judul_kendala=?, detail_error=?, cara_solusi=?, file_path=? WHERE id=?" : "UPDATE tb_troubleshoot SET kategori=?, judul_kendala=?, detail_error=?, cara_solusi=?, file_path=? WHERE id=? AND user_id=?";
    $stmt = $pdo->prepare($upd_sql);
    $upd_params = [$_POST['kategori'], $_POST['judul_kendala'], $_POST['detail_error'], $_POST['cara_solusi'], $file_path, $id];
    if (!$is_admin2) $upd_params[] = $_SESSION['user_id'];
    $stmt->execute($upd_params);
    header("Location: index.php"); exit();
}
$daftar_kategori = ['Server', 'Database', 'Aplikasi', 'Jaringan', 'Hardware', 'Recording', 'cloud', 'Security', 'Tugas-pagi'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Solusi - OpsVault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <style>
        :root { --bg-app: #f8fafc; --primary: #2563eb; }
        body { background-color: var(--bg-app); font-family: 'Inter', sans-serif; }
        .sidebar { width: 260px; height: 100vh; position: fixed; background: #fff; border-right: 1px solid #e2e8f0; padding: 2rem; z-index: 1000; }
        .main-content { margin-left: 260px; padding: 2.5rem; }
        .content-container { max-width: 900px; margin: 0 auto; }
        .form-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 2rem; }
        .brand-logo { font-size: 1.25rem; font-weight: 800; color: #1e293b; text-decoration: none; display: flex; align-items: center; gap: 0.5rem; }
        @media (max-width: 991.98px) {
            .sidebar { position: static; width: 100%; height: auto; flex-direction: row !important; align-items: center; padding: 1rem 1.25rem; border-right: none; border-bottom: 1px solid #e2e8f0; }
            .sidebar .brand-wrapper { margin-bottom: 0 !important; }
            .main-content { margin-left: 0; padding: 1.5rem 1.25rem; }
        }
    </style>
</head>
<body>

<div class="sidebar d-flex flex-column">
    <div class="brand-wrapper mb-4">
        <a class="brand-logo" href="index.php"><div class="bg-primary text-white rounded-3 p-2"><i class="fa-solid fa-shield-halved"></i></div>Ops<span class="text-primary">Vault</span></a>
    </div></div>
</div>
<div class="main-content">
    <div class="content-container">
        <div class="form-card">
            <h4 class="fw-bold mb-4">Edit Solusi</h4>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="fw-semibold small text-muted">KATEGORI</label>
                        <select name="kategori" class="form-select rounded-3" required>
                            <?php foreach($daftar_kategori as $kat): ?><option value="<?= $kat ?>" <?= $data['kategori'] == $kat ? 'selected' : '' ?>><?= $kat ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="fw-semibold small text-muted">JUDUL KENDALA</label>
                        <input type="text" name="judul_kendala" class="form-control rounded-3" value="<?= htmlspecialchars($data['judul_kendala']) ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="fw-semibold small text-muted">DETAIL ERROR</label>
                    <textarea name="detail_error" class="form-control rounded-3" rows="3" required><?= htmlspecialchars($data['detail_error']) ?></textarea>
                </div>
                <div class="mb-4">
                    <label class="fw-semibold small text-muted">LANGKAH SOLUSI</label>
                    <textarea id="cara_solusi" name="cara_solusi" required><?= $data['cara_solusi'] ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="fw-semibold small text-muted">UPLOAD PDF BARU (OPSIONAL)</label>
                    <?php if (!empty($data['file_path'])): ?>
                        <p class="small text-success">File saat ini: <a href="<?= $data['file_path'] ?>" target="_blank">Lihat PDF</a></p>
                    <?php endif; ?>
                    <input type="file" name="pdf_file" class="form-control rounded-3" accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx,.txt,.zip">
                </div>
                <button type="submit" class="btn btn-primary fw-bold px-4 py-2 rounded-3">Update Data</button>
            </form>
        </div>
    </div>
</div>
<script>
    $('#cara_solusi').summernote({
        height: 300,
        toolbar: [['insert', ['picture']], ['view', ['fullscreen', 'codeview']]],
        callbacks: { onImageUpload: function(files) {
            let data = new FormData(); data.append("file", files[0]);
            $.ajax({ url: 'upload.php', cache: false, contentType: false, processData: false, data: data, type: "post", 
                success: function(url) { $('#cara_solusi').summernote("insertImage", url); }
            });
        }}
    });
</script>
</body>
</html>
