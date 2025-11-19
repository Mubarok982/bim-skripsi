<?php
session_start();

// 1. Hapus Session
session_unset();
session_destroy();

// 2. Redirect Paksa Menggunakan JavaScript
// (Cara ini membypass masalah header PHP)
?>
<!DOCTYPE html>
<html>
<body>
    <script>
        // Hapus history agar tidak bisa di-back
        window.location.replace("login.php");
    </script>
</body>
</html>