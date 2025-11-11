<?php
require_once '../config.php';

// Simulasi: User yang sedang login
$_SESSION['current_user_id'] = '101';
$_SESSION['current_user_name'] = 'Alice';

// Fetch all student data
$stmt = $pdo->query("SELECT * FROM student_data ORDER BY student_id ASC");
$all_students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Student Data - BAC Demo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="lab-page">
            <div class="lab-header">
                <h1 class="lab-title">📊 Data Semua Mahasiswa</h1>
                <a href="../index.php" class="btn btn-secondary">← Kembali ke Home</a>
            </div>

            <div class="info-box">
                <h4>Testing IDOR dengan Semua ID</h4>
                <p>Halaman ini menampilkan semua student ID yang ada di database. Anda bisa klik link vulnerable/safe untuk melihat perbedaan access control.</p>
            </div>

            <!-- Current User Info -->
            <div style="background: #d4edda; padding: 15px; border-radius: 10px; margin-bottom: 25px; border-left: 5px solid #28a745;">
                <strong>👤 Logged in as:</strong> <?= $_SESSION['current_user_name'] ?> (ID: <?= $_SESSION['current_user_id'] ?>)
            </div>

            <!-- Student Data Table -->
            <div style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h3 style="margin-bottom: 20px;">📋 Daftar Mahasiswa (<?= count($all_students) ?>)</h3>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Nama</th>
                            <th>Jurusan</th>
                            <th>Grade</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_students as $student): 
                            $is_own_data = ($student['student_id'] === $_SESSION['current_user_id']);
                        ?>
                            <tr style="<?= $is_own_data ? 'background: #d4edda;' : '' ?>">
                                <td>
                                    <strong><?= $student['student_id'] ?></strong>
                                    <?= $is_own_data ? ' <span style="color: #27ae60;">(You)</span>' : '' ?>
                                </td>
                                <td><?= safe_output($student['name']) ?></td>
                                <td><?= safe_output($student['major']) ?></td>
                                <td>
                                    <strong style="font-size: 1.1rem; color: #27ae60;">
                                        <?= safe_output($student['grade']) ?>
                                    </strong>
                                </td>
                                <td>
                                    <a href="profile_vulnerable.php?id=<?= $student['student_id'] ?>" 
                                       class="btn btn-danger" 
                                       style="padding: 5px 12px; font-size: 0.85rem; margin-right: 5px;">
                                        Vulnerable
                                    </a>
                                    <a href="profile_safe.php?id=<?= $student['student_id'] ?>" 
                                       class="btn btn-success" 
                                       style="padding: 5px 12px; font-size: 0.85rem;">
                                        Safe
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Instructions -->
            <div class="demo-section" style="margin-top: 30px;">
                <div class="demo-box vulnerable">
                    <div class="demo-header">
                        <span style="font-size: 1.5rem;">❌</span>
                        <h3>Testing Vulnerable Version</h3>
                    </div>
                    <p><strong>Instruksi:</strong></p>
                    <ol style="margin-left: 20px; line-height: 1.8;">
                        <li>Klik tombol <strong>"Vulnerable"</strong> pada row manapun</li>
                        <li>Perhatikan bahwa Anda bisa akses data mahasiswa lain</li>
                        <li>Tidak ada validasi ownership</li>
                        <li>⚠️ Ini adalah kerentanan IDOR!</li>
                    </ol>
                </div>

                <div class="demo-box safe">
                    <div class="demo-header">
                        <span style="font-size: 1.5rem;">✅</span>
                        <h3>Testing Safe Version</h3>
                    </div>
                    <p><strong>Instruksi:</strong></p>
                    <ol style="margin-left: 20px; line-height: 1.8;">
                        <li>Klik tombol <strong>"Safe"</strong> pada row manapun</li>
                        <li>Jika bukan data Anda, akan muncul <strong>Access Denied</strong></li>
                        <li>Hanya bisa akses data sendiri (ID: <?= $_SESSION['current_user_id'] ?>)</li>
                        <li>✅ Validasi ownership berhasil!</li>
                    </ol>
                </div>
            </div>

            <!-- Comparison Table -->
            <div class="info-box" style="margin-top: 30px;">
                <h4>📊 Hasil Testing</h4>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Target ID</th>
                            <th>Vulnerable Result</th>
                            <th>Safe Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>101 (Alice - You)</strong></td>
                            <td style="background: #d4edda; color: #27ae60;">✓ Show data</td>
                            <td style="background: #d4edda; color: #27ae60;">✓ Show data</td>
                        </tr>
                        <tr>
                            <td><strong>102 (Bob - Others)</strong></td>
                            <td style="background: #fef5f5; color: #e74c3c;">⚠️ Show data (IDOR!)</td>
                            <td style="background: #d4edda; color: #27ae60;">✓ Access Denied</td>
                        </tr>
                        <tr>
                            <td><strong>103 (Charlie - Others)</strong></td>
                            <td style="background: #fef5f5; color: #e74c3c;">⚠️ Show data (IDOR!)</td>
                            <td style="background: #d4edda; color: #27ae60;">✓ Access Denied</td>
                        </tr>
                        <tr>
                            <td><strong>104 (Diana - Others)</strong></td>
                            <td style="background: #fef5f5; color: #e74c3c;">⚠️ Show data (IDOR!)</td>
                            <td style="background: #d4edda; color: #27ae60;">✓ Access Denied</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 30px; text-align: center;">
                <a href="profile_vulnerable.php?id=101" class="btn btn-danger">Profile Vulnerable</a>
                <a href="profile_safe.php?id=101" class="btn btn-success">Profile Safe</a>
            </div>
        </div>
    </div>
</body>
</html>