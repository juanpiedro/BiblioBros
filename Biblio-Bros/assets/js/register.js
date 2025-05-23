// register.js – Maneja el registro enviando JSON a Topregister.php
document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form");
  const errorBox = document.getElementById("error-message");

  function showError(msg) {
    errorBox.textContent = msg;
    errorBox.classList.remove("visually-hidden");
    setTimeout(() => {
      errorBox.classList.add("visually-hidden");
    }, 4000);
  }

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const fullname   = form.fullname.value.trim();
    const email      = form.email.value.trim().toLowerCase();
    const university = form.university.value;
    const password   = form.password.value;
    const confirm    = form["confirm-password"].value;

    // Validaciones
    if (!fullname || !email || !university || !password || !confirm) {
      showError("Please fill in all fields.");
      return;
    }
    if (password !== confirm) {
      showError("Passwords do not match.");
      return;
    }

    // Envío por fetch como JSON (incluyendo la clave "confirm-password")
    try {
      const res = await fetch("php/Topregister.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          fullname,
          email,
          university,
          password,
          "confirm-password": confirm
        })
      });
      const result = await res.json();

      if (!result.success) {
        showError(result.message);
        return;
      }
      // Registro correcto → redirigir a login
      window.location.href = "Toplogin.html";
    } catch (err) {
      showError("Network error, please try again.");
    }
  });
});
