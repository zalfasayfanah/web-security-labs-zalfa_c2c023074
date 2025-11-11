<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Security Lab - Keamanan Data</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="shield-icon">🛡️</div>
            <h1>Virtual Security Lab</h1>
            <p class="subtitle">Platform Pembelajaran Interaktif Keamanan Aplikasi Web</p>
        </header>

        <div class="lab-grid">
            <!-- SQL Injection Lab -->
            <div class="lab-card sqli">
                <div class="lab-icon">🗄️</div>
                <h2>SQL Injection</h2>
                <p>Pelajari bagaimana input user dapat memodifikasi query SQL dan cara mencegahnya dengan prepared statements.</p>
                <div class="button-group">
                    <a href="sqli/login_vulnerable.php" class="btn btn-danger">Vulnerable Version</a>
                    <a href="sqli/login_safe.php" class="btn btn-success">Safe Version</a>
                </div>
            </div>

            <!-- XSS Lab -->
            <div class="lab-card xss">
                <div class="lab-icon">💻</div>
                <h2>Cross-Site Scripting (XSS)</h2>
                <p>Simulasi stored XSS dan reflected XSS, serta teknik mitigasi dengan output encoding.</p>
                <div class="button-group">
                    <a href="xss/comment_vulnerable.php" class="btn btn-warning">Vulnerable Version</a>
                    <a href="xss/comment_safe.php" class="btn btn-success">Safe Version</a>
                </div>
            </div>

            <!-- File Upload Lab -->
            <div class="lab-card upload">
                <div class="lab-icon">📤</div>
                <h2>File Upload Vulnerability</h2>
                <p>Pahami risiko upload file berbahaya dan implementasi validasi yang benar.</p>
                <div class="button-group">
                    <a href="upload/upload_vulnerable.php" class="btn btn-purple">Vulnerable Version</a>
                    <a href="upload/upload_safe.php" class="btn btn-success">Safe Version</a>
                </div>
            </div>

            <!-- Broken Access Control Lab -->
            <div class="lab-card bac">
                <div class="lab-icon">🔒</div>
                <h2>Broken Access Control (IDOR)</h2>
                <p>Eksploitasi IDOR dan belajar implementasi authorization yang tepat.</p>
                <div class="button-group">
                    <a href="bac/profile_vulnerable.php?id=101" class="btn btn-info">Vulnerable Version</a>
                    <a href="bac/profile_safe.php?id=101" class="btn btn-success">Safe Version</a>
                </div>
            </div>
        </div>

        <div class="warning-box">
            <div class="warning-icon">⚠️</div>
            <div>
                <h3>Perhatian - Etika Penggunaan</h3>
                <p>Lab ini hanya untuk tujuan edukasi. Jangan gunakan pengetahuan ini untuk menyerang sistem tanpa izin. Semua pengujian harus dilakukan di lingkungan lokal/lab yang terisolasi.</p>
            </div>
        </div>

        <footer class="footer">
            <p>🧪 Virtual Security Lab - Praktikum Keamanan Data</p>
            <p class="small">Dibuat untuk tujuan edukasi. Gunakan dengan bertanggung jawab.</p>
        </footer>
    </div>
</body>
</html>