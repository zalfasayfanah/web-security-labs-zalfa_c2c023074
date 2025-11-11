<?php
require_once '../config.php';

// Fetch all comments (both vulnerable and safe)
$stmt = $pdo->query("SELECT * FROM comments ORDER BY created_at DESC");
$all_comments = $stmt->fetchAll();

// Separate by type
$vulnerable_comments = array_filter($all_comments, fn($c) => $c['is_safe'] == 0);
$safe_comments = array_filter($all_comments, fn($c) => $c['is_safe'] == 1);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Comments - XSS Demo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="lab-page">
            <div class="lab-header">
                <h1 class="lab-title">📋 Semua Komentar</h1>
                <a href="../index.php" class="btn btn-secondary">← Kembali ke Home</a>
            </div>

            <div class="info-box">
                <h4>Perbandingan Vulnerable vs Safe Output</h4>
                <p>Halaman ini menampilkan semua komentar dari kedua versi (vulnerable dan safe) untuk perbandingan.</p>
            </div>

            <div class="demo-section">
                <!-- Vulnerable Comments -->
                <div class="demo-box vulnerable">
                    <div class="demo-header">
                        <span style="font-size: 1.5rem;">❌</span>
                        <h3>Komentar Vulnerable (<?= count($vulnerable_comments) ?>)</h3>
                    </div>
                    
                    <div style="background: #fff3cd; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 0.9rem;">
                        ⚠️ Ditampilkan tanpa escape - XSS akan tereksekusi!
                    </div>

                    <?php if (empty($vulnerable_comments)): ?>
                        <p style="color: #999; text-align: center; padding: 20px;">Belum ada komentar vulnerable.</p>
                    <?php else: ?>
                        <?php foreach ($vulnerable_comments as $comment): ?>
                            <div class="comment-item" style="border-left-color: #e74c3c; margin-bottom: 10px;">
                                <div class="comment-header">
                                    <span class="comment-author">👤 <?= $comment['username'] ?></span>
                                    <span class="comment-date">📅 <?= date('d M Y H:i', strtotime($comment['created_at'])) ?></span>
                                </div>
                                <div class="comment-body">
                                    <!-- VULNERABLE OUTPUT -->
                                    <?= $comment['comment'] ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Safe Comments -->
                <div class="demo-box safe">
                    <div class="demo-header">
                        <span style="font-size: 1.5rem;">✅</span>
                        <h3>Komentar Safe (<?= count($safe_comments) ?>)</h3>
                    </div>
                    
                    <div style="background: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 0.9rem;">
                        ✅ Di-escape dengan htmlspecialchars() - XSS tidak akan tereksekusi
                    </div>

                    <?php if (empty($safe_comments)): ?>
                        <p style="color: #999; text-align: center; padding: 20px;">Belum ada komentar safe.</p>
                    <?php else: ?>
                        <?php foreach ($safe_comments as $comment): ?>
                            <div class="comment-item" style="border-left-color: #27ae60; margin-bottom: 10px;">
                                <div class="comment-header">
                                    <span class="comment-author">👤 <?= safe_output($comment['username']) ?></span>
                                    <span class="comment-date">📅 <?= date('d M Y H:i', strtotime($comment['created_at'])) ?></span>
                                </div>
                                <div class="comment-body">
                                    <!-- SAFE OUTPUT -->
                                    <?= safe_output($comment['comment']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="info-box" style="margin-top: 30px;">
                <h4>📊 Statistik</h4>
                <table class="data-table">
                    <tr>
                        <td style="width: 50%;"><strong>Total Komentar:</strong></td>
                        <td><?= count($all_comments) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Vulnerable Comments:</strong></td>
                        <td style="color: #e74c3c; font-weight: 600;"><?= count($vulnerable_comments) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Safe Comments:</strong></td>
                        <td style="color: #27ae60; font-weight: 600;"><?= count($safe_comments) ?></td>
                    </tr>
                </table>
            </div>

            <div style="margin-top: 30px; text-align: center; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                <a href="comment_vulnerable.php" class="btn btn-warning">Post Vulnerable Comment</a>
                <a href="comment_safe.php" class="btn btn-success">Post Safe Comment</a>
                
                <?php if (!empty($all_comments)): ?>
                    <form method="POST" action="?clearall=1" style="display: inline;">
                        <?php 
                        if (isset($_GET['clearall'])) {
                            $pdo->exec("DELETE FROM comments");
                            redirect('view_comments.php');
                        }
                        ?>
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Hapus semua komentar?')">🗑️ Clear All</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>