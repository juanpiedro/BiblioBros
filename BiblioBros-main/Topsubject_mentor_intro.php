<?php
require_once __DIR__ . '/auth_guard.php'; // inicia sesión, PDO y validación de usuario

// Protect route & validate subject_id
$subjectId = isset($_GET['subject_id'])
    ? (int)$_GET['subject_id']
    : (isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0);

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $intro = trim($_POST['intro_message'] ?? '');
    if ($intro !== '') {
        // MySQL upsert syntax
        $upsert = $pdo->prepare("
            INSERT INTO mentor_subject (user_id, subject_id, intro)
            VALUES (:uid, :sid, :intro)
            ON DUPLICATE KEY UPDATE intro = VALUES(intro)
        ");
        $upsert->execute([
            'uid'   => $userId,
            'sid'   => $subjectId,
            'intro' => $intro
        ]);
    }

    // Redirect to mentor view
    header("Location: Topsubject_mentor.php?subject_id={$subjectId}");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF‑8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>BiblioBros – <?= $subjectName ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="assets/css/style.css"/>
</head>
<body class="d-flex flex-column min-vh-100 mentor-theme">

  <div id="navbar-placeholder"></div>

  <main class="container d-flex flex-column align-items-center justify-content-center flex-fill py-5">
    <div class="form-card" style="max-width:600px;">
      <h2 class="section-title text-center mb-4"><?= $subjectName ?></h2>
      <h3 class="mb-3 text-center">Write Your Introductory Message</h3>

      <form action="Topsubject_mentor_intro.php" method="post">
        <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
        <div class="mb-4">
          <label for="intro-message" class="form-label">
            Describe how you will help mentees in this subject:
          </label>
          <textarea id="intro-message" name="intro_message"
                    class="form-control" rows="5" required><?= htmlspecialchars($_POST['intro_message'] ?? '') ?></textarea>
        </div>
        <div class="d-grid">
          <button type="submit" class="btn btn-secondary btn-xl">Enter</button>
        </div>
      </form>
    </div>
  </main>

  <div id="modal-container"></div>
  <div id="footer-placeholder"></div>

  <script src="assets/js/main.js"></script>
</body>
</html>
