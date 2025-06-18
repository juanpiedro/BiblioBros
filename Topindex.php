<?php
/*
 * Topindex.php
 *
 * This is the landing page of BiblioBros.
 * It introduces the platform and highlights key features, with links to login and register.
 */

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BiblioBros – Home</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

    <!-- AOS Animations -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css" />
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- Dynamic navbar container -->
    <div id="navbar-placeholder"></div>

    <!-- HERO SECTION (introductory landing area) -->
    <main class="hero-section text-center text-white d-flex align-items-center justify-content-center">
        <div data-aos="fade-up">
            <h1 class="display-4 fw-bold mb-4">Welcome to BiblioBros</h1>
            <p class="lead mb-5">University mentoring made easy, collaborative and accessible.</p>
            <a href="Toplogin.php" class="btn btn-primary btn-lg me-3">Login</a>
            <a href="Topregister.php" class="btn btn-secondary btn-lg">Register</a>
        </div>
    </main>

    <!-- FEATURES SECTION (key platform features) -->
    <section class="container pt-3 pb-5">
        <h2 class="section-title mx-auto text-center">Why BiblioBros?</h2>
        <div class="row mt-4 text-center">
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100">
                    <i class="fas fa-user-graduate fa-2x text-secondary mb-3"></i>
                    <h5 class="fw-bold">Mentorship</h5>
                    <p>Connect with experienced students from your faculty.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100">
                    <i class="fas fa-comments fa-2x text-secondary mb-3"></i>
                    <h5 class="fw-bold">Live Chat</h5>
                    <p>Ask questions and get help in real time through our chat system.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100">
                    <i class="fas fa-chart-line fa-2x text-secondary mb-3"></i>
                    <h5 class="fw-bold">Track Progress</h5>
                    <p>View stats, active sessions and your learning history easily.</p>
                </div>
            </div>
        </div>
    </section>


    <div id="modal-container"></div>
    <!-- Dynamic footer container -->
    <div id="footer-placeholder"></div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AOS Animations -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init();</script>

    <!-- Main JS for shared logic (navbar, footer, modals) -->
    <script src="assets/js/main.js"></script>
</body>

</html>