// authGuard.js – Blocks access to private pages if user is not logged in
document.addEventListener("DOMContentLoaded", () => {
  const currentUser = sessionStorage.getItem("currentUser");

  // If no user is logged in, redirect to login
  if (!currentUser) {
    window.location.href = "Topindex.html";
  }
});
