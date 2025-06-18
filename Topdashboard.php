<?php

require_once __DIR__ . '/auth_guard.php'; // starts session, connects PDO, checks auth

error_log('Session ID: ' . session_id());
error_log('User ID set: ' . ($_SESSION['user_id'] ?? 'null'));
// Fetch user info
$stmt = $pdo->prepare("
    SELECT fullname, university_id
    FROM users
    WHERE id = :uid
    LIMIT 1
");
$stmt->execute(['uid' => $_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: Toplogin.php');
    exit;
}

// Save in session if needed
$_SESSION['fullname'] = $user['fullname'];
$_SESSION['university_id'] = $user['university_id'];

// Fetch faculties for dropdown
$stmt2 = $pdo->prepare("
    SELECT id, name, description
    FROM faculties
    WHERE university_id = :uni
    ORDER BY name
");
$stmt2->execute(['uni' => $user['university_id']]);
$faculties = $stmt2->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>BiblioBros – Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
</head>

<body class="d-flex flex-column min-vh-100">

  <!-- Navbar -->
  <div id="navbar-placeholder"></div>

  <!-- Main Dashboard -->
  <main class="container py-5 flex-grow-1">
    <h2 class="section-title text-center mb-4">Dashboard</h2>
    <p class="text-center fs-5 text-muted">
      Welcome, <?= htmlspecialchars($user['fullname']) ?>!
    </p>

    <div class="card mb-4 p-4 position-relative">
      <h3 class="mb-3">Your Faculties</h3>

      <?php if (count($faculties) > 0): ?>
        <div class="row row-cols-2 g-3">
          <?php foreach ($faculties as $fac): ?>
            <div class="col-6">
              <a href="Topfaculty.php?faculty_id=<?= $fac['id'] ?>"
                 class="btn-faculty w-100 text-start p-3 d-block">
                <i class="bi bi-building me-2"></i>
                <span class="h6"><?= htmlspecialchars($fac['name']) ?></span>
                <?php if (!empty($fac['description'])): ?>
                  <small class="d-block text-muted mt-1">
                    <?= htmlspecialchars($fac['description']) ?>
                  </small>
                <?php endif; ?>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-muted">You don’t have any assigned faculties.</p>
      <?php endif; ?>
    </div>

    <!-- Quick Stats & Announcements -->
    <aside class="col-md-4">
      <div class="card mb-4 p-4">
        <h3 class="mb-3">Quick Stats</h3>
        <ul class="list-unstyled">
          <li>Profile Completion: <strong id="profile-completion">Loading...</strong></li>
          <li>Pending Requests: <strong>5</strong></li>
        </ul>
      </div>
      <div class="card p-4">
        <h3 class="mb-3">Announcements</h3>
        <p class="text-muted">No announcements at the moment.</p>
      </div>
    </aside>
  </main>

  <div id="modal-container"></div>
  <div id="footer-placeholder"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/main.js"></script>
</body>

</html>
