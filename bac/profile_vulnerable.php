<?php
require_once '../config.php';

// Simulasi: User yang sedang login adalah Alice (ID: 101)
$_SESSION['current_user_id'] = '101';
$_SESSION['current_user_name'] = 'Alice';

$result = null;
$access_granted = false;

// VULNERABLE: Tidak ada validasi ownership
if (isset($_GET['id'])) {
    $student_id = $_GET['id'];
    
    // Query langsung tanpa cek ownership
    $stmt = $pdo->prepare("SELECT * FROM student_data WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $result = $stmt->fetch();
    
    if ($result) {
        $access_granted = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Broken Access Control - Vulnerable</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="lab-page">
            <div class="lab-header">
                <h1 class="lab-title">🔒 Broken Access Control (IDOR) - Vulnerable</h1>
                <a href="../index.php" class="btn btn-secondary">← Kembali ke Home</a>
            </div>

            <div class="info-box">
                <h4>Konsep Broken Access Control (BAC/IDOR)</h4>
                <p>BAC terjadi ketika aplikasi tidak memvalidasi kepemilikan resource. User dapat mengakses data user lain hanya dengan mengubah ID di URL (IDOR - Insecure Direct Object Reference).</p>
            </div>

            <!-- Login Simulation -->
            <div style="background: #d4edda; padding: 20px; border-radius: 10px; margin-bottom: 25px; border-left: 5px solid #28a745;">
                <h4>👤 Simulasi Login</h4>
                <p style="margin-bottom: 0;">✓ Anda login sebagai: <strong><?= $_SESSION['current_user_name'] ?> (ID: <?= $_SESSION['current_user_id'] ?>)</strong></p>
                <small style="color: #666;">Session User ID: <?= $_SESSION['current_user_id'] ?></small>
            </div>

            <div class="demo-section">
                <div class="demo-box vulnerable">
                    <div class="demo-header">
                        <span style="font-size: 1.5rem;">❌</span>
                        <h3>Tanpa Validasi Ownership (IDOR)</h3>
                    </div>

                    <div class="code-box">
                        // VULNERABLE: Tidak ada cek ownership!<br>
                        $id = $_GET['id'];<br>
                        $stmt = $pdo->prepare("SELECT * FROM student_data WHERE student_id = ?");<br>
                        $stmt->execute([$id]);<br>
                        // Langsung tampilkan data tanpa validasi!
                    </div>

                    <form method="GET">
                        <div class="form-group">
                            <label>URL: /profile_vulnerable.php?id=</label>
                            <input type="text" name="id" class="form-control" value="<?= isset($_GET['id']) ? safe_output($_GET['id']) : '101' ?>" placeholder="Student ID">
                        </div>

                        <button type="submit" class="btn btn-danger" style="width: 100%;">Akses Data (Vulnerable)</button>
                    </form>

                    <div class="payload-list">
                        <p><strong>Coba ubah ID di URL:</strong></p>
                        <ul>
                            <li><a href="?id=101" style="color: #27ae60;">?id=101</a> - Alice (Anda sendiri) ✓</li>
                            <li><a href="?id=102" style="color: #e74c3c;">?id=102</a> - Bob (Orang lain) ⚠️</li>
                            <li><a href="?id=103" style="color: #e74c3c;">?id=103</a> - Charlie (Orang lain) ⚠️</li>
                            <li><a href="?id=104" style="color: #e74c3c;">?id=104</a> - Diana (Orang lain) ⚠️</li>
                        </ul>
                        <p style="color: #e74c3c; font-weight: 600; margin-top: 10px;">⚠️ Anda bisa mengakses data user lain!</p>
                    </div>
                </div>

                <div class="demo-box" style="border-color: #e74c3c; background: #fef5f5;">
                    <div class="demo-header">
                        <span style="font-size: 1.5rem;">💥</span>
                        <h3>Dampak BAC/IDOR</h3>
                    </div>
                    
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>🔓 <strong>Horizontal Privilege Escalation:</strong> Akses data user lain dengan role yang sama</li>
                        <li>⬆️ <strong>Vertical Privilege Escalation:</strong> User biasa akses fungsi admin</li>
                        <li>📊 <strong>Data Leakage:</strong> Informasi pribadi bocor</li>
                        <li>✏️ <strong>Data Manipulation:</strong> Ubah/hapus data orang lain</li>
                        <li>⚖️ <strong>Compliance Violation:</strong> Melanggar GDPR, UU PDP</li>
                    </ul>

                    <p style="margin-top: 15px;"><strong>Mengapa Vulnerable?</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Tidak ada pengecekan ownership</li>
                        <li>ID sequential dan mudah ditebak</li>
                        <li>Hanya mengandalkan URL parameter</li>
                        <li>Tidak validasi session_user_id</li>
                    </ul>
                </div>
            </div>

            <!-- Result Display -->
            <?php if (isset($_GET['id'])): ?>
                <div class="result-box <?= $access_granted ? 'danger' : 'success' ?>">
                    <h4>Hasil Akses:</h4>
                    
                    <?php if ($access_granted): ?>
                        <?php 
                        $is_own_data = ($result['student_id'] === $_SESSION['current_user_id']);
                        ?>
                        
                        <div class="alert alert-<?= $is_own_data ? 'success' : 'danger' ?>">
                            <?php if ($is_own_data): ?>
                                <strong>✓ AKSES DATA SENDIRI</strong><br>
                                Anda mengakses data Anda sendiri (normal behavior).
                            <?php else: ?>
                                <strong>⚠️ IDOR VULNERABILITY DETECTED!</strong><br>
                                Anda berhasil mengakses data user lain! Ini adalah kerentanan keamanan serius.
                            <?php endif; ?>
                        </div>

                        <div style="background: white; padding: 20px; border-radius: 10px; margin-top: 15px;">
                            <h4>👤 Data Mahasiswa</h4>
                            <table class="data-table">
                                <tr>
                                    <td style="width: 150px;"><strong>Student ID:</strong></td>
                                    <td><?= safe_output($result['student_id']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Nama:</strong></td>
                                    <td><?= safe_output($result['name']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td><?= safe_output($result['email']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Nilai:</strong></td>
                                    <td><strong style="font-size: 1.2rem; color: #27ae60;"><?= safe_output($result['grade']) ?></strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Jurusan:</strong></td>
                                    <td><?= safe_output($result['major']) ?></td>
                                </tr>
                            </table>
                        </div>

                        <?php if (!$is_own_data): ?>
                            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin-top: 15px; border-left: 4px solid #ffc107;">
                                <strong>💡 Penjelasan:</strong><br>
                                Server tidak memvalidasi apakah <code>$_GET['id']</code> sama dengan <code>$_SESSION['current_user_id']</code>. 
                                Attacker dapat enumerate ID (101, 102, 103...) untuk mengakses semua data user.
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <strong>✗ DATA TIDAK DITEMUKAN</strong><br>
                            Student ID tidak ada dalam database.
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div style="margin-top: 30px; text-align: center;">
                <a href="profile_safe.php?id=101" class="btn btn-success">Lihat Versi Aman (Safe) →</a>
                <a href="data.php" class="btn btn-info">Lihat Semua Data →</a>
            </div>
        </div>
    </div>
</body>
</html>