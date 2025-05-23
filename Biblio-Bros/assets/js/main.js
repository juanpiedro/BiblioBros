// main.js – Loads shared components and initializes page-specific logic
document.addEventListener("DOMContentLoaded", () => {
  const components = [
    { id: "navbar-placeholder", file: "navbar.html" },
    { id: "footer-placeholder", file: "footer.html" },
    { id: "modal-container", file: "modals.html" },
  ];

  Promise.all(
    components.map(c =>
      fetch(c.file)
        .then(res => res.ok ? res.text() : "")
        .then(html => {
          let container = document.getElementById(c.id);
          if (!container) {
            container = document.createElement("div");
            container.id = c.id;
            document.body.appendChild(container);
          }
          container.innerHTML = html;
        })
    )
  )
    .then(() => {
      checkSessionAndInit();
      cleanModals();
    })
    .catch(err => console.error("Component load error:", err));
});

function checkSessionAndInit() {
  fetch("php/session_status.php")
    .then(res => res.json())
    .then(status => {
      const protectedPages = [
        "Topdashboard.html",
        "Topprofile.html",
        "Topchat_mentor.html",
        "Topchat_mentee.html"
      ];
      const onProtected = protectedPages.some(p =>
        window.location.pathname.endsWith(p)
      );

      if (onProtected && !status.authenticated) {
        window.location.href = "Toplogin.html";
        return;
      }

      initLogout(status);
      initDashboard(status);
      initProfile(status);

      // Load chat previews for mentee subject page
      if (window.location.pathname.endsWith("Topsubject_mentee.html")) {
        fetch("php/load_chats_mentee.php", {
          credentials: "include"
        })
          .then(res => {
            if (!res.ok) throw new Error(res.status);
            return res.json();
          })
          .then(chats => {
            const ul = document.getElementById("active-chats");
            ul.innerHTML = "";
            if (chats.length === 0) {
              ul.innerHTML = "<li class='list-group-item'>No active chats yet.</li>";
              return;
            }
            chats.forEach(c => {
              const li = document.createElement("li");
              li.className = "list-group-item d-flex justify-content-between align-items-center";
              li.innerHTML = `
          Chat with ${c.mentor_name} (${c.subject})
          <a href="Topchat_mentee.html?chat_id=${c.chat_id}" class="btn btn-primary btn-sm">Open Chat</a>`;
              ul.appendChild(li);
            });
          })
          .catch(() => {
            const ul = document.getElementById("active-chats");
            if (ul) {
              ul.innerHTML =
                "<li class='list-group-item text-danger'>Error loading chats.</li>";
            }
          });
      }

      // Load mentor subject data: pending requests and active chats
      if (window.location.pathname.endsWith("Topsubject_mentor.html")) {
        const subject = encodeURIComponent("Algebra I");

        // Load requests from mentees
        fetch(`php/load_requests.php?subject=${subject}`, {
          credentials: "include"
        })
          .then(res => {
            if (!res.ok) throw new Error(res.status);
            return res.json();
          })
          .then(data => {
            const ul = document.getElementById("pending-requests");
            ul.innerHTML = "";
            if (data.length === 0) {
              ul.innerHTML = "<li class='list-group-item'>No pending questions.</li>";
              return;
            }
            data.forEach(r => {
              const li = document.createElement("li");
              li.className = "list-group-item d-flex justify-content-between align-items-center flex-wrap";
              li.innerHTML = `
          <div><strong>${r.mentee_name}</strong> — "${r.message}"</div>
          <form action="php/accept_request.php" method="post" class="mt-2 mt-sm-0">
            <input type="hidden" name="request_id" value="${r.id}">
            <button type="submit" class="btn btn-secondary btn-sm">Accept</button>
          </form>`;
              ul.appendChild(li);
            });
          })
          .catch(() => {
            document.getElementById("pending-requests").innerHTML =
              "<li class='list-group-item text-danger'>Error loading questions.</li>";
          });

        // Load active chats
        fetch("php/load_chats_mentor.php", {
          credentials: "include"
        })
          .then(res => {
            if (!res.ok) throw new Error(res.status);
            return res.json();
          })
          .then(chats => {
            const ul = document.getElementById("active-chats");
            ul.innerHTML = "";
            if (chats.length === 0) {
              ul.innerHTML = "<li class='list-group-item'>No active chats yet.</li>";
              return;
            }
            chats.forEach(c => {
              const li = document.createElement("li");
              li.className = "list-group-item d-flex justify-content-between align-items-center";
              li.innerHTML = `
          Chat with ${c.mentee_name} (${c.subject})
          <a href="Topchat_mentor.html?chat_id=${c.chat_id}" class="btn btn-primary btn-sm">Open Chat</a>`;
              ul.appendChild(li);
            });
          })
          .catch(() => {
            document.getElementById("active-chats").innerHTML =
              "<li class='list-group-item text-danger'>Error loading chats.</li>";
          });
      }



      // Chat logic for both mentee and mentor views
      if (["Topchat_mentee.html", "Topchat_mentor.html"].some(p => window.location.pathname.endsWith(p))) {
        const chatId = new URLSearchParams(window.location.search).get("chat_id");
        const chatHistory = document.getElementById("chat-history");
        const chatForm = document.getElementById("chat-form");
        const messageInput = document.getElementById("message");

        function loadMessages() {
          fetch(`php/load_messages.php?chat_id=${chatId}`, { credentials: 'include' })
            .then(res => res.json())
            .then(messages => {
              chatHistory.innerHTML = "";
              messages.forEach(msg => {
                const div = document.createElement("div");
                div.className = msg.sender === 'me'
                  ? "text-end text-primary mb-2"
                  : "text-start mb-2";
                div.innerHTML = `
                  <small>${msg.time}</small>
                  <p class="mb-1">${msg.text}</p>
                `;
                chatHistory.appendChild(div);
              });
              // Automatically scroll to the bottom after loading messages
              chatHistory.scrollTop = chatHistory.scrollHeight;
            });
        }

        // Handle message submission
        chatForm.addEventListener("submit", function (e) {
          e.preventDefault();
          const text = messageInput.value.trim();
          if (!text) return;

          fetch("php/send_message.php", {
            method: "POST",
            credentials: "include",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `chat_id=${chatId}&message=${encodeURIComponent(text)}`
          })
            .then(res => {
              if (!res.ok) throw new Error(res.status);
              messageInput.value = "";
              loadMessages();
            })
            .catch(err => console.error("Send error:", err));
        });

        // Initial load and polling for new messages every 5 seconds
        loadMessages();
        setInterval(loadMessages, 5000);
      }
    })
    .catch(err => {
      console.error("Error checking session:", err);
      window.location.href = "Toplogin.html";
    });
}

