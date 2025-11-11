<?php
require_once '../config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $comment = $_POST['comment'];
    
    // SAFE: Simpan raw data, sanitasi saat output
    $stmt = $pdo->prepare("INSERT INTO comments (username, comment, is_safe) VALUES (?, ?, 1)");
    $stmt->execute([$username, $comment]);
    
    $success_message = "Komentar berhasil disimpan dengan aman!";
}

// Fetch all safe comments
$stmt = $pdo->query("SELECT * FROM comments WHERE is_safe = 1 ORDER BY created_at DESC");
$comments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XSS - Safe Comment System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="lab-page">
            <div class="lab-header">
                <h1 class="lab-title">💻 Cross-Site Scripting (XSS) - Safe</h1>
                <a href="../index.php" class="btn btn-secondary">← Kembali ke Home</a>
            </div>

            <div class="info-box">
                <h4>Mitigasi XSS dengan Output Encoding</h4>
                <p>Data disimpan dalam bentuk raw, tetapi di-escape menggunakan <code>htmlspecialchars()</code> saat ditampilkan. Script tags akan diubah menjadi entitas HTML sehingga tidak dieksekusi.</p>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    ✅ <?= $success_message ?>
                </div>
            <?php endif; ?>

            <div class="demo-section">
                <div class="demo-box safe">
                    <div class="demo-header">
                        <span style="font-size: 1.5rem;">✅</span>
                        <h3>Stored XSS - Safe</h3>
                    </div>

                    <div class="code-box">
                        // Simpan raw data ke database<br>
                        $stmt->execute([$username, $comment]);<br><br>
                        // Escape saat output<br>
                        echo htmlspecialchars($row['comment'], ENT_QUOTES, 'UTF-8');
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

                        <button type="submit" class="btn btn-success" style="width: 100%;">Post Comment (Safe)</button>
                    </form>

                    <div class="payload-list">
                        <p><strong>Coba payload XSS (tidak akan berhasil):</strong></p>
                        <ul>
                            <li><code>&lt;script&gt;alert('XSS')&lt;/script&gt;</code></li>
                            <li><code>&lt;img src=x onerror=alert(1)&gt;</code></li>
                            <li><code>&lt;svg onload=alert('XSS')&gt;</code></li>
                        </ul>
                        <p style="color: #27ae60; font-weight: 600; margin-top: 10px;">✅ Semua payload akan di-escape dan ditampilkan sebagai text!</p>
                    </div>
                </div>

                <div class="demo-box" style="border-color: #27ae60; background: #f0fff0;">
                    <div class="demo-header">
                        <span style="font-size: 1.5rem;">🛡️</span>
                        <h3>Teknik Mitigasi</h3>
                    </div>
                    <p><strong>1. Output Encoding</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li><code>htmlspecialchars()</code> untuk HTML context</li>
                        <li><code>ENT_QUOTES</code> untuk escape single & double quotes</li>
                        <li><code>UTF-8</code> encoding untuk karakter khusus</li>
                    </ul>

                    <p style="margin-top: 15px;"><strong>2. Content Security Policy (CSP)</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Header: <code>Content-Security-Policy</code></li>
                        <li>Block inline scripts: <code>script-src 'self'</code></li>
                        <li>Prevent eval(): <code>unsafe-eval</code> disabled</li>
                    </ul>

                    <p style="margin-top: 15px;"><strong>3. HTTPOnly Cookies</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Cookie tidak bisa diakses via JavaScript</li>
                        <li>Mencegah cookie theft melalui XSS</li>
                        <li>Set: <code>setcookie(..., httponly: true)</code></li>
                    </ul>

                    <p style="margin-top: 15px;"><strong>4. Input Validation</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Whitelist characters allowed</li>
                        <li>Reject/strip HTML tags jika tidak perlu</li>
                        <li>Validasi di server-side, bukan hanya client</li>
                    </ul>
                </div>
            </div>

            <!-- Display Comments -->
            <div class="comments-section">
                <h3>💬 Daftar Komentar (<?= count($comments) ?>)</h3>
                <div style="background: #d4edda; padding: 15px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #28a745;">
                    <strong>✅ Aman:</strong> Semua komentar di-escape dengan <code>htmlspecialchars()</code>. Payload XSS akan ditampilkan sebagai text biasa.
                </div>

                <?php if (empty($comments)): ?>
                    <p style="color: #999; text-align: center; padding: 20px;">Belum ada komentar. Coba posting komentar dengan payload XSS dan lihat hasilnya!</p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-item" style="border-left-color: #27ae60;">
                            <div class="comment-header">
                                <span class="comment-author">👤 <?= safe_output($comment['username']) ?></span>
                                <span class="comment-date">📅 <?= date('d M Y H:i', strtotime($comment['created_at'])) ?></span>
                            </div>
                            <div class="comment-body">
                                <!-- SAFE: Gunakan htmlspecialchars untuk escape -->
                                <?= safe_output($comment['comment']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <form method="POST" action="?clear=1" style="margin-top: 20px;">
                        <?php 
                        if (isset($_GET['clear'])) {
                            $pdo->exec("DELETE FROM comments WHERE is_safe = 1");
                            redirect('comment_safe.php');
                        }
                        ?>
                        <button type="submit" class="btn btn-secondary">🗑️ Clear All Comments</button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="info-box" style="margin-top: 30px;">
                <h4>🔍 Perbandingan Output</h4>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Input</th>
                            <th style="width: 30%;">Vulnerable Output</th>
                            <th style="width: 30%;">Safe Output</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>&lt;script&gt;alert('XSS')&lt;/script&gt;</code></td>
                            <td style="background: #fef5f5; color: #e74c3c;">❌ Executed as script</td>
                            <td style="background: #f0fff0; color: #27ae60;">✅ Displayed as text</td>
                        </tr>
                        <tr>
                            <td><code>&lt;img src=x onerror=alert(1)&gt;</code></td>
                            <td style="background: #fef5f5; color: #e74c3c;">❌ Triggers onerror</td>
                            <td style="background: #f0fff0; color: #27ae60;">✅ Shows as &amp;lt;img...&amp;gt;</td>
                        </tr>
                        <tr>
                            <td><code>&lt;svg onload=alert('XSS')&gt;</code></td>
                            <td style="background: #fef5f5; color: #e74c3c;">❌ Onload executed</td>
                            <td style="background: #f0fff0; color: #27ae60;">✅ Escaped completely</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 30px; text-align: center;">
                <a href="comment_vulnerable.php" class="btn btn-warning">← Lihat Versi Vulnerable</a>
                <a href="view_comments.php" class="btn btn-info">Lihat Semua Komentar →</a>
            </div>
        </div>
    </div>
</body>
</html>