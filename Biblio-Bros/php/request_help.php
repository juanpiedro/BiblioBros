<?php
// request_help.php
session_start();

// 1) Sólo requiero user_id, no role
if (!isset($_SESSION['user_id'])) {
    header("Location: Toplogin.html");
    exit;
}
$mentee_id = (int)$_SESSION['user_id'];

// 2) Leo POST (form clásico)
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
if ($subject === '' || $message === '') {
    die("Faltan datos.");
}

// 3) Conexión PDO
try {
    $pdo = new PDO(
      "pgsql:host=localhost;port=5432;dbname=bibliobros",
      "postgres",
      "klk"
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// 4) Inserto la request
$stmt = $pdo->prepare("
    INSERT INTO requests (subject, mentee_id, message, status)
    VALUES (:subject, :mentee_id, :message, 'pending')
");
$stmt->execute([
    'subject'   => $subject,
    'mentee_id' => $mentee_id,
    'message'   => $message
]);

// 5) Redirijo de vuelta al mentee
header("Location: Topsubject_mentee.html?subject=".urlencode($subject));
exit;
