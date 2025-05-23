// loadModals.js – Loads shared modals into the page automatically
document.addEventListener("DOMContentLoaded", () => {
  fetch("modals.html")
    .then(res => {
      if (!res.ok) {
        throw new Error("Failed to load modal content.");
      }
      return res.text();
    })
    .then(html => {
      const container = document.createElement("div");
      container.innerHTML = html;
      document.body.appendChild(container);
    })
    .catch(err => {
      console.error("Error loading modals:", err);
    });
});
