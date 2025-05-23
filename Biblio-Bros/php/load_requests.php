<?php
// load_requests.php – devuelve TODAS las requests pendientes de un subject
session_start();
header('Content-Type: application/json');

// 1) Solo requiero sesión, no rol
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["error"=>"Access denied"]);
    exit;
}

$subject = trim($_GET['subject'] ?? '');
if ($subject === '') {
    http_response_code(400);
    echo json_encode(["error"=>"Missing subject"]);
    exit;
}

try {
    $pdo = new PDO(
      "pgsql:host=localhost;port=5432;dbname=bibliobros",
      "postgres",
      "klk"
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2) Obtengo TODAS las peticiones pendientes (mentor_id IS NULL)
    $stmt = $pdo->prepare("
        SELECT
          r.id,
          r.message,
          u.fullname AS mentee_name
        FROM requests r
        JOIN users u ON r.mentee_id = u.id
        WHERE r.subject = :subject
          AND r.status = 'pending'
          AND r.mentor_id IS NULL
        ORDER BY r.id DESC
    ");
    $stmt->execute(['subject'=>$subject]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error"=>"DB error: ".$e->getMessage()]);
}
