// login.js – Handles user login via PHP session
console.log("🔥 login.js cargado");  // confirma carga
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
    console.log("🛫 login submit");  // marca que entra al handler

    const email = form.email.value.trim().toLowerCase();
    const password = form.password.value;

    if (!email || !password) {
      showError("Please enter both email and password.");
      return;
    }

    try {
      const res = await fetch("php/Toplogin.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify({ email, password })
      });
      console.log("📬 response status:", res.status);
      const result = await res.json();
      console.log("📥 result:", result);

      if (!result.success) {
        showError(result.message);
        return;
      }

      // Login correcto
      window.location.href = "Topdashboard.html";
    } catch (err) {
      showError("Server error. Please try again.");
      console.error(err);
    }
  });
});
