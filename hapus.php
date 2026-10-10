<?php
require_once 'auth.php';
require_once 'config.php';
if (isset($_GET['id'])) {
    // Ambil path file dulu sebelum dihapus
    $stmt = $pdo->prepare("SELECT file_path FROM tb_troubleshoot WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $file_to_delete = $stmt->fetchColumn();

    // Hapus file fisik jika ada dan ada di folder uploads
    if ($file_to_delete && file_exists($file_to_delete) && strpos($file_to_delete, 'uploads/') === 0) {
        unlink($file_to_delete);
    }

    // Hapus record di database
    $is_admin = ($_SESSION['role'] ?? '') === 'admin';
    if ($is_admin) {
        $stmt = $pdo->prepare("DELETE FROM tb_troubleshoot WHERE id = ?");
        $stmt->execute([$_GET['id']]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM tb_troubleshoot WHERE id = ? AND user_id = ?");
        $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    }
}
header("Location: index.php");
exit();
?>
