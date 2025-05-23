<?php
session_start();
header('Content-Type: application/json');

try {
    // Conexión por TCP a localhost y autenticación md5
    $pdo = new PDO(
      "pgsql:host=localhost;port=5432;dbname=bibliobros",
      "postgres",
      "klk"
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $input    = json_decode(file_get_contents('php://input'), true);
    $email    = trim($input['email']    ?? '');
    $password = trim($input['password'] ?? '');

    if (!$email || !$password) {
        echo json_encode(['success'=>false,'message'=>'Email and password are required.']);
        exit;
    }

    $stmt = $pdo->prepare(
      "SELECT id, fullname, password_hash 
         FROM users 
        WHERE email = :email"
    );
    $stmt->execute(['email'=>$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        echo json_encode(['success'=>false,'message'=>'Invalid credentials.']);
        exit;
    }

    $_SESSION['user_id']  = (int)$user['id'];
    $_SESSION['fullname'] = $user['fullname'];

    echo json_encode(['success'=>true,'fullname'=>$user['fullname']]);
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'message'=>'DB error: '.$e->getMessage()]);
}
