document.addEventListener("DOMContentLoaded", function () {
  const loginForm = document.getElementById("loginForm");
  const registerForm = document.getElementById("registerForm");
  const searchBox = document.getElementById("searchBox");
  const bookTable = document.getElementById("bookTable");
  const resetForm = document.getElementById("resetForm");

  // Tab switching functionality
  window.showTab = function(tabName) {
    // Hide all auth sections
    const authSections = document.querySelectorAll('.auth-section');
    authSections.forEach(section => {
      section.classList.remove('active');
    });

    // Remove active class from all tab buttons
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => {
      button.classList.remove('active');
    });

    // Show selected section and activate corresponding button
    document.getElementById(tabName + '-section').classList.add('active');
    event.target.classList.add('active');
  };

  if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
      const username = document.getElementById("username").value.trim();
      const password = document.getElementById("password").value.trim();

      if (!username || !password) {
        e.preventDefault();
        alert("Please fill in both username and password.");
      }
    });
  }

  if (registerForm) {
    registerForm.addEventListener("submit", function (e) {
      const fullName = document.getElementById("reg_full_name").value.trim();
      const username = document.getElementById("reg_username").value.trim();
      const email = document.getElementById("reg_email").value.trim();
      const password = document.getElementById("reg_password").value;
      const confirmPassword = document.getElementById("reg_confirm_password").value;
      const role = document.getElementById("reg_role") ? document.getElementById("reg_role").value : "";

      // Validation
      if (!fullName || !username || !email || !password || !confirmPassword) {
        e.preventDefault();
        alert("Please fill in all fields.");
        return;
      }

      if (password.length < 6) {
        e.preventDefault();
        alert("Password must be at least 6 characters long.");
        return;
      }

      if (password !== confirmPassword) {
        e.preventDefault();
        alert("Passwords do not match.");
        return;
      }

      // Email validation
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        e.preventDefault();
        alert("Please enter a valid email address.");
        return;
      }
    });
  }

  if (resetForm) {
  resetForm.addEventListener("submit", function (e) {
    const email = document.getElementById("reset_email").value.trim();
    const newPassword = document.getElementById("reset_new_password").value;
    const confirmPassword = document.getElementById("reset_confirm_password").value;

    if (!email || !newPassword || !confirmPassword) {
      e.preventDefault();
      alert("Please fill in all fields.");
      return;
    }
    if (newPassword.length < 6) {
      e.preventDefault();
      alert("Password must be at least 6 characters long.");
      return;
    }
    if (newPassword !== confirmPassword) {
      e.preventDefault();
      alert("Passwords do not match.");
      return;
    }
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      e.preventDefault();
      alert("Please enter a valid email address.");
      return;
    }
  });
}

  if (searchBox && bookTable) {
    searchBox.addEventListener("input", function () {
      const keyword = searchBox.value.toLowerCase();
      const rows = bookTable.querySelectorAll("tbody tr");

      rows.forEach(row => {
        const title = row.querySelector("td:nth-child(2)")?.textContent.toLowerCase() || "";
        row.style.display = title.includes(keyword) ? "" : "none";
      });
    });
  }

  // New: Search for book cards in grid view
  const booksGrid = document.querySelector('.books-grid');
  if (searchBox && booksGrid) {
    searchBox.addEventListener('input', function () {
      const keyword = searchBox.value.toLowerCase();
      const cards = booksGrid.querySelectorAll('.book-card');
      cards.forEach(card => {
        const title = card.querySelector('.book-title')?.textContent.toLowerCase() || "";
        card.style.display = title.includes(keyword) ? '' : 'none';
      });
    });
  }
});
