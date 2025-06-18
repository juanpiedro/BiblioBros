<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php'; // start session and create PDO connection

// Fetch universities for the select dropdown
$stmt = $pdo->query("SELECT id, name FROM universities ORDER BY name");
$universities = $stmt->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $university_id = (int) ($_POST['university_id'] ?? 0);
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Validate form fields
    if (!$fullname || !$email || !$university_id || !$password || !$confirm) {
        $errors[] = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'The email is not valid.';
    } elseif ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    } else {
        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $check->execute(['email' => $email]);
        if ($check->fetchColumn() > 0) {
            $errors[] = 'This email is already registered.';
        }
    }

    if (empty($errors)) {
        // Hash the password securely
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert user record
        $ins = $pdo->prepare("
            INSERT INTO users (fullname, email, password_hash, university_id)
            VALUES (:fullname, :email, :hash, :uid)
        ");
        $ins->execute([
            'fullname' => $fullname,
            'email' => $email,
            'hash' => $hash,
            'uid' => $university_id
        ]);

        header('Location: Toplogin.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BiblioBros – Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/style.css" />
</head>

<body class="d-flex flex-column min-vh-100">
    <div id="navbar-placeholder"></div>

    <main class="container d-flex flex-column align-items-center justify-content-center flex-fill py-5">
        <div class="form-card">
            <h2 class="section-title text-center mb-4">Create Account</h2>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="post" novalidate>
                <div class="mb-3">
                    <label for="fullname" class="form-label">Full Name</label>
                    <input type="text" id="fullname" name="fullname" class="form-control"
                        value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>" required />
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required />
                </div>
                <div class="mb-3">
                    <label for="university_id" class="form-label">University</label>
                    <select id="university_id" name="university_id" class="form-select" required>
                        <option value="">-- Select a university --</option>
                        <?php foreach ($universities as $uni): ?>
                            <option value="<?= $uni['id'] ?>" <?= (isset($_POST['university_id']) && (int) $_POST['university_id'] === (int) $uni['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($uni['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required />
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                        required />
                </div>
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-secondary btn-xl">Register</button>
                </div>
                <p class="text-center">
                    Already have an account? <a href="Toplogin.php" class="text-decoration-none">Log in</a>.
                </p>
            </form>
        </div>
    </main>
    <div id="modal-container"></div>
    <div id="footer-placeholder"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Shared scripts (navbar, footer, modals, logout, authGuard…) -->
    <script src="assets/js/main.js"></script>
</body>

</html>