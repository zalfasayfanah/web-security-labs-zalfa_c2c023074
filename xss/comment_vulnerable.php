<?php
require_once '../config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $comment = $_POST['comment'];
    
    // VULNERABLE: Simpan comment tanpa sanitasi
    $stmt = $pdo->prepare("INSERT INTO comments (username, comment, is_safe) VALUES (?, ?, 0)");
    $stmt->execute([$username, $comment]);
    
    $success_message = "Komentar berhasil disimpan (tanpa sanitasi)!";
}

// Fetch all vulnerable comments
$stmt = $pdo->query("SELECT * FROM comments WHERE is_safe = 0 ORDER BY created_at DESC");
$comments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XSS - Vulnerable Comment System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="lab-page">
            <div class="lab-header">
                <h1 class="lab-title">💻 Cross-Site Scripting (XSS) - Vulnerable</h1>
                <a href="../index.php" class="btn btn-secondary">← Kembali ke Home</a>
            </div>

            <div class="info-box">
                <h4>Konsep XSS (Cross-Site Scripting)</h4>
                <p>XSS memungkinkan attacker menyisipkan JavaScript berbahaya yang akan dieksekusi di browser korban. Stored XSS menyimpan payload di database dan dijalankan setiap kali halaman dimuat.</p>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-warning">
                    ⚠️ <?= $success_message ?>
                </div>
            <?php endif; ?>

            <div class="demo-section">
                <div class="demo-box vulnerable">
                    <div class="demo-header">
                        <span style="font-size: 1.5rem;">❌</span>
                        <h3>Stored XSS - Vulnerable</h3>
                    </div>

                    <div class="code-box">
                        // Simpan ke database<br>
                        $stmt->execute([$username, $comment]);<br><br>
                        // Tampilkan tanpa escape<br>
                        echo $row['comment']; // VULNERABLE!
                    </div>

                    <form method="POST">
                        <div class="form-group">
                            <label>Username:</label>
                            <input type="text" name="username" class="form-control" placeholder="Nama Anda" required>
                        </div>

                        <div class="form-group">
                            <label>Komentar:</label>
                            <textarea name="comment" class="form-control" placeholder="Tulis komentar Anda..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-warning" style="width: 100%;">Post Comment (Vulnerable)</button>
                    </form>

                    <div class="payload-list">
                        <p><strong>Payload XSS untuk dicoba:</strong></p>
                        <ul>
                            <li><code>&lt;script&gt;alert('XSS Attack!')&lt;/script&gt;</code></li>
                            <li><code>&lt;img src=x onerror=alert('XSS')&gt;</code></li>
                            <li><code>&lt;svg onload=alert('XSS')&gt;</code></li>
                            <li><code>&lt;iframe src="javascript:alert('XSS')"&gt;</code></li>
                        </ul>
                        <p style="color: #d63031; font-weight: 600; margin-top: 10px;">⚠️ Hanya untuk lab! Jangan gunakan di sistem produksi!</p>
                    </div>
                </div>

                <div class="demo-box" style="border-color: #e74c3c; background: #fef5f5;">
                    <div class="demo-header">
                        <span style="font-size: 1.5rem;">⚡</span>
                        <h3>Dampak XSS</h3>
                    </div>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>🍪 <strong>Cookie Theft:</strong> Mencuri session cookies</li>
                        <li>🎣 <strong>Phishing:</strong> Menampilkan fake login form</li>
                        <li>⌨️ <strong>Keylogging:</strong> Merekam input keyboard</li>
                        <li>🔄 <strong>Request Forgery:</strong> Aksi atas nama user</li>
                        <li>📱 <strong>Defacement:</strong> Mengubah tampilan halaman</li>
                    </ul>

                    <p style="margin-top: 15px;"><strong>Mengapa Berbahaya?</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Output langsung tanpa encoding</li>
                        <li>Tidak ada Content Security Policy</li>
                        <li>Browser execute script as trusted code</li>
                    </ul>
                </div>
            </div>

            <!-- Display Comments -->
            <div class="comments-section">
                <h3>💬 Daftar Komentar (<?= count($comments) ?>)</h3>
                <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #ffc107;">
                    <strong>⚠️ Peringatan:</strong> Komentar di bawah ini ditampilkan tanpa sanitasi. Jika ada payload XSS, akan langsung dieksekusi!
                </div>

                <?php if (empty($comments)): ?>
                    <p style="color: #999; text-align: center; padding: 20px;">Belum ada komentar. Coba posting komentar dengan payload XSS!</p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-item" style="border-left-color: #e74c3c;">
                            <div class="comment-header">
                                <span class="comment-author">👤 <?= $comment['username'] ?></span>
                                <span class="comment-date">📅 <?= date('d M Y H:i', strtotime($comment['created_at'])) ?></span>
                            </div>
                            <div class="comment-body">
                                <!-- VULNERABLE: Echo langsung tanpa escape -->
                                <?= $comment['comment'] ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <form method="POST" action="?clear=1" style="margin-top: 20px;">
                        <?php 
                        if (isset($_GET['clear'])) {
                            $pdo->exec("DELETE FROM comments WHERE is_safe = 0");
                            redirect('comment_vulnerable.php');
                        }
                        ?>
                        <button type="submit" class="btn btn-secondary">🗑️ Clear All Comments</button>
                    </form>
                <?php endif; ?>
            </div>

            <div style="margin-top: 30px; text-align: center;">
                <a href="comment_safe.php" class="btn btn-success">Lihat Versi Aman (Safe) →</a>
            </div>
        </div>
    </div>
</body>
</html>