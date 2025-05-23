<?php
session_start();
header('Content-Type: text/plain');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$chat_id = (int)($_POST['chat_id'] ?? 0);
$message = trim($_POST['message'] ?? '');

if (!$chat_id || $message === '') {
    http_response_code(400);
    echo "Missing chat_id or message";
    exit;
}

try {
    $pdo = new PDO(
        "pgsql:host=localhost;port=5432;dbname=bibliobros",
        "postgres",
        "klk"
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo "DB error: " . $e->getMessage();
    exit;
}

// Verificar que el usuario pertenece al chat
$stmt = $pdo->prepare("
    SELECT r.mentor_id, r.mentee_id
      FROM chats c
      JOIN requests r ON c.request_id = r.id
     WHERE c.id = :chat_id
");
$stmt->execute(['chat_id' => $chat_id]);
$chat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$chat || !in_array($user_id, [$chat['mentor_id'], $chat['mentee_id']])) {
    http_response_code(403);
    echo "Access denied";
    exit;
}

// Insertar mensaje
$insert = $pdo->prepare("
    INSERT INTO messages (chat_id, sender_id, content)
    VALUES (:chat_id, :sender_id, :content)
");
$insert->execute([
    'chat_id'   => $chat_id,
    'sender_id' => $user_id,
    'content'   => $message
]);

echo "OK";
