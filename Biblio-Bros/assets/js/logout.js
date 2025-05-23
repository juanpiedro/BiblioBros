// assets/js/nueva_carpeta/logout.js

document.addEventListener("DOMContentLoaded", () => {
  const logoutBtn        = document.getElementById("logout-button");
  const confirmLogoutBtn = document.getElementById("confirmLogout");

  // 1. Mostrar el modal al hacer clic en “Log out”
  if (logoutBtn) {
    logoutBtn.addEventListener("click", (e) => {
      e.preventDefault();
      const logoutModalEl = document.getElementById("logoutModal");
      if (logoutModalEl) {
        const logoutModal = new bootstrap.Modal(logoutModalEl);
        logoutModal.show();
      } else {
        console.error("⚠️ No se encontró #logoutModal en la página");
      }
    });
  }

  // 2. Cuando el usuario confirma, redirige al endpoint PHP
  if (confirmLogoutBtn) {
    confirmLogoutBtn.addEventListener("click", () => {
      window.location.href = "php/logout.php";
    });
  }
});
