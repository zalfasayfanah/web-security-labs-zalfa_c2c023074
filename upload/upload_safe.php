<?php
require_once '../config.php';

$upload_message = '';
$upload_status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $original_name = $file['name'];
    $tmp_name = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    
    // SAFE: Validasi ketat
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
    $max_file_size = 5 * 1024 * 1024; // 5MB
    
    // Check for upload errors
    if ($file_error !== UPLOAD_ERR_OK) {
        $upload_message = "Error upload file!";
        $upload_status = 'danger';
    } else {
        // Get file extension
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        
        // Validate extension (whitelist)
        if (!in_array($ext, $allowed_extensions)) {
            $upload_message = "❌ Ekstensi file tidak diizinkan! Hanya: " . implode(', ', $allowed_extensions);
            $upload_status = 'danger';
        }
        // Validate file size
        elseif ($file_size > $max_file_size) {
            $upload_message = "❌ Ukuran file terlalu besar! Maksimal 5MB.";
            $upload_status = 'danger';
        }
        // Validate MIME type
        elseif (!in_array($file['type'], ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
            $upload_message = "❌ Tipe MIME tidak valid!";
            $upload_status = 'danger';
        } else {
            // Generate safe filename dengan UUID
            $safe_filename = uniqid() . '_' . time() . '.' . $ext;
            $upload_path = 'uploads/' . $safe_filename;
            
            // Move uploaded file
            if (move_uploaded_file($tmp_name, $upload_path)) {
                // Save to database
                $stmt = $pdo->prepare("INSERT INTO uploaded_files (filename, original_name, file_path, upload_type) VALUES (?, ?, ?, 'safe')");
                $stmt->execute([$safe_filename, $original_name, $upload_path]);
                
                $upload_message = "✅ File berhasil diupload dengan aman!<br>Original: <code>$original_name</code><br>Saved as: <code>$safe_filename</code>";
                $upload_status = 'success';
            } else {
                $upload_message = "❌ Gagal memindahkan file!";
                $upload_status = 'danger';
            }
        }
    }
}

