<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
  http_response_code(403);
  echo json_encode(["error"=>"Unauthorized"]);
  exit;
}
$chat_id = (int)($_GET['chat_id'] ?? 0);
if (!$chat_id) {
  http_response_code(400);
  echo json_encode(["error"=>"Missing chat_id"]);
  exit;
}

try {
  $pdo = new PDO("pgsql:host=localhost;port=5432;dbname=bibliobros","postgres","klk");
  $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

  // Verifica que el usuario pertenece al chat
  $chk = $pdo->prepare("
    SELECT r.mentor_id, r.mentee_id
    FROM chats c
    JOIN requests r ON c.request_id = r.id
    WHERE c.id = :chat_id
  ");
  $chk->execute(['chat_id'=>$chat_id]);
  $row = $chk->fetch(PDO::FETCH_ASSOC);
  if (!$row || !in_array($_SESSION['user_id'],[$row['mentor_id'],$row['mentee_id']])) {
    http_response_code(403);
    echo json_encode(["error"=>"Access denied"]);
    exit;
  }

  // Carga mensajes
  $stmt = $pdo->prepare("
    SELECT sender_id, content, timestamp
    FROM messages
    WHERE chat_id = :chat_id
    ORDER BY timestamp ASC
  ");
  $stmt->execute(['chat_id'=>$chat_id]);
  $msgs = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Formatea
  $out = array_map(fn($m) => [
    "text"   => $m['content'],
    "time"   => date("H:i",strtotime($m['timestamp'])),
    "sender" => $m['sender_id']==$_SESSION['user_id']?'me':'other'
  ], $msgs);

  echo json_encode($out);

} catch(Exception $e) {
  http_response_code(500);
  echo json_encode(["error"=>"DB error"]);
}
