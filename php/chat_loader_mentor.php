<?php
/*
 * chat_loader_mentor.php – Devuelve solo los chats del mentor (activos y cerrados).
 */

require_once __DIR__ . '/../auth_guard.php';
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

$mentor_id = (int) $_SESSION['user_id'];

try {
  // Active chats
  $stmt = $pdo->prepare("
    SELECT c.id AS chat_id,
           u.fullname AS mentee_name,
           s.name AS subject,
           c.active
      FROM chats c
      JOIN requests r ON r.id = c.request_id
      JOIN users u ON u.id = r.mentee_id
      JOIN subjects s ON s.id = r.subject_id
     WHERE r.mentor_id = :uid AND c.active = TRUE
     ORDER BY c.created_at DESC
  ");
  $stmt->execute([':uid' => $mentor_id]);
  $activeChats = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Closed chats
  $stmt = $pdo->prepare("
    SELECT c.id AS chat_id,
           u.fullname AS mentee_name,
           s.name AS subject,
           DATE_FORMAT(c.created_at, '%d %b %Y %H:%i') AS closed_at
      FROM chats c
      JOIN requests r ON r.id = c.request_id
      JOIN users u ON u.id = r.mentee_id
      JOIN subjects s ON s.id = r.subject_id
     WHERE r.mentor_id = :uid AND c.active = FALSE
     ORDER BY c.created_at DESC
  ");
  $stmt->execute([':uid' => $mentor_id]);
  $closedChats = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
    'requests' => [], // Estructura esperada por el JS aunque vacía
    'active_chats' => $activeChats,
    'closed_chats' => $closedChats
  ]);

} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
