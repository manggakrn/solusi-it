<?php
require_once 'auth.php'; 
require_once 'config.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// Judul Dinamis
$pageTitle = !empty($kategori_filter) ? htmlspecialchars($kategori_filter) : "Semua Masalah";

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$params = [];
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
$query = "SELECT * FROM tb_troubleshoot WHERE 1=1";
if (!$is_admin) { $query .= " AND user_id = ?"; array_push($params, $_SESSION['user_id']); }
if (!empty($search)) {
    $query .= " AND (kategori LIKE ? OR judul_kendala LIKE ? OR detail_error LIKE ? OR cara_solusi LIKE ?)";
    $search_param = "%$search%";
    array_push($params, $search_param, $search_param, $search_param, $search_param);
}

if (!empty($kategori_filter)) {
    $query .= " AND kategori = ?";
    array_push($params, $kategori_filter);
}

$count_query = "SELECT COUNT(*) FROM tb_troubleshoot WHERE 1=1" . str_replace("SELECT * FROM tb_troubleshoot WHERE 1=1", "", $query);
$stmt_count = $pdo->prepare($count_query);
$stmt_count->execute($params);
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $per_page);

$query .= " ORDER BY tanggal DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();

$daftar_kategori = ['Server', 'Database', 'Aplikasi', 'Jaringan', 'Hardware', 'Recording', 'cloud', 'Security', 'Tugas-pagi'];

