<?php
require_once '../config.php';

$result = null;
$user_found = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // SAFE: Menggunakan Prepared Statements
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        $_SESSION['vulnerable'] = false;
        $user_found = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Injection - Safe Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="lab-page">
            <div class="lab-header">
                <h1 class="lab-title">🗄️ SQL Injection - Safe Version</h1>
                <a href="../index.php" class="btn btn-secondary">← Kembali ke Home</a>
            </div>

            <div class="info-box">
                <h4>Mitigasi SQL Injection</h4>
                <p>Menggunakan Prepared Statements dengan parameter binding untuk memisahkan kode SQL dari data user, sehingga input diperlakukan sebagai data murni, bukan bagian dari query.</p>
            </div>

            <div class="demo-section">
                <div class="demo-box safe">
                    <div class="demo-header">
                        <span style="font-size: 1.5rem;">✅</span>
                        <h3>Versi Aman (Safe)</h3>
                    </div>

                    <div class="code-box">
                        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");<br>
                        $stmt->execute([$username]);<br>
                        if ($user && password_verify($password, $user['password'])) { ... }
                    </div>

                    <form method="POST">
                        <div class="form-group">
                            <label>Username:</label>
                            <input type="text" name="username" class="form-control" placeholder="Gunakan: alice" required>
                        </div>

                        <div class="form-group">
                            <label>Password:</label>
                            <input type="password" name="password" class="form-control" placeholder="Gunakan: password" required>
                        </div>

                        <button type="submit" class="btn btn-success" style="width: 100%;">Login (Safe)</button>
                    </form>

                    <div class="payload-list">
                        <p><strong>Kredensial Valid untuk Testing:</strong></p>
                        <ul>
                            <li>Username: <code>alice</code> | Password: <code>password</code></li>
                            <li>Username: <code>bob</code> | Password: <code>password</code></li>
                            <li>Username: <code>admin</code> | Password: <code>password</code></li>
                        </ul>
                    </div>
                </div>

                <div class="demo-box" style="border-color: #27ae60; background: #f0fff0;">
                    <div class="demo-header">
                        <span style="font-size: 1.5rem;">🛡️</span>
                        <h3>Keamanan</h3>
                    </div>
                    <p><strong>Mengapa Aman?</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li><strong>Prepared Statements:</strong> Query dan data dipisahkan</li>
                        <li><strong>Parameter Binding:</strong> Input otomatis di-escape</li>
                        <li><strong>Password Hashing:</strong> Password disimpan sebagai hash</li>
                        <li><strong>Type Safety:</strong> Parameter diperlakukan sesuai tipe data</li>
                    </ul>

                    <p style="margin-top: 15px;"><strong>Best Practices:</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>✅ Selalu gunakan PDO atau MySQLi prepared statements</li>
                        <li>✅ Never trust user input</li>
                        <li>✅ Hash password dengan <code>password_hash()</code></li>
                        <li>✅ Validasi input di server-side</li>
                        <li>✅ Principle of least privilege untuk database user</li>
                    </ul>
                </div>
            </div>

            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <div class="result-box <?= $user_found ? 'success' : 'danger' ?>">
                    <h4>Hasil Eksekusi:</h4>
                    
                    <p><strong>Query yang dieksekusi (prepared):</strong></p>
                    <div class="code-box">
                        SELECT * FROM users WHERE username = ?<br>
                        <em style="color: #27ae60;">Parameter: "<?= safe_output($username) ?>" (otomatis di-escape)</em>
                    </div>

                    <?php if ($user_found): ?>
                        <div class="alert alert-success">
                            <strong>✓ LOGIN BERHASIL!</strong><br>
                            Selamat datang, <?= safe_output($user['username']) ?>! (Role: <?= $user['role'] ?>)
                        </div>
                        <p>Input diperlakukan sebagai data, bukan sebagai bagian dari kode SQL. Payload SQL Injection tidak akan berhasil.</p>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <strong>✗ LOGIN GAGAL</strong><br>
                            Username atau password salah. Payload SQL Injection tidak berpengaruh karena menggunakan prepared statements.
                        </div>
                        <p>Coba masukkan payload seperti <code>admin' OR '1'='1</code> - tetap tidak akan berhasil bypass!</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div style="margin-top: 30px; text-align: center;">
                <a href="login_vulnerable.php" class="btn btn-danger">← Lihat Versi Vulnerable</a>
            </div>
        </div>
    </div>
</body>
</html>