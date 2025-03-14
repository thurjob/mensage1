// Authentication related functionality

// Check if user is logged in
function checkAuth() {
  const token = localStorage.getItem("token");
  const user = JSON.parse(localStorage.getItem("user") || "null");

  if (!token || !user) {
    // Show login modal if not logged in
    showLoginModal();
    return false;
  }

  // Update UI with user info
  updateUserUI(user);
  return true;
}

// Update UI elements with user information
function updateUserUI(user) {
  // Update profile link
  const profileLink = document.getElementById("profile-link");
  if (profileLink) {
    profileLink.href = `profile.html?username=${user.username}`;
  }

  // Update sidebar user info if exists
  const sidebarUsername = document.getElementById("sidebar-username");
  const sidebarFullname = document.getElementById("sidebar-fullname");
  const sidebarProfilePic = document.getElementById("sidebar-profile-pic");

  if (sidebarUsername) sidebarUsername.textContent = user.username;
  if (sidebarFullname) sidebarFullname.textContent = user.fullName;
  if (sidebarProfilePic)
    sidebarProfilePic.src =
      user.profilePic || "/placeholder.svg?height=60&width=60";

  // Check if user is verified and show badge if needed
  const verifiedBadge = document.getElementById("verified-badge");
  if (verifiedBadge && user.verified) {
    verifiedBadge.style.display = "inline";
  }
}

// Show login modal
function showLoginModal() {
  const loginModal = document.getElementById("login-modal");
  if (loginModal) {
    loginModal.style.display = "block";
  }
}

// Show register modal
function showRegisterModal() {
  const registerModal = document.getElementById("register-modal");
  const loginModal = document.getElementById("login-modal");

  if (loginModal) loginModal.style.display = "none";
  if (registerModal) registerModal.style.display = "block";
}

// Close modals
function closeModals() {
  const modals = document.querySelectorAll(".modal");
  modals.forEach((modal) => {
    modal.style.display = "none";
  });
}

// Login function
async function login(username, password) {
  try {
    // In a real app, this would be an API call
    const response = await fetch("api/login.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ username, password }),
    });

    if (!response.ok) {
      throw new Error("Login failed");
    }

    const data = await response.json();

    // Store token and user data
    localStorage.setItem("token", data.token);
    localStorage.setItem("user", JSON.stringify(data.user));

    // Close modal and update UI
    closeModals();
    updateUserUI(data.user);

    // Reload page to reflect logged in state
    window.location.reload();
  } catch (error) {
    alert("Login failed: " + error.message);
  }
}

// Register function
async function register(fullName, username, email, password) {
  try {
    // In a real app, this would be an API call
    const response = await fetch("api/register.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ fullName, username, email, password }),
    });

    if (!response.ok) {
      throw new Error("Registration failed");
    }

    const data = await response.json();

    // Store token and user data
    localStorage.setItem("token", data.token);
    localStorage.setItem("user", JSON.stringify(data.user));

    // Close modal and update UI
    closeModals();
    updateUserUI(data.user);

    // Reload page to reflect logged in state
    window.location.reload();
  } catch (error) {
    alert("Registration failed: " + error.message);
  }
}

// Logout function
function logout() {
  localStorage.removeItem("token");
  localStorage.removeItem("user");
  window.location.href = "index.html";
}

// Event listeners
document.addEventListener("DOMContentLoaded", () => {
  // Check if user is logged in
  checkAuth();

  // Login form submission
  const loginForm = document.getElementById("login-form");
  if (loginForm) {
    loginForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const username = document.getElementById("login-username").value;
      const password = document.getElementById("login-password").value;
      login(username, password);
    });
  }

  // Register form submission
  const registerForm = document.getElementById("register-form");
  if (registerForm) {
    registerForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const fullName = document.getElementById("register-fullname").value;
      const username = document.getElementById("register-username").value;
      const email = document.getElementById("register-email").value;
      const password = document.getElementById("register-password").value;
      register(fullName, username, email, password);
    });
  }

  // Switch between login and register
  const registerLink = document.getElementById("register-link");
  if (registerLink) {
    registerLink.addEventListener("click", (e) => {
      e.preventDefault();
      showRegisterModal();
    });
  }

  const loginLink = document.getElementById("login-link");
  if (loginLink) {
    loginLink.addEventListener("click", (e) => {
      e.preventDefault();
      showLoginModal();
    });
  }

  // Close modal when clicking on X or outside the modal
  const closeButtons = document.querySelectorAll(".close");
  closeButtons.forEach((button) => {
    button.addEventListener("click", closeModals);
  });

  window.addEventListener("click", (e) => {
    if (e.target.classList.contains("modal")) {
      closeModals();
    }
  });
});
