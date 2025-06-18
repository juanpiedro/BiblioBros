<?php
/*
 * Topprofile.php
 *
 * Single-entry profile page: shows and edits profile, displays subjects & roles,
 * closed conversations, and ratings (via AJAX).
 */

require_once __DIR__ . '/auth_guard.php';
$userId = (int) $_SESSION['user_id'];

// Handle form submission (update profile)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $errors = [];

    if (!$fullname || !$email) $errors[] = 'Full name and email are required.';
    if ($newPassword && $newPassword !== $confirmPassword) $errors[] = 'Passwords do not match.';
    if ($errors) {
        header("Location: Topprofile.php?error=" . urlencode(implode(' ', $errors)));
        exit;
    }

    $params = ['fullname'=>$fullname, 'email'=>$email, 'uid'=>$userId];
    $set = 'fullname = :fullname, email = :email';
    if ($newPassword) {
        $params['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        $set .= ', password_hash = :password_hash';
    }
    $stmt = $pdo->prepare("UPDATE users SET $set WHERE id = :uid");
    $stmt->execute($params);
    header('Location: Topprofile.php?updated=1');
    exit;
}

// Fetch user info
$stmt = $pdo->prepare("
    SELECT u.fullname, u.email, u.public_description, uni.name AS university_name
    FROM users u
    JOIN universities uni ON uni.id = u.university_id
    WHERE u.id = :uid LIMIT 1
");
$stmt->execute(['uid' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    session_destroy();
    header('Location: Toplogin.php');
    exit;
}

// Subject-role associations
$stmt2 = $pdo->prepare("
    SELECT usr.role, s.name AS subject_name, f.name AS faculty_name, uni.name AS university_name
    FROM user_subject_role usr
    JOIN subjects s ON s.id = usr.subject_id
    JOIN faculties f ON f.id = s.faculty_id
    JOIN universities uni ON uni.id = f.university_id
    WHERE usr.user_id = :uid
    ORDER BY uni.name, f.name, s.name
");
$stmt2->execute(['uid' => $userId]);
$assocs = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Closed conversations (mentee)
$stmt3 = $pdo->prepare("
    SELECT c.id AS chat_id, u.fullname AS mentor_name, s.name AS subject_name,
           DATE_FORMAT(c.created_at, '%d %b %Y %H:%i') AS closed_at
    FROM chats c
    JOIN requests r ON r.id = c.request_id
    JOIN users u ON u.id = r.mentor_id
    JOIN subjects s ON s.id = r.subject_id
    WHERE r.mentee_id = :uid
      AND c.active = FALSE
    ORDER BY c.created_at DESC
");
$stmt3->execute(['uid' => $userId]);
$closedAsMentee = $stmt3->fetchAll(PDO::FETCH_ASSOC);

// Closed conversations (mentor)
$stmt4 = $pdo->prepare("
    SELECT c.id AS chat_id, u.fullname AS mentee_name, s.name AS subject_name,
           DATE_FORMAT(c.created_at, '%d %b %Y %H:%i') AS closed_at
    FROM chats c
    JOIN requests r ON r.id = c.request_id
    JOIN users u ON u.id = r.mentee_id
    JOIN subjects s ON s.id = r.subject_id
    WHERE r.mentor_id = :uid
      AND c.active = FALSE
    ORDER BY c.created_at DESC
");
$stmt4->execute(['uid' => $userId]);
$closedAsMentor = $stmt4->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>BiblioBros – Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="assets/css/style.css"/>
</head>
<body class="d-flex flex-column min-vh-100">
<div id="navbar-placeholder"></div>
<main class="container flex-grow-1 py-5">
  <h2 class="section-title text-center mb-4">Your Profile</h2>

  <div class="form-card mx-auto" style="max-width: 600px;">
    <form method="post" novalidate>
      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
      <?php elseif (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Profile updated successfully!</div>
      <?php endif; ?>

      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>" required/>
      </div>
      <div class="mb-3">
        <label class="form-label">University</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($user['university_name']) ?>" readonly/>
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="public_description" class="form-control" rows="4"><?= htmlspecialchars($user['public_description'] ?? '') ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required/>
      </div>
      <hr>
      <div class="mb-3">
        <label class="form-label">New Password</label>
        <input type="password" name="new_password" class="form-control"/>
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control"/>
      </div>
      <div class="d-flex justify-content-between">
        <button type="reset" class="btn btn-light">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>

  <section class="mt-5 mx-auto" style="max-width: 800px;">
    <h3 class="section-title">Your Subjects & Roles</h3>
    <?php if ($assocs): ?>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>University</th><th>Faculty</th><th>Subject</th><th>Role</th></tr></thead>
          <tbody>
            <?php foreach ($assocs as $a): ?>
              <tr>
                <td><?= htmlspecialchars($a['university_name']) ?></td>
                <td><?= htmlspecialchars($a['faculty_name']) ?></td>
                <td><?= htmlspecialchars($a['subject_name']) ?></td>
                <td><?= ucfirst(htmlspecialchars($a['role'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-muted text-center">You’re not associated with any subjects yet.</p>
    <?php endif; ?>
  </section>

  <section class="mt-5 mx-auto" style="max-width:800px;">
    <h3 class="section-title">Your Ratings</h3>
    <div class="card">
      <div class="card-body">
        <div id="ratings-app">
          <!-- Vue component mounts here -->
        </div>
      </div>
    </div>
  </section>

  <section class="mt-5 mx-auto" style="max-width:800px;">
    <h3 class="section-title">Your Closed Conversations</h3>
    <?php if ($closedAsMentee): ?>
      <h5>As Mentee</h5>
      <ul class="list-group mb-4">
        <?php foreach ($closedAsMentee as $c): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <div><strong><?= htmlspecialchars($c['subject_name']) ?></strong> with <?= htmlspecialchars($c['mentor_name']) ?>
              <span class="text-muted small">— closed at <?= $c['closed_at'] ?></span>
            </div>
            <a href="Topchat_mentee.php?chat_id=<?= $c['chat_id'] ?>" class="btn btn-primary btn-sm">View Chat</a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php if ($closedAsMentor): ?>
      <h5>As Mentor</h5>
      <ul class="list-group">
        <?php foreach ($closedAsMentor as $c): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <div><strong><?= htmlspecialchars($c['subject_name']) ?></strong> with <?= htmlspecialchars($c['mentee_name']) ?>
              <span class="text-muted small">— closed at <?= $c['closed_at'] ?></span>
            </div>
            <a href="Topchat_mentor.php?chat_id=<?= $c['chat_id'] ?>" class="btn btn-primary btn-sm">View Chat</a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php if (!$closedAsMentee && !$closedAsMentor): ?>
      <p class="text-muted text-center">No closed conversations yet.</p>
    <?php endif; ?>
  </section>
</main>

<div id="modal-container"></div>
<div id="footer-placeholder"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js" defer></script>
<script src="assets/js/ratings-vue.js" defer></script>
<script src="assets/js/main.js" defer></script>
</body>
</html>
