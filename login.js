// login.js - Handles user login functionality
document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form");
    const errorMsg = document.getElementById("error-message");

    // Check if user is already logged in
    const currentUser = JSON.parse(localStorage.getItem("currentUser"));
    if (currentUser) {
        window.location.href = "dashboard.html";
        return;
    }

    // Show error message function
    function showError(message) {
        errorMsg.textContent = message;
        errorMsg.classList.remove("visually-hidden");
        setTimeout(() => {
            errorMsg.classList.add("visually-hidden");
        }, 5000);
    }

    form.addEventListener("submit", (e) => {
        e.preventDefault();

        // Get form values
        const email = document.getElementById("email").value.trim().toLowerCase();
        const password = document.getElementById("password").value;

        // Validation
        if (!email || !password) {
            showError("Пожалуйста, введите email и пароль!");
            return;
        }

        // Get users from localStorage
        const users = JSON.parse(localStorage.getItem("users") || "[]");

        // Find user with matching email and password
        const user = users.find(u => u.email === email && u.password === password);

        if (!user) {
            showError("Неверный email или пароль!");
            return;
        }

        // Store current user (without password)
        const { password: _, ...userWithoutPassword } = user;
        localStorage.setItem("currentUser", JSON.stringify(userWithoutPassword));

        // Redirect to dashboard
        window.location.href = "dashboard.html";
    });
});