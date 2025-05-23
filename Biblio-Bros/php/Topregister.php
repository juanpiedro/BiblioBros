<?php
session_start();
header('Content-Type: application/json');

try {
    // 1) Conexión vía TCP a localhost:5432 con usuario/clave
    $pdo = new PDO(
        "pgsql:host=localhost;port=5432;dbname=bibliobros",
        "postgres",
        "klk"
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2) Leemos JSON del cuerpo
    $input = json_decode(file_get_contents('php://input'), true);
    $fullname   = trim($input['fullname']           ?? '');
    $email      = trim($input['email']              ?? '');
    $university = trim($input['university']         ?? '');
    $password   = trim($input['password']           ?? '');
    $confirm    = trim($input['confirm-password']   ?? '');

    // 3) Validaciones básicas
    if (!$fullname || !$email || !$university || !$password || !$confirm) {
        throw new Exception("All fields are required.");
    }
    if ($password !== $confirm) {
        throw new Exception("Passwords do not match.");
    }

    // 4) Comprobar email duplicado
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Email already registered.");
    }

    // 5) Insertar usuario (sin columna `role`)
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $pdo->prepare("
        INSERT INTO users (fullname, email, password_hash, university)
        VALUES (:fullname, :email, :hash, :university)
    ");
    $insert->execute([
        'fullname'   => $fullname,
        'email'      => $email,
        'hash'       => $hash,
        'university' => $university
    ]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'DB error: '.$e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