// Fetch uploaded files
$stmt = $pdo->query("SELECT * FROM uploaded_files WHERE upload_type = 'safe' ORDER BY uploaded_at DESC");
$files = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload - Safe Version</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="lab-page">
            <div class="lab-header">
                <h1 class="lab-title">📤 File Upload Vulnerability - Safe</h1>
                <a href="../index.php" class="btn btn-secondary">← Kembali ke Home</a>
            </div>

            <div class="info-box">
                <h4>Mitigasi File Upload Vulnerability</h4>
                <p>Implementasi validasi ketat dengan whitelist ekstensi, validasi MIME type, generate nama file dengan UUID, dan batasan ukuran file.</p>
            </div>

            <?php if ($upload_message): ?>
                <div class="alert alert-<?= $upload_status ?>">
                    <?= $upload_message ?>
                </div>
            <?php endif; ?>

            <div class="demo-section">
                <div class="demo-box safe">
                    <div class="demo-header">
                        <span style="font-size: 1.5rem;">✅</span>
                        <h3>Upload dengan Validasi</h3>
                    </div>

                    <div class="code-box">
                        // SAFE: Validasi ketat<br>
                        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];<br>
                        $ext = pathinfo($name, PATHINFO_EXTENSION);<br>
                        if (!in_array($ext, $allowed)) die("Forbidden");<br>
                        $safe_name = uniqid() . '.' . $ext;
                    </div>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Pilih File:</label>
                            <input type="file" name="file" class="form-control" required>
                            <small style="color: #666; font-size: 0.85rem;">Ekstensi yang diizinkan: jpg, jpeg, png, gif, pdf, doc, docx (Max: 5MB)</small>
                        </div>

                        <button type="submit" class="btn btn-success" style="width: 100%;">Upload File (Safe)</button>
                    </form>

                    <div class="payload-list">
                        <p><strong>Coba upload file berbahaya (akan ditolak):</strong></p>
                        <ul>
                            <li><code>shell.php</code> ❌ Ditolak</li>
                            <li><code>backdoor.phtml</code> ❌ Ditolak</li>
                            <li><code>virus.exe</code> ❌ Ditolak</li>
                        </ul>
                        <p style="color: #27ae60; font-weight: 600; margin-top: 10px;">✅ Hanya file dengan ekstensi whitelist yang diterima!</p>
                    </div>
                </div>

                <div class="demo-box" style="border-color: #27ae60; background: #f0fff0;">
                    <div class="demo-header">
                        <span style="font-size: 1.5rem;">🛡️</span>
                        <h3>Teknik Keamanan</h3>
                    </div>
                    
                    <p><strong>1. Whitelist Extension</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Hanya izinkan ekstensi yang diperlukan</li>
                        <li><code>$allowed = ['jpg', 'png', 'pdf'];</code></li>
                        <li>Blacklist tidak cukup aman!</li>
                    </ul>

                    <p style="margin-top: 15px;"><strong>2. MIME Type Validation</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Cek <code>$_FILES['file']['type']</code></li>
                        <li>Validasi content-type header</li>
                        <li>Gunakan <code>finfo_file()</code> untuk deep check</li>
                    </ul>

                    <p style="margin-top: 15px;"><strong>3. Rename File (UUID)</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Generate nama random: <code>uniqid()</code></li>
                        <li>Hindari nama file original user</li>
                        <li>Cegah path traversal (../, %00, dll)</li>
                    </ul>

                    <p style="margin-top: 15px;"><strong>4. File Size Limit</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Batasi ukuran maksimal (5MB, 10MB)</li>
                        <li>Cegah DoS via large file upload</li>
                        <li>Set di PHP: <code>upload_max_filesize</code></li>
                    </ul>

                    <p style="margin-top: 15px;"><strong>5. Store Outside Webroot</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Simpan file di luar folder public</li>
                        <li>Serve via script dengan authentication</li>
                        <li>Cegah direct access ke uploaded files</li>
                    </ul>

                    <p style="margin-top: 15px;"><strong>6. Additional Security</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Set permissions: <code>chmod 644</code></li>
                        <li>Disable script execution di uploads folder</li>
                        <li>Scan dengan antivirus (ClamAV)</li>
                        <li>Content Security Policy header</li>
                    </ul>
                </div>
            </div>

            <!-- Uploaded Files List -->
            <div class="file-list">
                <h3>📁 File yang Diupload (<?= count($files) ?>)</h3>
                
                <div style="background: #d4edda; padding: 15px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #28a745;">
                    <strong>✅ Aman:</strong> Semua file telah divalidasi dan di-rename dengan UUID.
                </div>

                <?php if (empty($files)): ?>
                    <p style="color: #999; text-align: center; padding: 20px;">Belum ada file yang diupload.</p>
                <?php else: ?>
                    <?php foreach ($files as $file): ?>
                        <div class="file-item" style="border-left: 4px solid #27ae60;">
                            <div>
                                <strong>📄 <?= safe_output($file['original_name']) ?></strong>
                                <div style="font-size: 0.85rem; color: #999; margin-top: 5px;">
                                    Saved as: <code><?= safe_output($file['filename']) ?></code><br>
                                    Uploaded: <?= date('d M Y H:i', strtotime($file['uploaded_at'])) ?>
                                </div>
                            </div>
                            <a href="<?= $file['file_path'] ?>" target="_blank" class="btn btn-success" style="padding: 8px 15px;">View</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="info-box" style="margin-top: 30px;">
                <h4>⚙️ Konfigurasi .htaccess untuk Folder Uploads</h4>
                <div class="code-box">
                    # Disable PHP execution in uploads folder<br>
                    php_flag engine off<br><br>
                    # Deny access to specific file types<br>
                    &lt;FilesMatch "\.(php|phtml|php3|php4|php5|pl|py|jsp|asp|sh|cgi)$"&gt;<br>
                    &nbsp;&nbsp;Order allow,deny<br>
                    &nbsp;&nbsp;Deny from all<br>
                    &lt;/FilesMatch&gt;
                </div>
            </div>

            <div style="margin-top: 30px; text-align: center;">
                <a href="upload_vulnerable.php" class="btn btn-warning">← Lihat Versi Vulnerable</a>
            </div>
        </div>
    </div>
</body>
</html>