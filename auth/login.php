<?php
session_start();

// --- LOGIKA LOGOUT (Ditaruh Paling Atas) ---
// Cek apakah ada perintah ?action=logout di URL
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    
    // 1. Kosongkan variabel session
    $_SESSION = array();

    // 2. Hapus cookie session browser (PENTING)
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // 3. Hancurkan session di server
    session_destroy();

    // 4. Redirect kembali ke login.php (bersih tanpa query string)
    header("Location: login.php");
    exit();
}
// -------------------------------------------

include "../admin/db.php"; 

$error = "";

// Cek jika user sudah login (dan bukan sedang logout), langsung redirect
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'operator': header("Location: ../admin/home_admin.php"); exit();
        case 'dosen': header("Location: ../dosen/home_dosen.php"); exit();
        case 'mahasiswa': header("Location: ../mahasiswa/home_mahasiswa.php"); exit();
        case 'tata_usaha': header("Location: ../home_tu.php"); exit(); 
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM mstr_akun WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['foto'] = $row['foto'];

            switch ($row['role']) {
                case 'operator':
                    $_SESSION['admin_username'] = $row['username']; 
                    $_SESSION['nama_admin'] = $row['nama'];
                    header("Location: ../admin/home_admin.php");
                    break;
                case 'dosen':
                    $_SESSION['nip'] = $row['username'];
                    header("Location: ../home_dosen.php");
                    break;
                case 'mahasiswa':
                    $_SESSION['npm'] = $row['username'];
                    header("Location: ../home_mahasiswa.php"); 
                    break;
                case 'tata_usaha':
                    $_SESSION['username_tu'] = $row['username'];
                    header("Location: ../home_tu.php");
                    break;
                default:
                    $error = "Role pengguna tidak dikenali.";
                    session_destroy();
            }
            exit();
        } else {
            $error = "Password salah.";
        }
    } else {
        $error = "Username tidak ditemukan.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Sistem Monitoring Skripsi</title>
    <link rel="stylesheet" href="../style1.css"> 
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .judul { text-align: center; margin-bottom: 20px; font-weight: bold; color: #333; }
        .login-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .login-box h3 { text-align: center; margin-bottom: 20px; color: #333; }
        .input-field { margin-bottom: 15px; }
        .input-field label { display: block; margin-bottom: 5px; color: #666; font-size: 14px; } 
        .input-field input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .login-button { width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; transition: background 0.3s;}
        .login-button:hover { background-color: #0056b3; }
        .message { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-size: 14px; }
        .links { margin-top: 15px; text-align: center; font-size: 13px; }
        .links a { color: #007bff; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <div class="judul">
        <h2>SISTEM MONITORING SKRIPSI</h2>
        <h4>UNIVERSITAS MUHAMMADIYAH MAGELANG</h4>
    </div>

    <div class="login-box">
        <h3>Silakan Login</h3>

        <?php if (!empty($error)): ?>
            <div class="message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="input-field">
                <label for="username">Username / NPM / NIDK</label>
                <input type="text" name="username" id="username" placeholder="Masukkan Username" required autocomplete="off">
            </div>
            
            <div class="input-field">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Masukkan Password" required>
            </div>
            
            <button type="submit" class="login-button">Masuk</button>
        </form>

        <div class="links">
            Lupa Password? <a href="../lupa_password.php">Klik disini</a>
            <br><br>
            Kembali ke <a href="../index.php">Beranda</a>
        </div>
    </div>

</body>
</html>