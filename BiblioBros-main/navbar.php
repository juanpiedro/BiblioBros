<?php
/*
 * navbar.php
 *
 * This file renders the main navigation bar. It adapts its appearance based on the current page theme.
 * The logout button triggers a confirmation modal before logging out.
 */

// detect current script
$page = basename($_SERVER['SCRIPT_NAME']);

$darkPages = [
  'Topsubject_mentor.php',
  'Topsubject_mentor_intro.php',
  'Topchat_mentor.php',
  // etc.
];

$isDark = in_array($page, $darkPages, true);

$navClass = 'navbar navbar-expand-lg sticky-top ' 
  . ($isDark ? 'navbar-dark bg-dark' : 'navbar-light bg-light');

$logoutBtnClass = $isDark
  ? 'btn btn-primary'
  : 'btn btn-secondary';
?>
<nav class="<?= $navClass ?>">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="Topdashboard.php">
      <img src="assets/img/logo.png" class="site-logo logo-dark"  alt="BiblioBros Logo">
      <img src="assets/img/invlogo.png" class="site-logo logo-light" alt="BiblioBros Logo Inverted">
      <span class="ms-3">BiblioBros</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link" href="Topdashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="Topprofile.php">Profile</a></li>
        <li class="nav-item">
          <a
            href="#"
            id="logout-button"
            data-bs-toggle="modal"
            data-bs-target="#logoutModal"
            class="<?= $logoutBtnClass ?> ms-3"
          >
            Log out
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
