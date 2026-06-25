<?php
/*
 * rating.php
 *
 * Allows mentees to rate their mentors after a chat session.
 */

require_once __DIR__ . '/auth_guard.php'; // Starts session and connects to DB

$mentee_id = (int) $_SESSION['user_id'];

// Leer chat_id desde GET o POST
$chatId = ($_SERVER['REQUEST_METHOD'] === 'POST')
  ? (int) ($_POST['chat_id'] ?? 0)
  : (int) ($_GET['chat_id'] ?? 0);

if ($chatId <= 0) {
  header('Location: Topdashboard.php');
  exit;
}

// Si se ha enviado el formulario de valoración
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $score = isset($_POST['score']) ? (int) $_POST['score'] : 0;
  $comment = trim($_POST['comment'] ?? '');

  if ($score < 1 || $score > 5) {
    $error = "Invalid rating.";
  } else {
    // Verificar que el usuario es el mentee y obtener subject_id
    $chk = $pdo->prepare("
      SELECT r.subject_id
        FROM chats c
        JOIN requests r ON r.id = c.request_id
       WHERE c.id = :cid AND r.mentee_id = :mid
       LIMIT 1
    ");
    $chk->execute([
      ':cid' => $chatId,
      ':mid' => $mentee_id
    ]);
    $row = $chk->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
      $error = "You are not authorized to rate this conversation.";
    } else {
      $subjectId = (int) $row['subject_id'];

      // Guardar valoración
      $ins = $pdo->prepare("
        INSERT INTO ratings (chat_id, score, comment)
        VALUES (:cid, :score, :comment)
      ");
      $ins->execute([
        ':cid' => $chatId,
        ':score' => $score,
        ':comment' => $comment
      ]);

      // Actualizar estado de la solicitud
      $updReq = $pdo->prepare("
        UPDATE requests
           SET status = 'closed'
         WHERE id = (SELECT request_id FROM chats WHERE id = :cid)
      ");
      $updReq->execute([':cid' => $chatId]);

      // Marcar chat como inactivo
      $updChat = $pdo->prepare("
        UPDATE chats SET active = FALSE WHERE id = :cid
      ");
      $updChat->execute([':cid' => $chatId]);

      header("Location: Topsubject_mentee.php?subject_id={$subjectId}");
      exit;
    }
  }
}

// GET: obtener datos del chat
$stmt = $pdo->prepare("
  SELECT 
    u.fullname AS mentor_name,
    s.name     AS subject_name,
    s.id       AS subject_id
  FROM chats c
  JOIN requests r  ON r.id = c.request_id
  JOIN users u     ON u.id = r.mentor_id
  JOIN subjects s  ON s.id = r.subject_id
  WHERE c.id = :cid
  LIMIT 1
");
$stmt->execute([':cid' => $chatId]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
  header('Location: Topdashboard.php');
  exit;
}

$mentorName  = htmlspecialchars($data['mentor_name'], ENT_QUOTES);
$subjectName = htmlspecialchars($data['subject_name'], ENT_QUOTES);
$subjectId   = (int) $data['subject_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>BiblioBros – Rate Mentor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body class="d-flex flex-column min-vh-100">
  <div id="navbar-placeholder"></div>

  <main class="container py-5 flex-grow-1 rating-page">
    <h2 class="mb-4">Rate Your Mentor</h2>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="mb-4">
      <p><strong>Subject:</strong> <?= $subjectName ?></p>
      <p><strong>Mentor:</strong> <?= $mentorName ?></p>
    </div>

    <form id="rating-form" method="post" action="rating.php">
      <input type="hidden" name="chat_id" value="<?= $chatId ?>" />

      <div class="star-rating">
        <?php for ($i = 5; $i >= 1; $i--): ?>
          <input type="radio" id="score<?= $i ?>" name="score" value="<?= $i ?>" <?= (isset($score) && $score == $i) ? 'checked' : '' ?> required>
          <label for="score<?= $i ?>" title="<?= $i ?> stars">★</label>
        <?php endfor; ?>
      </div>

      <div class="mb-3">
        <label for="comment" class="form-label">Comment (optional)</label>
        <textarea id="comment" name="comment" class="form-control" rows="3"><?= htmlspecialchars($comment ?? '') ?></textarea>
      </div>

      <button type="submit" class="btn btn-warning me-2">Submit Rating</button>
      <a href="Topsubject_mentee.php?subject_id=<?= $subjectId ?>" class="btn btn-secondary">Cancel</a>
    </form>
  </main>

  <div id="footer-placeholder"></div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
  <script src="assets/js/main.js"></script>
</body>
</html>
