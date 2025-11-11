<?php
require_once '../config.php';

$upload_message = '';
$upload_status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $filename = $file['name'];
    $tmp_name = $file['tmp_name'];
    $file_size = $file['size'];
    
    // VULNERABLE: Tidak ada validasi ekstensi atau MIME type
    $upload_path = 'uploads/' . $filename;
    
    if (move_uploaded_file($tmp_name, $upload_path)) {
        // Save to database
        $stmt = $pdo->prepare("INSERT INTO uploaded_files (filename, original_name, file_path, upload_type) VALUES (?, ?, ?, 'vulnerable')");
        $stmt->execute([$filename, $filename, $upload_path]);
        
        $upload_message = "File berhasil diupload tanpa validasi!";
        $upload_status = 'warning';
        
        // Check if dangerous file
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, ['php', 'phtml', 'php3', 'php4', 'php5', 'exe', 'sh', 'bat'])) {
            $upload_message .= "<br><strong style='color: #e74c3c;'>⚠️ PERINGATAN: File berbahaya terdeteksi! ($ext)</strong>";
            $upload_status = 'danger';
        }
    } else {
        $upload_message = "Upload gagal!";
        $upload_status = 'danger';
    }
}

// Fetch uploaded files
$stmt = $pdo->query("SELECT * FROM uploaded_files WHERE upload_type = 'vulnerable' ORDER BY uploaded_at DESC");
$files = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload - Vulnerable Version</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="lab-page">
            <div class="lab-header">
                <h1 class="lab-title">📤 File Upload Vulnerability - Vulnerable</h1>
                <a href="../index.php" class="btn btn-secondary">← Kembali ke Home</a>
            </div>

            <div class="info-box">
                <h4>Konsep File Upload Vulnerability</h4>
                <p>File upload yang tidak divalidasi dapat menyebabkan Remote Code Execution (RCE), webshell upload, atau backdoor. Attacker dapat mengupload file berbahaya seperti PHP shell.</p>
            </div>

            <?php if ($upload_message): ?>
                <div class="alert alert-<?= $upload_status ?>">
                    <?= $upload_message ?>
                </div>
            <?php endif; ?>

            <div class="demo-section">
                <div class="demo-box vulnerable">
                    <div class="demo-header">
                        <span style="font-size: 1.5rem;">❌</span>
                        <h3>Upload Tanpa Validasi</h3>
                    </div>

                    <div class="code-box">
                        // VULNERABLE: Tidak ada validasi!<br>
                        $filename = $_FILES['file']['name'];<br>
                        move_uploaded_file($tmp_name, 'uploads/' . $filename);
                    </div>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Pilih File:</label>
                            <input type="file" name="file" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-warning" style="width: 100%;">Upload File (Vulnerable)</button>
                    </form>

                    <div class="payload-list">
                        <p><strong>File berbahaya yang bisa diupload:</strong></p>
                        <ul>
                            <li><code>shell.php</code> - Web shell</li>
                            <li><code>backdoor.phtml</code> - PHP alternative extension</li>
                            <li><code>malware.exe</code> - Executable file</li>
                            <li><code>script.bat</code> - Batch script</li>
                        </ul>
                        <p style="color: #e74c3c; font-weight: 600; margin-top: 10px;">⚠️ Hanya untuk demo! Jangan upload file berbahaya sungguhan!</p>
                    </div>
                </div>

                <div class="demo-box" style="border-color: #e74c3c; background: #fef5f5;">
                    <div class="demo-header">
                        <span style="font-size: 1.5rem;">💣</span>
                        <h3>Dampak & Risiko</h3>
                    </div>
                    
                    <p><strong>1. Remote Code Execution (RCE)</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Upload PHP web shell → eksekusi command arbitrary</li>
                        <li>Akses penuh ke server</li>
                        <li>Contoh: <code>system($_GET['cmd'])</code></li>
                    </ul>

                    <p style="margin-top: 15px;"><strong>2. Website Defacement</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Overwrite file penting (index.php, config.php)</li>
                        <li>Tampilan website diubah attacker</li>
                        <li>Reputasi rusak</li>
                    </ul>

                    <p style="margin-top: 15px;"><strong>3. Backdoor Persistence</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>File backdoor tetap aktif meski celah ditutup</li>
                        <li>Akses jangka panjang untuk attacker</li>
                        <li>Sulit dideteksi</li>
                    </ul>

                    <p style="margin-top: 15px;"><strong>4. Data Exfiltration</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Membaca file sensitif (config.php, .env)</li>
                        <li>Dump database</li>
                        <li>Kirim data ke server attacker</li>
                    </ul>
                </div>
            </div>

            <!-- Uploaded Files List -->
            <div class="file-list">
                <h3>📁 File yang Diupload (<?= count($files) ?>)</h3>
                
                <?php if (empty($files)): ?>
                    <p style="color: #999; text-align: center; padding: 20px;">Belum ada file yang diupload.</p>
                <?php else: ?>
                    <?php foreach ($files as $file): 
                        $ext = strtolower(pathinfo($file['filename'], PATHINFO_EXTENSION));
                        $is_dangerous = in_array($ext, ['php', 'phtml', 'php3', 'exe', 'sh', 'bat']);
                    ?>
                        <div class="file-item" style="border-left: 4px solid <?= $is_dangerous ? '#e74c3c' : '#3498db' ?>;">
                            <div>
                                <strong style="<?= $is_dangerous ? 'color: #e74c3c;' : '' ?>">
                                    <?= $is_dangerous ? '⚠️ ' : '📄 ' ?>
                                    <?= safe_output($file['filename']) ?>
                                </strong>
                                <div style="font-size: 0.85rem; color: #999; margin-top: 5px;">
                                    Uploaded: <?= date('d M Y H:i', strtotime($file['uploaded_at'])) ?>
                                    <?php if ($is_dangerous): ?>
                                        <span style="color: #e74c3c; font-weight: 600;"> | BERBAHAYA!</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a href="<?= $file['file_path'] ?>" target="_blank" class="btn btn-secondary" style="padding: 8px 15px;">View</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div style="margin-top: 30px; text-align: center;">
                <a href="upload_safe.php" class="btn btn-success">Lihat Versi Aman (Safe) →</a>
            </div>
        </div>
    </div>
</body>
</html>