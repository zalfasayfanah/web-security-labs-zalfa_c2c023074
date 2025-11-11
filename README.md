# web-security-labs-zalfa_c2c023074
1. SQL Injection
   a. Rentan
   jika dimasukkan
   ●	Username: admin ‘ OR ‘ 1’=’1 
   ●	Password: ‘ OR ‘ 1’=’1
   maka,
   Hasilnya Masuk, karena perintah tersebut merupakan ekspresi OR '1'='1' selalu bernilai TRUE,      kondisi WHERE menjadi selalu terpenuhi → query mengembalikan satu atau lebih baris user            (seringkali baris pertama, mis. admin) → aplikasi mengira kredensial valid → berhasil login.
   b. Aman
   jika dimasukkan
   ●	Username: admin ‘ OR ‘ 1’=’1 
   ●	Password: ‘ OR ‘ 1’=’1
   maka,
   Hasilnya Pada versi aman (menggunakan prepared statements) payload SQL injection admin' OR         '1'='1 tidak berhasil melakukan bypass authentication
3. XSS
   a. Rentan
   jika dimasukkan
   Username: <script>alert('XSS Test')</script>
   Komentar: ini komentar biasa
   maka,
   Payload tersimpan di database
   Saat halaman di-refresh, muncul alert box "XSS Test"
   Script dieksekusi setiap kali halaman dimuat (Stored XSS)
   b. Aman
   jika dimasukkan
   Username: <script>alert('XSS Test')</script>
   Komentar: ini komentar biasa
   maka,
   Payload disimpan ke database	
   Output ditampilkan sebagai: &lt;script&gt;alert('XSS Test')&lt;/script&gt;
   Browser menampilkan text literal, BUKAN executable code
   Alert box TIDAK muncul
5. Upload File
   a. Rentan
   jika dimasukkan
   file shell.php
   maka,
   menampilkan username system (menunjukkan RCE berhasil). artinya semua type file bisa diterima
   b. Aman
   jika dimasukkan
   file shell.php
   maka,
   Upload shell.php ditolak (ekstensi tidak masuk whitelist). karena ada batasan type file yang     ditentukan.
7. BAC
   a. Rentan
   jika dimasukkan
   id user 102
   maka,
   Semuanya langsung bisa ke akses pengguna berhasil mengakses data user lain hanya dengan          mengganti parameter id pada URL
   b. Aman
   jika dimasukkan
   id user 102
   maka,
   halaman yang menolak akses karena server melakukan ownership check
