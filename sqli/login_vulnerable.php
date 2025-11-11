<?php
require_once '../config.php';

$result = null;
$query_executed = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // VULNERABLE: String concatenation langsung tanpa sanitasi
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $query_executed = $query;
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user'] = $user;
        $_SESSION['vulnerable'] = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Injection - Vulnerable Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="lab-page">
            <div class="lab-header">
                <h1 class="lab-title">🗄️ SQL Injection - Vulnerable Version</h1>
                <a href="../index.php" class="btn btn-secondary">← Kembali ke Home</a>
            </div>

            <div class="info-box">
                <h4>Konsep SQL Injection</h4>
                <p>SQL Injection terjadi ketika input user digabungkan langsung ke query SQL tanpa sanitasi, memungkinkan attacker memodifikasi struktur query.</p>
            </div>

            <div class="demo-section">
                <div class="demo-box vulnerable">
                    <div class="demo-header">
                        <span style="font-size: 1.5rem;">❌</span>
                        <h3>Versi Vulnerable</h3>
                    </div>

                    <div class="code-box">
                        $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
                    </div>

                    <form method="POST">
                        <div class="form-group">
                            <label>Username:</label>
                            <input type="text" name="username" class="form-control" placeholder="Coba: admin' OR '1'='1" required>
                        </div>

                        <div class="form-group">
                            <label>Password:</label>
                            <input type="text" name="password" class="form-control" placeholder="Coba: ' OR '1'='1" required>
                        </div>

                        <button type="submit" class="btn btn-danger" style="width: 100%;">Login (Vulnerable)</button>
                    </form>

                    <div class="payload-list">
                        <p><strong>Payload untuk dicoba:</strong></p>
                        <ul>
                            <li><code>admin' OR '1'='1</code></li>
                            <li><code>admin' --</code></li>
                            <li><code>' OR 1=1 --</code></li>
                            <li><code>admin' #</code></li>
                        </ul>
                    </div>
                </div>

                <div class="demo-box" style="border-color: #3498db; background: #f0f8ff;">
                    <div class="demo-header">
                        <span style="font-size: 1.5rem;">ℹ️</span>
                        <h3>Penjelasan</h3>
                    </div>
                    <p><strong>Mengapa Vulnerable?</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Input langsung digabung ke query (string concatenation)</li>
                        <li>Tidak ada validasi atau escape karakter khusus</li>
                        <li>Attacker bisa menutup quote dan menambah kondisi SQL</li>
                        <li>Bisa bypass authentication tanpa password yang benar</li>
                    </ul>

                    <p style="margin-top: 15px;"><strong>Dampak:</strong></p>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>🔓 Bypass authentication</li>
                        <li>📊 Data leakage</li>
                        <li>💾 Database manipulation</li>
                        <li>⚡ Privilege escalation</li>
                    </ul>
                </div>
            </div>

            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <div class="result-box <?= ($result && $result->num_rows > 0) ? 'danger' : 'success' ?>">
                    <h4>Hasil Eksekusi:</h4>
                    
                    <p><strong>Query yang dieksekusi:</strong></p>
                    <div class="code-box">
                        <?= safe_output($query_executed) ?>
                    </div>

                    <?php if ($result && $result->num_rows > 0): ?>
                        <div class="alert alert-danger">
                            <strong>⚠️ SQL INJECTION BERHASIL!</strong><br>
                            Login berhasil meskipun password salah. Attacker dapat bypass authentication!
                        </div>

                        <p><strong>Data yang dikembalikan:</strong></p>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $result->data_seek(0);
                                while($row = $result->fetch_assoc()): 
                                ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>
                                        <td><?= $row['username'] ?></td>
                                        <td><?= $row['role'] ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-success">
                            ✓ Login gagal. Username atau password salah.
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div style="margin-top: 30px; text-align: center;">
                <a href="login_safe.php" class="btn btn-success">Lihat Versi Aman (Safe) →</a>
            </div>
        </div>
    </div>
</body>
</html>