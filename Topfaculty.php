<?php
require_once __DIR__ . '/auth_guard.php'; // login + DB, session

$facultyId = isset($_GET['faculty_id']) ? (int) $_GET['faculty_id'] : 0;
if ($facultyId <= 0) {
    header('Location: Topdashboard.php');
    exit;
}

// Fetch faculty details
$stmt = $pdo->prepare("
    SELECT name, description
    FROM faculties
    WHERE id = :fid
    LIMIT 1
");
$stmt->execute(['fid' => $facultyId]);
$faculty = $stmt->fetch();

if (!$faculty) {
    header('Location: Topdashboard.php');
    exit;
}
$facultyName = htmlspecialchars($faculty['name']);
$facultyDesc = htmlspecialchars($faculty['description']);

// Fetch subjects
$stmt2 = $pdo->prepare("
    SELECT id, name
    FROM subjects
    WHERE faculty_id = :fid
    ORDER BY name
");
$stmt2->execute(['fid' => $facultyId]);
$subjects = $stmt2->fetchAll();

// Fetch top mentors
$stmt3 = $pdo->prepare("
    SELECT 
      u.id,
      u.fullname,
      COALESCE(ROUND(AVG(r.score), 2),0) AS avg_score,
      COUNT(r.id) AS num_ratings
    FROM mentor_subject ms
    JOIN users u ON u.id = ms.user_id
    JOIN subjects s ON s.id = ms.subject_id
    LEFT JOIN requests req ON req.mentor_id = u.id AND req.status = 'accepted'
    LEFT JOIN chats c ON c.request_id = req.id
    LEFT JOIN ratings r ON r.chat_id = c.id
    WHERE s.faculty_id = :fid
    GROUP BY u.id
    ORDER BY avg_score DESC, num_ratings DESC
    LIMIT 6
");
$stmt3->execute(['fid' => $facultyId]);
$mentors = $stmt3->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>BiblioBros – <?= $facultyName ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
</head>

<body class="d-flex flex-column min-vh-100">

  <!-- Navbar -->
  <div id="navbar-placeholder"></div>

  <!-- Main Content -->
  <main class="container py-5 flex-grow-1">

    <!-- Faculty name & description -->
    <section class="mb-4">
      <h2 class="section-title"><?= $facultyName ?></h2>
      <?php if ($facultyDesc): ?>
        <p class="lead"><?= $facultyDesc ?></p>
      <?php endif; ?>
    </section>

    <!-- Download button -->
    <div class="mb-4">
      <a href="php/subjects_view.php?faculty_id=<?= $facultyId ?>&download=html" class="btn btn-outline-secondary">
        📥 Download subjects HTML
      </a>

    </div>


    <!-- Subjects tab -->
    <section id="subjects" class="mb-5">
      <div id="subjects-list" class="row g-3">
        <?php foreach ($subjects as $sub): ?>
          <div class="col-md-4 subject-card">
            <div class="card p-3">
              <h5><?= htmlspecialchars($sub['name']) ?></h5>
              <a href="Topsubject.php?subject_id=<?= $sub['id'] ?>" class="btn btn-warning btn-sm">Go</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Mentors tab -->
    <section id="mentors" class="d-none">
      <h4 class="text-warning mb-3">Top Mentors</h4>
      <div class="row g-3">
        <?php foreach ($mentors as $m): ?>
          <div class="col-md-4">
            <div class="card p-3">
              <h5><?= htmlspecialchars($m['fullname']) ?></h5>
              <p class="mb-1">
                <?= number_format($m['avg_score'], 2) ?> ★
                (<?= $m['num_ratings'] ?> ratings)
              </p>
            </div>
          </div>
        <?php endforeach; ?>
        <?php if (empty($mentors)): ?>
          <p class="text-muted">No mentors available yet.</p>
        <?php endif; ?>
      </div>
    </section>

  </main>

  <div id="modal-container"></div>
  <div id="footer-placeholder"></div>

  <script src="assets/js/main.js"></script>
  <script>
    function showSection(id) {
      document.getElementById('subjects').classList.toggle('d-none', id !== 'subjects');
      document.getElementById('mentors').classList.toggle('d-none', id !== 'mentors');
    }

    function filterSubjects() {
      const term = document.getElementById('subject-search').value.toLowerCase();
      document.querySelectorAll('.subject-card').forEach(card => {
        const name = card.querySelector('h5').textContent.toLowerCase();
        card.style.display = name.includes(term) ? '' : 'none';
      });
    }
  </script>
</body>

</html>