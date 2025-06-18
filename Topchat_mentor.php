<?php
/*
 * Topchat_mentor.php – MySQL version
 */

require_once __DIR__ . '/auth_guard.php';

$chatId = isset($_GET['chat_id']) ? (int) $_GET['chat_id'] : 0;
if ($chatId <= 0) {
  header('Location: Topdashboard.php');
  exit;
}

try {
  $stmt = $pdo->prepare("
    SELECT c.active, u.fullname AS mentee_name
      FROM chats c
      JOIN requests r ON r.id = c.request_id
      JOIN users u ON u.id = r.mentee_id
     WHERE c.id = :cid
     LIMIT 1
  ");
  $stmt->execute([':cid' => $chatId]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$row) {
    header('Location: Topdashboard.php');
    exit;
  }

  $isActive = (bool) $row['active'];
  $menteeName = htmlspecialchars($row['mentee_name'], ENT_QUOTES);

} catch (PDOException $e) {
  die("DB error: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BiblioBros – Chat with <?= $menteeName ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="d-flex flex-column min-vh-100 mentor-theme">
  <div id="navbar-placeholder"></div>

  <main class="container py-5 d-flex flex-column flex-grow-1">
    <div class="row gx-4">

      <aside class="col-md-4">
        <h2 class="section-title">Chats</h2>
        <ul id="chat-list" class="list-group">
          <li class="list-group-item">Loading chats...</li>
        </ul>
      </aside>

      <section class="col-md-8 d-flex flex-column" id="chat-window" style="display: none">
        <div class="chat-header mb-3 d-flex justify-content-between align-items-center">
          <h2 id="chat-with">Chat with <?= $menteeName ?></h2>

          <?php if (!$isActive): ?>
            <span class="badge bg-secondary">Conversation closed</span>
          <?php endif; ?>
        </div>

        <div id="chat-history" class="chat-history flex-grow-1 overflow-auto mb-3 border rounded p-3 bg-light" data-user-id="<?= $_SESSION['user_id'] ?>">
          <p class="text-muted">Loading messages...</p>
        </div>

        <?php if ($isActive): ?>
          <form id="chat-form" class="d-flex">
            <input type="text" id="message-input" class="form-control me-2" placeholder="Write a message…" required />
            <button type="submit" class="btn btn-primary">Send</button>
          </form>
        <?php endif; ?>
      </section>
    </div>
  </main>

  <div id="modal-container"></div>
  <div id="footer-placeholder"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
  <script src="assets/js/main.js"></script>
</body>
</html>
