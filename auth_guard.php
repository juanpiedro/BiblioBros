<?php
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: Toplogin.php');
    exit;
}
