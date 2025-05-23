<?php
// accept_request.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: Toplogin.html");
    exit;
}

$mentor_id = (int)$_SESSION['user_id'];
$request_id = (int)($_POST['request_id'] ?? 0);

if (!$request_id) {
    die("Missing request ID.");
}

try {
    $pdo = new PDO(
        "pgsql:host=localhost;port=5432;dbname=bibliobros",
        "postgres",
        "klk"
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection error: " . $e->getMessage());
}

// 1) Verificar que la solicitud está disponible
$stmt = $pdo->prepare("
    SELECT *
      FROM requests
     WHERE id = :id
       AND status = 'pending'
       AND mentor_id IS NULL
");
$stmt->execute(['id' => $request_id]);

if ($stmt->rowCount() === 0) {
    die("Request not available or already accepted.");
}

// 2) Asignar mentor y actualizar estado
$update = $pdo->prepare("
    UPDATE requests
       SET mentor_id = :mentor_id,
           status    = 'accepted'
     WHERE id = :id
");
$update->execute([
    'mentor_id' => $mentor_id,
    'id'        => $request_id
]);

// 3) Crear chat
$insertChat = $pdo->prepare("
    INSERT INTO chats (request_id) VALUES (:request_id)
");
$insertChat->execute(['request_id' => $request_id]);

// 4) Redirigir de vuelta
header("Location: Topsubject_mentor.html?subject=" . urlencode($_POST['subject'] ?? ''));
exit;
