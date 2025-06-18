<?php
/*
 * Topsubject_mentee.php
 *
 * This page displays the mentee's view for a specific subject.
 * It allows mentees to publish questions, view active chats, and review closed conversations.
 */
require_once __DIR__ . '/auth_guard.php'; // inicia sesión, PDO y validación de usuario

// 1) Protect route
if (!isset($_SESSION['user_id'])) {
  header('Location: Toplogin.php');
  exit;
}

// 2) Get & validate subject_id
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>BiblioBros – <?= $subjectName ?> (Mentee)</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
</head>

<body class="d-flex flex-column min-vh-100">

  <!-- Navbar -->
  <div id="navbar-placeholder"></div>

  <!-- MAIN CONTENT -->
  <main class="container py-5 flex-grow-1">

    <!-- Subject Title -->
    <section class="text-center mb-5">
      <h2 class="section-title"><?= $subjectName ?></h2>
    </section>

    <!-- Publish Question Button -->
    <section class="mb-5 text-center">
      <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#requestModal">
        Publish a Question
      </button>
    </section>

    <!-- Active Chat Previews -->
    <section class="mb-5">
      <h3 class="section-title">Active Conversations</h3>
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


  </main>


  <!-- Footer -->
  <div id="footer-placeholder"></div>
  <div id="modal-container"></div>


  <script>
  window.__SUBJECT_ID__ = <?= json_encode($subjectId) ?>;
  </script>
  <script src="assets/js/main.js" defer></script>


  <!-- Publish Question Modal (mentees) -->
  <div class="modal fade" id="requestModal" tabindex="-1" aria-labelledby="requestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form action="php/request_help.php" method="post" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="requestModalLabel">Publish a Question</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Here we can use the $subjectId variable -->
          <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
          <div class="mb-3 text-start">
            <label for="mentorMessage" class="form-label">Your question</label>
            <textarea id="mentorMessage" name="message" class="form-control" rows="4" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Send Request</button>
        </div>
      </form>
    </div>
  </div>

</body>

</html>