function getKategoriStyle($kat) {
    $map = [
        'Server' => ['bg' => '#e0e7ff', 'text' => '#4338ca', 'icon' => 'fa-server'],
        'Database' => ['bg' => '#f3e8ff', 'text' => '#6b21a8', 'icon' => 'fa-database'],
        'Aplikasi' => ['bg' => '#ecfeff', 'text' => '#0e7490', 'icon' => 'fa-laptop-code'],
        'Jaringan' => ['bg' => '#dcfce7', 'text' => '#15803d', 'icon' => 'fa-wifi'],
        'Hardware' => ['bg' => '#ffedd5', 'text' => '#c2410c', 'icon' => 'fa-screwdriver-wrench'],
        'Recording' => ['bg' => '#fee2e2', 'text' => '#b91c1c', 'icon' => 'fa-microphone'],
        'cloud' => ['bg' => '#e0f2fe', 'text' => '#0369a1', 'icon' => 'fa-cloud'],
        'Security' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'icon' => 'fa-shield-halved'],
        'Tugas-pagi' => ['bg' => '#fef9c3', 'text' => '#854d0e', 'icon' => 'fa-sun']
    ];
    return isset($map[$kat]) ? $map[$kat] : ['bg' => '#e2e8f0', 'text' => '#475569', 'icon' => 'fa-tag'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arsip Solusi IT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary: #2563eb; }
        body { 
            /* Background Halus: Gradasi tipis dari biru muda ke putih */
            background: linear-gradient(135deg, #f1f5f9 0%, #ffffff 100%);
            background-attachment: fixed;
            color: #1e293b; 
            font-family: 'Inter', sans-serif; 
            min-height: 100vh;
        }
        .sidebar { width: 260px; height: 100vh; position: fixed; background: #fff; border-right: 1px solid #e2e8f0; padding: 2rem; z-index: 1000; }
        .main-content { margin-left: 260px; padding: 2.5rem; }
        .nav-link-custom { color: #64748b; padding: 0.8rem 1rem; border-radius: 8px; display: block; text-decoration: none; font-weight: 500; font-size: 0.9rem; transition: 0.2s; }
        .nav-link-custom:hover, .nav-link-custom.active { background: #eff6ff; color: var(--primary); }
        .list-item { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.2s ease; cursor: pointer; padding: 1rem; }
        .list-item:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); border-color: var(--primary); }
        .badge-kategori { font-size: 0.7rem; font-weight: 600; padding: 5px 12px; border-radius: 20px; text-transform: uppercase; }
        .brand-logo { font-size: 1.25rem; font-weight: 800; color: #1e293b; text-decoration: none; display: flex; align-items: center; gap: 0.5rem; }
        .mobile-topbar { display: none; }
        .sidebar-backdrop { display: none; }
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); transition: transform .25s ease; z-index: 1040; box-shadow: 0 0 40px rgba(0,0,0,.15); }
            .sidebar.show { transform: none; }
            .main-content { margin-left: 0; padding: 1.25rem; padding-top: 4.75rem; }
            .mobile-topbar { display: flex; align-items: center; gap: .75rem; position: fixed; top: 0; left: 0; right: 0; height: 3.75rem; background: #fff; border-bottom: 1px solid #e2e8f0; padding: 0 1rem; z-index: 1010; }
            .sidebar-backdrop.show { display: block; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 1000; }
            .list-item { flex-wrap: wrap; }
            .list-item .badge-col { width: auto !important; }
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

<div class="sidebar d-flex flex-column">
    <div class="brand-wrapper mb-4">
        <a class="brand-logo" href="index.php">
            <div class="bg-primary text-white rounded-3 p-2"><i class="fa-solid fa-shield-halved"></i></div>
            Ops<span class="text-primary">Vault</span>
        </a>
    </div>
    <nav class="nav flex-column flex-grow-1">
        <a class="nav-link-custom <?= empty($kategori_filter) ? 'active' : '' ?>" href="index.php"><i class="fa-solid fa-list me-2"></i> Semua Data</a>
        <?php foreach($daftar_kategori as $kat): 
            $style = getKategoriStyle($kat);
        ?>
            <a class="nav-link-custom <?= $kategori_filter == $kat ? 'active' : '' ?>" href="index.php?kategori=<?= urlencode($kat) ?>">
                <i class="fa-solid <?= $style['icon'] ?> me-2"></i> <?= $kat ?>
            </a>
        <?php endforeach; ?>
    <?php if ($is_admin): ?><a class="nav-link-custom" href="users.php"><i class="fa-solid fa-users-gear me-2"></i> Manajemen User</a><?php endif; ?></nav>
    <div style="border-top:1px solid #e2e8f0;padding-top:1rem;margin-top:0.5rem;margin-bottom:0.75rem;">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:0.75rem;padding:0.6rem 1rem;background:#f8fafc;border-radius:8px;">
        <i class="fa-solid fa-circle-user" style="color:#2563eb;font-size:1.1rem;"></i>
        <span style="font-size:.85rem;font-weight:600;color:#334155;"><?= htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? 'User') ?></span>
    </div>
    <a href="logout.php" onclick="return confirm('Yakin ingin logout?')" class="btn btn-outline-danger w-100 rounded-3 fw-semibold" style="font-size:.85rem;">
        <i class="fa-solid fa-right-from-bracket me-2"></i>Logout
    </a>
</div><a href="tambah.php" class="btn btn-primary w-100 fw-bold rounded-3 py-2"><i class="fa-solid fa-plus me-2"></i>Tambah Solusi</a></div></div>

<div class="main-content">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <h3 class="fw-bold"><?= $pageTitle ?></h3>
        <form method="GET" class="d-flex w-100" style="max-width:420px;">
            <input type="text" name="search" class="form-control rounded-3" placeholder="Cari..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-light border ms-2">Cari</button>
        </form>
    </div>

    <?php foreach ($logs as $row): 
        $base64Data = base64_encode(json_encode($row));
        $style = getKategoriStyle($row['kategori']);
    ?>
        <div class="list-item mb-2 d-flex align-items-center">
            <div class="badge-col" style="width: 130px;"><span class="badge badge-kategori" style="background:<?= $style['bg'] ?>; color:<?= $style['text'] ?>"><i class="fa-solid <?= $style['icon'] ?> me-1"></i><?= $row['kategori'] ?></span></div>
            <div class="flex-grow-1 px-3">
                <div class="fw-semibold text-dark"><?= htmlspecialchars($row['judul_kendala']) ?></div>
                <small class="text-muted"><i class="fa-regular fa-calendar me-1"></i> <?= date('d M Y', strtotime($row['tanggal'])) ?></small>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary btn-trigger-view" data-log="<?= $base64Data ?>"><i class="fa-solid fa-eye"></i></button>
                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-light border"><i class="fa-solid fa-pen"></i></a>
                <a href="hapus.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Hapus?')"><i class="fa-solid fa-trash"></i></a>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if ($total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link rounded-3 mx-1 border-0 shadow-sm" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&kategori=<?= urlencode($kategori_filter) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    <?php if ($is_admin): ?><a class="nav-link-custom" href="users.php"><i class="fa-solid fa-users-gear me-2"></i> Manajemen User</a><?php endif; ?></nav>
    <?php endif; ?>
</div>

<!-- Modal -->
<div class="modal fade" id="viewDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 p-4"><h5 class="modal-title fw-bold" id="modalTitle"></h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body bg-light rounded-4 mx-3">
                <div class="mb-3"><strong>Detail Error:</strong><pre id="modalError" class="p-3 bg-white border rounded text-danger"></pre></div>
                <div><strong>Solusi:</strong><div id="modalSolusi" class="p-3 bg-white border rounded"></div></div>
                <div id="modalPdfArea" class="mt-3" style="display:none;">
                    <strong id="modalFileLabel">Lampiran:</strong>
                    <a id="modalPdfLink" href="#" target="_blank" class="btn btn-sm btn-info text-white"><i id="modalFileIcon" class="fa-solid fa-file me-1"></i><span id="modalFileText">Lihat File</span></a>
                </div>
            </div>
            <div class="modal-footer border-0 p-4"><button class="btn btn-primary w-100 fw-bold" onclick="unduhSolusi(this)"><i class="fa-solid fa-file-pdf me-2"></i>Unduh Solusi (PDF)</button></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2pdf.js@0.10.2/dist/html2pdf.bundle.min.js"></script>
<script>
    document.querySelectorAll('.btn-trigger-view').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = JSON.parse(atob(this.getAttribute('data-log')));
            document.getElementById('modalTitle').innerText = data.judul_kendala;
            document.getElementById('modalError').innerText = data.detail_error;
            document.getElementById('modalSolusi').innerHTML = data.cara_solusi;
            // Fix: cegah link dalam konten navigasi keluar halaman
            document.querySelectorAll('#modalSolusi a').forEach(a => {
                a.setAttribute('target', '_blank');
                a.setAttribute('rel', 'noopener noreferrer');
            });
            // Fix: gambar responsive & tidak navigasi keluar
            document.querySelectorAll('#modalSolusi img').forEach(img => {
                img.style.maxWidth = '100%';
                img.style.height = 'auto';
                img.style.display = 'block';
                img.style.borderRadius = '8px';
                img.style.marginTop = '8px';
                img.style.cursor = 'zoom-in';
                img.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    window.open(this.src, '_blank');
                });
            });
            const pdfArea = document.getElementById('modalPdfArea');
            if (data.file_path) {
                pdfArea.style.display = 'block';
                const link = document.getElementById('modalPdfLink');
                link.href = data.file_path;
                const ext = (data.file_path.split('.').pop() || '').toLowerCase();
                // [ikon, teks tombol, paksa-unduh?] — browser tak bisa preview doc/xls/zip → unduh
                const map = {
                    pdf:  ['fa-file-pdf',    'Lihat PDF',    false],
                    jpg:  ['fa-file-image',  'Lihat Gambar', false],
                    jpeg: ['fa-file-image',  'Lihat Gambar', false],
                    png:  ['fa-file-image',  'Lihat Gambar', false],
                    gif:  ['fa-file-image',  'Lihat Gambar', false],
                    webp: ['fa-file-image',  'Lihat Gambar', false],
                    txt:  ['fa-file-lines',  'Lihat Teks',   false],
                    doc:  ['fa-file-word',   'Unduh Word',   true],
                    docx: ['fa-file-word',   'Unduh Word',   true],
                    xls:  ['fa-file-excel',  'Unduh Excel',  true],
                    xlsx: ['fa-file-excel',  'Unduh Excel',  true],
                    zip:  ['fa-file-zipper', 'Unduh Arsip',  true]
                };
                const [icon, text, dl] = map[ext] || ['fa-file', 'Unduh File', true];
                document.getElementById('modalFileIcon').className = 'fa-solid ' + icon + ' me-1';
                document.getElementById('modalFileText').innerText = text;
                document.getElementById('modalFileLabel').innerText = 'Lampiran (' + (ext ? ext.toUpperCase() : 'FILE') + '):';
                if (dl) { link.setAttribute('download', ''); } else { link.removeAttribute('download'); }
            } else {
                pdfArea.style.display = 'none';
            }
            new bootstrap.Modal(document.getElementById('viewDetailModal')).show();
        });
    });
    function escapeHtml(s){ return (s||'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
    async function unduhSolusi(btn) {
        const title = (document.getElementById('modalTitle').innerText || 'Solusi').trim();
        const errorText = (document.getElementById('modalError').innerText || '').trim();
        const solusiHtml = document.getElementById('modalSolusi').innerHTML;

        // Kontainer luar disembunyikan di luar layar; konten anak tetap di alur normal
        // agar tingginya terbaca html2canvas (kalau elemennya sendiri position:fixed, tinggi = 0).
        const holder = document.createElement('div');
        holder.style.cssText = 'position:fixed;left:-10000px;top:0;';
        const wrap = document.createElement('div');
        wrap.style.cssText = 'width:760px;padding:24px;background:#fff;color:#1e293b;font-family:Inter,Arial,sans-serif;';
        wrap.innerHTML =
            '<h2 style="font-size:20px;font-weight:700;margin:0 0 4px;">' + escapeHtml(title) + '</h2>' +
            '<hr style="border:none;border-top:2px solid #2563eb;margin:8px 0 16px;">' +
            (errorText ? '<h3 style="font-size:14px;color:#b91c1c;margin:0 0 6px;">Detail Error</h3>' +
                '<pre style="white-space:pre-wrap;word-wrap:break-word;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px;font-family:monospace;font-size:12px;color:#b91c1c;margin:0 0 16px;">' + escapeHtml(errorText) + '</pre>' : '') +
            '<h3 style="font-size:14px;color:#2563eb;margin:0 0 6px;">Solusi</h3>' +
            '<div style="font-size:13px;line-height:1.6;">' + solusiHtml + '</div>';

        // Pastikan gambar (base64 & uploads/) muat di halaman
        wrap.querySelectorAll('img').forEach(img => { img.style.maxWidth = '100%'; img.style.height = 'auto'; img.removeAttribute('width'); img.removeAttribute('height'); });
        holder.appendChild(wrap);
        document.body.appendChild(holder);

        // Tunggu semua gambar selesai dimuat agar ikut ke-render
        await Promise.all([...wrap.querySelectorAll('img')].map(img => (img.complete && img.naturalHeight)
            ? Promise.resolve()
            : new Promise(res => { img.onload = img.onerror = res; })));

        const filename = (title.replace(/[^\w\-]+/g, '_').slice(0, 60) || 'solusi') + '.pdf';
        const opt = {
            margin: [10, 10, 10, 10],
            filename: filename,
            image: { type: 'jpeg', quality: 0.95 },
            html2canvas: { scale: 2, useCORS: true, logging: false, backgroundColor: '#ffffff' },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
            pagebreak: { mode: ['css', 'legacy'] }
        };

        const original = btn ? btn.innerHTML : null;
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menyiapkan PDF...'; }
        try {
            await html2pdf().set(opt).from(wrap).save();
        } catch (e) {
            alert('Gagal membuat PDF: ' + e.message);
        } finally {
            holder.remove();
            if (btn) { btn.disabled = false; btn.innerHTML = original; }
        }
    }
    // Toggle sidebar di layar HP
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