function initLogout(status) {
  const logoutBtn = document.getElementById("logout-button");
  const confirmLogoutBtn = document.getElementById("confirmLogout");
  if (logoutBtn && confirmLogoutBtn) {
    logoutBtn.addEventListener("click", e => {
      e.preventDefault();
      new bootstrap.Modal(document.getElementById("logoutModal")).show();
    });
    confirmLogoutBtn.addEventListener("click", () => {
      window.location.href = "php/logout.php";
    });
  }
}

function initDashboard(status) {
  if (window.location.pathname.endsWith("Topdashboard.html") && status.authenticated) {
    const greeting = document.getElementById("user-greeting");
    if (greeting) {
      const firstName = status.fullname.split(" ")[0] || "";
      greeting.textContent = `Welcome back, ${firstName}!`;
    }

    // Enable faculty dropdown on hover
    const facultiesCard = document.querySelector('.your-faculties-card');
    const selector = document.querySelector('.faculty-selector');
    if (facultiesCard && selector) {
      facultiesCard.addEventListener('mouseenter', () => {
        selector.classList.remove('visually-hidden');
      });
      facultiesCard.addEventListener('mouseleave', () => {
        selector.classList.add('visually-hidden');
      });
    }

    // Redirect to selected faculty
    const facultyDropdown = document.getElementById("facultyDropdown");
    if (facultyDropdown) {
      facultyDropdown.addEventListener("change", function () {
        const url = this.value;
        if (url) window.location.href = url;
      });
    }

    // Optionally, here you could fetch data for quick stats or pending requests
  }
}

function initProfile(status) {
  if (window.location.pathname.endsWith("Topprofile.html") && status.authenticated) {
    const fullnameInput = document.getElementById("fullname");
    const universityInput = document.getElementById("university");
    const emailInput = document.getElementById("email");
    const descriptionInput = document.getElementById("public-desc");
    const form = document.querySelector("form");
    const cancelBtn = document.querySelector('button[type="reset"]');
    const confirmCancelBtn = document.getElementById("confirmCancel");

    if (fullnameInput) fullnameInput.value = status.fullname;
    if (universityInput) universityInput.value = status.university || "";
    if (emailInput) emailInput.value = status.email || "";
    if (descriptionInput) descriptionInput.value = status.description || "";

    form?.addEventListener("submit", e => {
      e.preventDefault();
      // Here you could fetch to update_profile.php
      new bootstrap.Modal(document.getElementById("successModal")).show();
    });

    cancelBtn?.addEventListener("click", e => {
      e.preventDefault();
      new bootstrap.Modal(document.getElementById("cancelModal")).show();
    });
    confirmCancelBtn?.addEventListener("click", () => {
      window.location.href = "Topdashboard.html";
    });
  }
}

function cleanModals() {
  document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('hidden.bs.modal', () => {
      document.body.classList.remove('modal-open');
      document.querySelectorAll('.modal-backdrop')
        .forEach(el => el.remove());
    });
  });
}
