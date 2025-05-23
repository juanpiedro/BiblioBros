<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  // No hay sesión activa
  echo json_encode([
    'authenticated' => false
  ]);
  exit;
}

// Tienes sesión; devuelve los datos que necesites
echo json_encode([
  'authenticated' => true,
  'user_id'       => $_SESSION['user_id'],
  'role'          => $_SESSION['role'],
  'fullname'      => $_SESSION['fullname'] ?? ''
]);
