<?php

require_once __DIR__ . '/config.php'; // starts session and creates $pdo

if (isset($_SESSION['user_id'])) {
    header('Location: Topdashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare("
            SELECT id, fullname, password_hash
            FROM users
            WHERE email = :email
            LIMIT 1
        ");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = 'Invalid credentials.';
        } else {
            session_regenerate_id(true); // security best practice
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            header('Location: Topdashboard.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>BiblioBros – Login</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Custom Styles -->
    <link rel="stylesheet" href="assets/css/style.css" />
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- Dynamic Navbar -->
    <div id="navbar-placeholder"></div>

    <!-- Login Form -->
    <main class="container d-flex flex-column align-items-center justify-content-center flex-fill py-5">
        <div class="form-card">
            <h2 class="section-title text-center mb-4">Login to Your Account</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert" aria-live="assertive">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="Toplogin.php" method="post" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com"
                        value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required />
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••"
                        required />
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-xl">Login</button>
                </div>

                <p class="text-center">
                    Don't have an account?
                    <a href="Topregister.php" class="text-decoration-none">Register here</a>.
                </p>
            </form>
        </div>
    </main>

    <!-- Dynamic Modals -->
    <div id="modal-container"></div>

    <!-- Dynamic Footer -->
    <div id="footer-placeholder"></div>

    <!-- Bootstrap JS + Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Shared logic: navbar, footer, modals, authGuard… -->
    <script src="assets/js/main.js"></script>
</body>

</html>