<?php
require_once __DIR__ . '/auth_guard.php';



$subjectId = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? (int)($_POST['subject_id'] ?? 0)
    : (int)($_GET['subject_id'] ?? 0);

if ($subjectId <= 0) {
    header('Location: Topdashboard.php');
    exit;
}

// Fetch subject name
$stmt = $pdo->prepare("SELECT name FROM subjects WHERE id = :sid LIMIT 1");
$stmt->execute(['sid' => $subjectId]);
$subject = $stmt->fetch();
if (!$subject) {
    header('Location: Topdashboard.php');
    exit;
}
$subjectName = htmlspecialchars($subject['name']);
$userId = $_SESSION['user_id'];

// Check existing role
$stmtRole = $pdo->prepare("
  SELECT role
  FROM user_subject_role
  WHERE user_id = :uid AND subject_id = :sid
  LIMIT 1
");
$stmtRole->execute(['uid'=>$userId,'sid'=>$subjectId]);
$existingRole = $stmtRole->fetchColumn();

// Handle POST role selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$existingRole) {
  $role = $_POST['role'] ?? '';
  if (in_array($role, ['mentor','mentee'], true)) {
    $upsert = $pdo->prepare("
      INSERT INTO user_subject_role (user_id, subject_id, role)
      VALUES (:uid, :sid, :role)
      ON DUPLICATE KEY UPDATE role = role
    ");
    $upsert->execute(['uid'=>$userId,'sid'=>$subjectId,'role'=>$role]);

    if ($role === 'mentor') {
      $chk = $pdo->prepare("
        SELECT intro FROM mentor_subject
        WHERE user_id = :uid AND subject_id = :sid
        LIMIT 1
      ");
      $chk->execute(['uid'=>$userId,'sid'=>$subjectId]);
      $intro = $chk->fetchColumn();

      header("Location: " . ($intro
        ? "Topsubject_mentor.php?subject_id=$subjectId"
        : "Topsubject_mentor_intro.php?subject_id=$subjectId"
      ));
    } else {
      header("Location: Topsubject_mentee.php?subject_id=$subjectId");
    }
    exit;
  }
}

// Auto-redirect if role already exists
if ($existingRole) {
  if ($existingRole === 'mentor') {
    $chk = $pdo->prepare("
      SELECT intro FROM mentor_subject
      WHERE user_id = :uid AND subject_id = :sid
      LIMIT 1
    ");
    $chk->execute(['uid'=>$userId,'sid'=>$subjectId]);
    $intro = $chk->fetchColumn();

    header("Location: " . ($intro
      ? "Topsubject_mentor.php?subject_id=$subjectId"
      : "Topsubject_mentor_intro.php?subject_id=$subjectId"
    ));
  } else {
    header("Location: Topsubject_mentee.php?subject_id=$subjectId");
  }
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>BiblioBros – <?= $subjectName ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
</head>

<body class="d-flex flex-column min-vh-100">

  <!-- Navbar -->
  <div id="navbar-placeholder"></div>

  <!-- MAIN CONTENT: Role chooser -->
  <main class="container py-5 flex-grow-1">
    <section class="text-center mb-5">
      <h2 class="section-title"><?= $subjectName ?></h2>
      <p class="lead">Please choose your role for this subject:</p>
    </section>

    <form method="post" class="d-flex justify-content-center gap-3 mb-5">
      <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
      <button type="submit" name="role" value="mentor" class="btn btn-secondary btn-lg">I am a Mentor</button>
      <button type="submit" name="role" value="mentee" class="btn btn-primary btn-lg">I am a Mentee</button>
    </form>
  </main>

  <!-- Modals and Footer -->
  <div id="modal-container"></div>
  <div id="footer-placeholder"></div>

  <!-- Bootstrap JS and shared logic -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/main.js"></script>
</body>

</html>