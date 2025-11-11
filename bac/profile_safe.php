<?php
require_once '../config.php';

// Simulasi: User yang sedang login adalah Alice (ID: 101)
$_SESSION['current_user_id'] = '101';
$_SESSION['current_user_name'] = 'Alice';

$result = null;
$access_granted = false;
$access_denied = false;

// SAFE: Validasi ownership
if (isset($_GET['id'])) {
    $requested_id = $_GET['id'];
    $current_user_id = $_SESSION['current_user_id'];
    
    // Cek apakah ID yang diminta = ID user yang login
    if ($requested_id !== $current_user_id) {
        $access_denied = true;
    } else {
        // Query dengan validasi ownership
        $stmt = $pdo->prepare("SELECT * FROM student_data WHERE student_id = ? AND student_id = ?");
        $stmt->execute([$requested_id, $current_user_id]);
        $result = $stmt->fetch();
        
        if ($result) {
            $access_granted = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Broken Access Control - Safe</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="lab-page">
            <div class="lab-header">
                <h1 class="lab-title">🔒 Broken Access Control (IDOR) - Safe</h1>
                <a href="../index.php" class="btn btn-secondary">← Kembali ke Home</a>
            </div>

            <div class="info-box">
                <h4>Mitigasi Broken Access Control</h4>
                <p>Implementasi validasi ownership dengan memastikan <code>requested_id === session_user_id</code>. Server selalu memvalidasi hak akses sebelum menampilkan data.</p>
            </div>

            <!-- Login Simulation -->
            <div style="background: #d4edda; padding: 20px; border-radius: 10px; margin-bottom: 25px; border-left: 5px solid #28a745;">
                <h4>👤 Simulasi Login</h4>
                <p style="margin-bottom: 0;">✓ Anda login sebagai: <strong><?= $_SESSION['current_user_name'] ?> (ID: <?= $_SESSION['current_user_id'] ?>)</strong></p>
                <small style="color: #666;">Session User ID: <?= $_SESSION['current_user_id'] ?></small>
            </div>

            <div class="demo-section">
                <div class="demo-box safe">
                    <div class="demo-header">
                        <span style="font-size: 1.5rem;">✅</span>
                        <h3>Dengan Validasi Ownership</h3>
                    </div>

                    <div class="code-box">
                        // SAFE: Validasi ownership!<br>
                        $requested_id = $_GET['id'];<br>
                        $current_user_id = $_SESSION['user_id'];<br><br>
                        if ($requested_id !== $current_user_id) {<br>
                        &nbsp;&nbsp;die("Access Denied!");<br>
                        }<br><br>
                        // Query dengan double validation<br>
                        $stmt = $pdo->prepare("SELECT * FROM student_data WHERE student_id = ? AND student_id = ?");
                    </div>

                    <form method="GET">
                        <div class="form-group">
                            <label>URL: /profile_safe.php?id=</label>
                            <input type="text" name="id" class="form-control" value="<?= isset($_GET['id']) ? safe_output($_GET['id']) : '101' ?>" placeholder="Student ID">
                        </div>

                        <button type="submit" class="btn btn-success" style="width: 100%;">Akses Data (Safe)</button>
                    </form>

                    <div class="payload-list">
                        <p><strong>Coba ubah ID di URL:</strong></p>
                        <ul>
                            <li><a href="?id=101" style="color: #27ae60;">?id=101</a> - Alice (Anda sendiri) ✓ Allowed</li>
                            <li><a href="?id=102" style="color: #e74c3c;">?id=102</a> - Bob ❌ Access Denied</li>
                            <li><a href="?id=103" style="color: #e74c3c;">?id=103</a> - Charlie ❌ Access Denied</li>
                            <li><a href="?id=104" style="color: #e74c3c;">?id=104</a> - Diana ❌ Access Denied</li>
                        </ul>
                        <p style="color: #27ae60; font-weight: 600; margin-top: 10px;">✅ Hanya bisa akses data sendiri!</p>
                    </div>
                </div>

                <div class="demo-box" style="border-color: #27ae60; background: #f0fff0;">
                    <div class="demo-header">
                        <span style="font-size: 1.5rem;">🛡️</span>
                        <h3>Teknik Keamanan</h3>
                    </div>
                    
                    <p><strong>1. Server-Side Validation</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Validasi di server, bukan client</li>
                        <li>Cek <code>$_GET['id'] === $_SESSION['user_id']</code></li>
                        <li>Return 403 Forbidden jika tidak match</li>
                    </ul>

                    <p style="margin-top: 15px;"><strong>2. Use UUIDs Instead of Sequential IDs</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>UUID v4: <code>550e8400-e29b-41d4-a716-446655440000</code></li>
                        <li>Tidak bisa ditebak/enumerate</li>
                        <li>128-bit random: sangat kecil kemungkinan collision</li>
                    </ul>

                    <p style="margin-top: 15px;"><strong>3. Token-Based Authorization</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Generate token saat create resource</li>
                        <li>Hash token di database</li>
                        <li>URL: <code>/profile?id=UUID&token=SECRET</code></li>
                    </ul>

                    <p style="margin-top: 15px;"><strong>4. Role-Based Access Control (RBAC)</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Define roles: admin, user, guest</li>
                        <li>Check permissions: <code>can_view(), can_edit()</code></li>
                        <li>Middleware untuk enforce policy</li>
                    </ul>

                    <p style="margin-top: 15px;"><strong>5. Indirect Object Reference</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Mapping: <code>user_index[0] → actual_id[4521]</code></li>
                        <li>User hanya lihat index, bukan real ID</li>
                        <li>Server translate index ke actual ID</li>
                    </ul>

                    <p style="margin-top: 15px;"><strong>6. Logging & Monitoring</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Log semua access denied attempts</li>
                        <li>Alert jika ada spike suspicious requests</li>
                        <li>Track user_id + IP + timestamp</li>
                    </ul>
                </div>
            </div>

            <!-- Result Display -->
            <?php if (isset($_GET['id'])): ?>
                <div class="result-box <?= $access_granted ? 'success' : 'danger' ?>">
                    <h4>Hasil Akses:</h4>
                    
                    <?php if ($access_denied): ?>
                        <div class="alert alert-danger">
                            <strong>🚫 ACCESS DENIED!</strong><br>
                            Anda tidak memiliki hak akses ke data ini. Server memvalidasi bahwa <code>requested_id (<?= safe_output($requested_id) ?>)</code> ≠ <code>session_user_id (<?= $_SESSION['current_user_id'] ?>)</code>
                        </div>

                        <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin-top: 15px; border-left: 4px solid #ffc107;">
                            <strong>🔒 Keamanan:</strong><br>
                            Server melakukan pengecekan ownership sebelum query database. 
                            Meskipun ID valid, akses ditolak karena bukan milik user yang login.
                        </div>
                    <?php elseif ($access_granted): ?>
                        <div class="alert alert-success">
                            <strong>✓ AKSES DIIZINKAN</strong><br>
                            Anda mengakses data Anda sendiri. Validasi ownership berhasil.
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
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <strong>✗ DATA TIDAK DITEMUKAN</strong><br>
                            Student ID tidak ada dalam database atau bukan milik Anda.
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="info-box" style="margin-top: 30px;">
                <h4>🔍 Perbandingan</h4>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Aspek</th>
                            <th>Vulnerable</th>
                            <th>Safe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Ownership Check</strong></td>
                            <td style="background: #fef5f5; color: #e74c3c;">❌ Tidak ada</td>
                            <td style="background: #f0fff0; color: #27ae60;">✅ Ada validasi</td>
                        </tr>
                        <tr>
                            <td><strong>Query Validation</strong></td>
                            <td style="background: #fef5f5; color: #e74c3c;">❌ Langsung query by ID</td>
                            <td style="background: #f0fff0; color: #27ae60;">✅ WHERE id=? AND user_id=?</td>
                        </tr>
                        <tr>
                            <td><strong>Access to Other Users</strong></td>
                            <td style="background: #fef5f5; color: #e74c3c;">❌ Bisa akses</td>
                            <td style="background: #f0fff0; color: #27ae60;">✅ Access Denied</td>
                        </tr>
                        <tr>
                            <td><strong>Error Message</strong></td>
                            <td style="background: #fef5f5; color: #e74c3c;">❌ Tampilkan data</td>
                            <td style="background: #f0fff0; color: #27ae60;">✅ 403 Forbidden</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 30px; text-align: center;">
                <a href="profile_vulnerable.php?id=101" class="btn btn-danger">← Lihat Versi Vulnerable</a>
                <a href="data.php" class="btn btn-info">Lihat Semua Data →</a>
            </div>
        </div>
    </div>
</body>
</html>