<?php
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    redirect('login_vulnerable.php');
}

$user = $_SESSION['user'];
$is_vulnerable = isset($_SESSION['vulnerable']) ? $_SESSION['vulnerable'] : false;

// Logout handler
if (isset($_GET['logout'])) {
    session_destroy();
    redirect('login_vulnerable.php');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SQL Injection Demo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="lab-page">
            <div class="lab-header">
                <h1 class="lab-title">📊 Dashboard</h1>
                <a href="?logout=1" class="btn btn-danger">Logout</a>
            </div>

            <div class="alert <?= $is_vulnerable ? 'alert-danger' : 'alert-success' ?>">
                <?php if ($is_vulnerable): ?>
                    <strong>⚠️ VULNERABLE LOGIN DETECTED!</strong><br>
                    Anda login menggunakan metode yang rentan terhadap SQL Injection.
                <?php else: ?>
                    <strong>✅ SECURE LOGIN</strong><br>
                    Anda login menggunakan metode yang aman dengan Prepared Statements.
                <?php endif; ?>
            </div>

            <div class="demo-section">
                <div class="demo-box" style="border-color: #3498db; background: #f0f8ff;">
                    <h3>👤 Informasi User</h3>
                    <table class="data-table">
                        <tr>
                            <td style="width: 150px;"><strong>User ID:</strong></td>
                            <td><?= $user['id'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>Username:</strong></td>
                            <td><?= safe_output($user['username']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Role:</strong></td>
                            <td>
                                <span style="background: <?= $user['role'] === 'admin' ? '#e74c3c' : '#3498db' ?>; color: white; padding: 5px 15px; border-radius: 20px; font-weight: 600;">
                                    <?= strtoupper($user['role']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Login Method:</strong></td>
                            <td>
                                <?php if ($is_vulnerable): ?>
                                    <span style="color: #e74c3c; font-weight: 600;">❌ Vulnerable (String Concatenation)</span>
                                <?php else: ?>
                                    <span style="color: #27ae60; font-weight: 600;">✅ Safe (Prepared Statements)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php if ($user['role'] === 'admin'): ?>
                    <div class="demo-box" style="border-color: #e74c3c; background: #fef5f5;">
                        <h3>🔑 Admin Access</h3>
                        <p style="color: #e74c3c; font-weight: 600;">
                            ⚠️ Anda memiliki hak akses ADMIN! 
                        </p>
                        <p>Jika login ini hasil dari SQL Injection, maka attacker berhasil melakukan privilege escalation.</p>
                        
                        <div style="margin-top: 20px; padding: 15px; background: #fff; border-radius: 5px;">
                            <h4>📋 Daftar Semua User (Admin Only)</h4>
                            <?php
                            $all_users = $pdo->query("SELECT id, username, role FROM users")->fetchAll();
                            ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_users as $u): ?>
                                        <tr>
                                            <td><?= $u['id'] ?></td>
                                            <td><?= safe_output($u['username']) ?></td>
                                            <td><?= $u['role'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="info-box" style="margin-top: 30px;">
                <h4>💡 Penjelasan</h4>
                <?php if ($is_vulnerable): ?>
                    <p><strong style="color: #e74c3c;">Risiko SQL Injection:</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Attacker bisa login tanpa kredensial yang valid</li>
                        <li>Bisa mengakses data user lain</li>
                        <li>Privilege escalation: user biasa → admin</li>
                        <li>Manipulasi atau penghapusan data database</li>
                    </ul>
                <?php else: ?>
                    <p><strong style="color: #27ae60;">Keamanan Prepared Statements:</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Input user dipisahkan dari struktur query SQL</li>
                        <li>Karakter khusus otomatis di-escape</li>
                        <li>Tidak mungkin mengubah logika query</li>
                        <li>Password di-hash dengan bcrypt/argon2</li>
                    </ul>
                <?php endif; ?>
            </div>

            <div style="margin-top: 30px; text-align: center;">
                <a href="../index.php" class="btn btn-secondary">← Kembali ke Home</a>
                <?php if ($is_vulnerable): ?>
                    <a href="login_safe.php" class="btn btn-success">Coba Login Aman →</a>
                <?php else: ?>
                    <a href="login_vulnerable.php" class="btn btn-danger">Coba Login Vulnerable →</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

