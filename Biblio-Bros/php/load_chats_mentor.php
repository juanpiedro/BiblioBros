<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
  http_response_code(403);
  echo json_encode(["error"=>"Unauthorized"]);
  exit;
}
$mentor = (int)$_SESSION['user_id'];

try {
  $pdo = new PDO("pgsql:host=localhost;port=5432;dbname=bibliobros","postgres","klk");
  $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

  $stmt = $pdo->prepare("
    SELECT c.id AS chat_id, r.subject, u.fullname AS mentee_name
    FROM chats c
    JOIN requests r ON c.request_id = r.id
    JOIN users u ON r.mentee_id = u.id
    WHERE r.mentor_id = :mentor AND r.status = 'accepted'
    ORDER BY c.id DESC
  ");
  $stmt->execute(['mentor'=>$mentor]);
  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch(Exception $e) {
  http_response_code(500);
  echo json_encode(["error"=>"DB error"]);
}
