<?php
// logout.php
session_start();
session_destroy();              // ❌ Cierra la sesión
header("Location: Toplogin.html");  // 🔄 Redirige al login
exit;