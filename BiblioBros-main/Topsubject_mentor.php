<?php
/*
 * Topsubject_mentor.php
 *
 * This page displays the mentor's view for a specific subject.
 * It shows the mentor's introductory message, mentee requests, active chats, and closed conversations.
 */
require_once __DIR__ . '/auth_guard.php'; // inicia sesión, PDO y validación de usuario


// 2) Get and validate subject_id
$subjectId = isset($_GET['subject_id']) ? (int) $_GET['subject_id'] : 0;
if ($subjectId <= 0) {
  header('Location: Topdashboard.php');
  exit;
}


// 3) Fetch subject name
$stmt = $pdo->prepare("
    SELECT name
    FROM subjects
    WHERE id = :sid
    LIMIT 1
");
$stmt->execute(['sid' => $subjectId]);
$subject = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$subject) {
  header('Location: Topdashboard.php');
  exit;
}
$subjectName = htmlspecialchars($subject['name']);

// 4) Fetch this mentor’s intro for the subject
$stmt2 = $pdo->prepare("
    SELECT intro
    FROM mentor_subject
    WHERE user_id = :uid
      AND subject_id = :sid
    LIMIT 1
");
$stmt2->execute([
  'uid' => $_SESSION['user_id'],
  'sid' => $subjectId
]);
$row = $stmt2->fetch(PDO::FETCH_ASSOC);
$introMessage = $row ? htmlspecialchars($row['intro']) : 'No intro message provided.';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>BiblioBros – <?= $subjectName ?> (Mentor)</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
</head>

<body class="d-flex flex-column min-vh-100 mentor-theme">

  <!-- Navbar -->
  <div id="navbar-placeholder"></div>

  <!-- MAIN SUBJECT VIEW -->
  <main class="container d-flex flex-column align-items-center justify-content-center flex-fill py-5">
    <div class="form-card w-100" style="max-width: 800px;">
      <!-- Subject Title -->
      <h2 class="section-title text-center mb-4"><?= $subjectName ?></h2>

      <!-- Intro message -->
      <section class="mb-5">
        <h4 class="mb-3 text-center">Your Introductory Message</h4>
        <p class="text-center"><?= $introMessage ?></p>
      </section>

      <!-- Requests from mentees -->
      <section class="mb-5">
        <h4 class="mb-3">Requests Received</h4>
        <ul id="pending-requests" class="list-group">
          <li class="list-group-item">Loading questions...</li>
        </ul>
      </section>

      <!-- Active chats -->
      <section class="mb-4">
        <h4 class="mb-3">Active Chats</h4>
        <ul id="active-chats" class="list-group">
          <li class="list-group-item">Loading chats...</li>
        </ul>
      </section>

      <!-- Closed Chat Previews -->
      <section class="mb-5">
        <h3 class="section-title">Closed Conversations</h3>
        <ul id="closed-chats" class="list-group">
          <li class="list-group-item">Loading closed chats...</li>
        </ul>
      </section>
    </div>
  </main>

  <!-- Modals & Footer -->
  <div id="modal-container"></div>
  <div id="footer-placeholder"></div>

  <script>
  // Set chat loader URL dynamically
  const CHAT_LOADER = 'php/chat_loader_mentor.php?subject_id=<?= $subjectId ?>';
  </script>
  <!-- Shared scripts (navbar, footer, modals, logout…) -->
  <script src="assets/js/main.js"></script>
</body>

</html>