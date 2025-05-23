// loadProfile.js – Loads and updates user profile info with Bootstrap modals
document.addEventListener("DOMContentLoaded", () => {
    const user = JSON.parse(sessionStorage.getItem("currentUser"));
    if (!user) {
        window.location.href = "Toplogin.html";
        return;
    }

    // Elements
    const fullnameInput = document.getElementById("fullname");
    const universityInput = document.getElementById("university");
    const emailInput = document.getElementById("email");
    const descriptionInput = document.getElementById("public-desc");
    const form = document.querySelector("form");
    const cancelBtn = document.querySelector('button[type="reset"]');
    const confirmCancelBtn = document.getElementById("confirmCancel");

    // Load current user data into the form
    if (fullnameInput) fullnameInput.value = user.fullname || "";
    if (universityInput) universityInput.value = user.university || "";
    if (emailInput) emailInput.value = user.email || "";
    if (descriptionInput) descriptionInput.value = user.description || "";

    // Save changes
    form.addEventListener("submit", (e) => {
        e.preventDefault();

        const updatedUser = {
            ...user,
            fullname: fullnameInput.value.trim(),
            university: universityInput.value.trim(),
            email: emailInput.value.trim(),
            description: descriptionInput.value.trim(),
        };

        // Update sessionStorage
        sessionStorage.setItem("currentUser", JSON.stringify(updatedUser));

        // Update localStorage (for persistence between sessions)
        const allUsers = JSON.parse(localStorage.getItem("users")) || [];
        const updatedUsers = allUsers.map(u =>
            u.email === user.email ? { ...u, ...updatedUser } : u
        );
        localStorage.setItem("users", JSON.stringify(updatedUsers));

        // Show Bootstrap modal success message
        const successModal = new bootstrap.Modal(document.getElementById("successModal"));
        successModal.show();
    });

    // Cancel button with confirmation modal
    if (cancelBtn) {
        cancelBtn.addEventListener("click", (e) => {
            e.preventDefault();
            const cancelModal = new bootstrap.Modal(document.getElementById("cancelModal"));
            cancelModal.show();
        });
    }

    // Handle confirmation from cancel modal
    if (confirmCancelBtn) {
        confirmCancelBtn.addEventListener("click", () => {
            window.location.href = "Topdashboard.html";
        });
    }
});
