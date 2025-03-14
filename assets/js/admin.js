// Admin Dashboard JavaScript

// Mock data for admin dashboard
const mockStats = {
  totalUsers: 1250,
  totalPosts: 4872,
  totalLikes: 28945,
  totalComments: 9876,
};

const mockActivity = [
  {
    id: 1,
    type: "new_user",
    user: {
      id: 10,
      username: "newuser1",
      profilePic: "/placeholder.svg?height=40&width=40",
    },
    message: "New user registered",
    timestamp: "2025-03-14T08:30:00",
  },
  {
    id: 2,
    type: "new_post",
    user: {
      id: 5,
      username: "user5",
      profilePic: "/placeholder.svg?height=40&width=40",
    },
    message: "Created a new post",
    timestamp: "2025-03-14T08:15:00",
  },
  {
    id: 3,
    type: "report",
    user: {
      id: 8,
      username: "user8",
      profilePic: "/placeholder.svg?height=40&width=40",
    },
    message: "Reported a post for inappropriate content",
    timestamp: "2025-03-14T07:45:00",
  },
  {
    id: 4,
    type: "verification",
    user: {
      id: 12,
      username: "user12",
      profilePic: "/placeholder.svg?height=40&width=40",
    },
    message: "Requested verification",
    timestamp: "2025-03-14T07:30:00",
  },
  {
    id: 5,
    type: "login",
    user: {
      id: 3,
      username: "user3",
      profilePic: "/placeholder.svg?height=40&width=40",
    },
    message: "Logged in from a new device",
    timestamp: "2025-03-14T07:15:00",
  },
];

const mockUsers = [
  {
    id: 1,
    username: "user1",
    fullName: "User One",
    email: "user1@example.com",
    profilePic: "/placeholder.svg?height=40&width=40",
    postsCount: 42,
    followersCount: 1024,
    status: "active",
    verified: true,
    role: "user",
  },
  {
    id: 2,
    username: "user2",
    fullName: "User Two",
    email: "user2@example.com",
    profilePic: "/placeholder.svg?height=40&width=40",
    postsCount: 28,
    followersCount: 512,
    status: "active",
    verified: false,
    role: "user",
  },
  {
    id: 3,
    username: "user3",
    fullName: "User Three",
    email: "user3@example.com",
    profilePic: "/placeholder.svg?height=40&width=40",
    postsCount: 15,
    followersCount: 256,
    status: "active",
    verified: true,
    role: "moderator",
  },
  {
    id: 4,
    username: "admin",
    fullName: "Admin User",
    email: "admin@example.com",
    profilePic: "/placeholder.svg?height=40&width=40",
    postsCount: 5,
    followersCount: 128,
    status: "active",
    verified: true,
    role: "admin",
  },
  {
    id: 5,
    username: "user5",
    fullName: "User Five",
    email: "user5@example.com",
    profilePic: "/placeholder.svg?height=40&width=40",
    postsCount: 8,
    followersCount: 64,
    status: "inactive",
    verified: false,
    role: "user",
  },
];

const mockPosts = [
  {
    id: 1,
    user: {
      id: 1,
      username: "user1",
      profilePic: "/placeholder.svg?height=40&width=40",
    },
    image: "/placeholder.svg?height=300&width=300",
    likes: 125,
    comments: 24,
    reported: false,
    timestamp: "2025-03-10T12:00:00",
  },
  {
    id: 2,
    user: {
      id: 2,
      username: "user2",
      profilePic: "/placeholder.svg?height=40&width=40",
    },
    image: "/placeholder.svg?height=300&width=300",
    likes: 89,
    comments: 12,
    reported: true,
    timestamp: "2025-03-09T18:30:00",
  },
  {
    id: 3,
    user: {
      id: 3,
      username: "user3",
      profilePic: "/placeholder.svg?height=40&width=40",
    },
    image: "/placeholder.svg?height=300&width=300",
    likes: 256,
    comments: 36,
    reported: false,
    timestamp: "2025-03-08T09:15:00",
  },
  {
    id: 4,
    user: {
      id: 1,
      username: "user1",
      profilePic: "/placeholder.svg?height=40&width=40",
    },
    image: "/placeholder.svg?height=300&width=300",
    likes: 78,
    comments: 8,
    reported: false,
    timestamp: "2025-03-07T14:45:00",
  },
];

const mockReports = [
  {
    id: 1,
    type: "post",
    itemId: 2,
    reportedBy: {
      id: 3,
      username: "user3",
    },
    reason: "Inappropriate content",
    date: "2025-03-14T07:45:00",
    status: "pending",
  },
  {
    id: 2,
    type: "comment",
    itemId: 5,
    reportedBy: {
      id: 2,
      username: "user2",
    },
    reason: "Harassment",
    date: "2025-03-13T15:30:00",
    status: "reviewed",
  },
  {
    id: 3,
    type: "user",
    itemId: 8,
    reportedBy: {
      id: 1,
      username: "user1",
    },
    reason: "Fake account",
    date: "2025-03-12T09:15:00",
    status: "resolved",
  },
];

// Format timestamp to relative time
function formatRelativeTime(timestamp) {
  const now = new Date();
  const activityDate = new Date(timestamp);
  const diffInSeconds = Math.floor((now - activityDate) / 1000);

  if (diffInSeconds < 60) {
    return `${diffInSeconds}s ago`;
  } else if (diffInSeconds < 3600) {
    return `${Math.floor(diffInSeconds / 60)}m ago`;
  } else if (diffInSeconds < 86400) {
    return `${Math.floor(diffInSeconds / 3600)}h ago`;
  } else if (diffInSeconds < 604800) {
    return `${Math.floor(diffInSeconds / 86400)}d ago`;
  } else {
    return activityDate.toLocaleDateString();
  }
}

// Load dashboard stats
function loadDashboardStats() {
  document.getElementById("total-users").textContent =
    mockStats.totalUsers.toLocaleString();
  document.getElementById("total-posts").textContent =
    mockStats.totalPosts.toLocaleString();
  document.getElementById("total-likes").textContent =
    mockStats.totalLikes.toLocaleString();
  document.getElementById("total-comments").textContent =
    mockStats.totalComments.toLocaleString();
}

// Load recent activity
function loadRecentActivity() {
  const activityList = document.getElementById("activity-list");
  if (!activityList) return;

  const activityHTML = mockActivity
    .map((activity) => {
      let iconClass = "fas fa-user";

      switch (activity.type) {
        case "new_user":
          iconClass = "fas fa-user-plus";
          break;
        case "new_post":
          iconClass = "fas fa-image";
          break;
        case "report":
          iconClass = "fas fa-flag";
          break;
        case "verification":
          iconClass = "fas fa-check-circle";
          break;
        case "login":
          iconClass = "fas fa-sign-in-alt";
          break;
      }

      return `
              <div class="activity-item">
                  <div class="activity-icon">
                      <i class="${iconClass}"></i>
                  </div>
                  <div class="activity-info">
                      <p><strong>${activity.user.username}</strong> ${
        activity.message
      }</p>
                      <p class="time">${formatRelativeTime(
                        activity.timestamp
                      )}</p>
                  </div>
              </div>
          `;
    })
    .join("");

  activityList.innerHTML = activityHTML;
}

// Load users table
function loadUsersTable() {
  const usersTableBody = document.getElementById("users-table-body");
  if (!usersTableBody) return;

  const usersHTML = mockUsers
    .map((user) => {
      const verifiedStatus = user.verified
        ? '<span class="verified-badge"><i class="fas fa-check-circle"></i> Yes</span>'
        : "<span>No</span>";

      return `
              <tr data-id="${user.id}">
                  <td>${user.id}</td>
                  <td>${user.username}</td>
                  <td>${user.fullName}</td>
                  <td>${user.email}</td>
                  <td>${user.postsCount}</td>
                  <td>${user.followersCount}</td>
                  <td><span class="status-badge status-${user.status}">${user.status}</span></td>
                  <td>${verifiedStatus}</td>
                  <td class="user-actions-cell">
                      <button class="action-btn view-btn" data-action="view" data-id="${user.id}">
                          <i class="fas fa-eye"></i>
                      </button>
                      <button class="action-btn edit-btn" data-action="edit" data-id="${user.id}">
                          <i class="fas fa-edit"></i>
                      </button>
                      <button class="action-btn delete-btn" data-action="delete" data-id="${user.id}">
                          <i class="fas fa-trash"></i>
                      </button>
                  </td>
              </tr>
          `;
    })
    .join("");

  usersTableBody.innerHTML = usersHTML;

  // Add event listeners to action buttons
  const actionButtons = usersTableBody.querySelectorAll(".action-btn");
  actionButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const action = this.getAttribute("data-action");
      const userId = this.getAttribute("data-id");

      if (action === "view") {
        viewUser(userId);
      } else if (action === "edit") {
        viewUser(userId); // Same as view for this demo
      } else if (action === "delete") {
        if (confirm("Are you sure you want to delete this user?")) {
          // In a real app, this would be an API call
          alert("User deleted successfully!");
          this.closest("tr").remove();
        }
      }
    });
  });
}

// Load posts grid
function loadPostsGrid() {
  const postsGrid = document.getElementById("admin-posts-grid");
  if (!postsGrid) return;

  const postsHTML = mockPosts
    .map((post) => {
      const reportedBadge = post.reported
        ? '<span class="report-badge"><i class="fas fa-flag"></i></span>'
        : "";

      return `
              <div class="admin-post-item" data-id="${post.id}">
                  <img src="${post.image}" alt="Post">
                  <div class="admin-post-overlay">
                      <div class="username">${post.user.username}</div>
                      <div class="admin-post-stats">
                          <span><i class="fas fa-heart"></i> ${post.likes}</span>
                          <span><i class="fas fa-comment"></i> ${post.comments}</span>
                      </div>
                  </div>
                  ${reportedBadge}
                  <div class="admin-post-actions">
                      <button class="view-post" data-id="${post.id}">
                          <i class="fas fa-eye"></i>
                      </button>
                      <button class="delete-post" data-id="${post.id}">
                          <i class="fas fa-trash"></i>
                      </button>
                  </div>
              </div>
          `;
    })
    .join("");

  postsGrid.innerHTML = postsHTML;

  // Add event listeners to post actions
  const viewButtons = postsGrid.querySelectorAll(".view-post");
  const deleteButtons = postsGrid.querySelectorAll(".delete-post");

  viewButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const postId = this.getAttribute("data-id");
      // In a real app, this would open a modal with post details
      alert(`Viewing post ${postId}`);
    });
  });

  deleteButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const postId = this.getAttribute("data-id");
      if (confirm("Are you sure you want to delete this post?")) {
        // In a real app, this would be an API call
        alert(`Post ${postId} deleted successfully!`);
        this.closest(".admin-post-item").remove();
      }
    });
  });
}

// Load reports table
function loadReportsTable() {
  const reportsTableBody = document.getElementById("reports-table-body");
  if (!reportsTableBody) return;

  const reportsHTML = mockReports
    .map((report) => {
      let statusClass = "";
      switch (report.status) {
        case "pending":
          statusClass = "status-inactive";
          break;
        case "reviewed":
          statusClass = "status-active";
          break;
        case "resolved":
          statusClass = "status-verified";
          break;
      }

      return `
              <tr data-id="${report.id}">
                  <td>${report.id}</td>
                  <td>${report.type}</td>
                  <td>${report.itemId}</td>
                  <td>${report.reportedBy.username}</td>
                  <td>${report.reason}</td>
                  <td>${new Date(report.date).toLocaleString()}</td>
                  <td><span class="status-badge ${statusClass}">${
        report.status
      }</span></td>
                  <td class="user-actions-cell">
                      <button class="action-btn view-btn" data-action="view" data-id="${
                        report.id
                      }">
                          <i class="fas fa-eye"></i>
                      </button>
                      <button class="action-btn edit-btn" data-action="resolve" data-id="${
                        report.id
                      }">
                          <i class="fas fa-check"></i>
                      </button>
                      <button class="action-btn delete-btn" data-action="dismiss" data-id="${
                        report.id
                      }">
                          <i class="fas fa-times"></i>
                      </button>
                  </td>
              </tr>
          `;
    })
    .join("");

  reportsTableBody.innerHTML = reportsHTML;

  // Add event listeners to action buttons
  const actionButtons = reportsTableBody.querySelectorAll(".action-btn");
  actionButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const action = this.getAttribute("data-action");
      const reportId = this.getAttribute("data-id");

      if (action === "view") {
        // In a real app, this would open a modal with report details
        alert(`Viewing report ${reportId}`);
      } else if (action === "resolve") {
        if (confirm("Mark this report as resolved?")) {
          // In a real app, this would be an API call
          this.closest("tr").querySelector(".status-badge").textContent =
            "resolved";
          this.closest("tr").querySelector(".status-badge").className =
            "status-badge status-verified";
        }
      } else if (action === "dismiss") {
        if (confirm("Dismiss this report?")) {
          // In a real app, this would be an API call
          this.closest("tr").remove();
        }
      }
    });
  });
}

// View user details
function viewUser(userId) {
  const user = mockUsers.find((u) => u.id == userId);
  if (!user) return;

  // Populate modal with user data
  document.getElementById("modal-username").textContent = user.username;
  document.getElementById("modal-fullname").textContent = user.fullName;
  document.getElementById("modal-email").textContent = user.email;
  document.getElementById("modal-user-avatar").src = user.profilePic;
  document.getElementById("modal-posts").textContent = user.postsCount;
  document.getElementById("modal-followers").textContent = user.followersCount;
  document.getElementById("modal-following").textContent =
    user.followersCount / 2; // Just for demo

  // Set form values
  document.getElementById("user-status").value = user.status;
  document.getElementById("user-verified").checked = user.verified;
  document.getElementById("user-role").value = user.role;

  // Show modal
  document.getElementById("user-view-modal").style.display = "block";

  // Save changes button
  document.getElementById("save-user-changes").onclick = () => {
    const updatedUser = {
      ...user,
      status: document.getElementById("user-status").value,
      verified: document.getElementById("user-verified").checked,
      role: document.getElementById("user-role").value,
    };

    // In a real app, this would be an API call
    // updateUser(updatedUser);

    // Update UI
    const userRow = document.querySelector(`tr[data-id="${userId}"]`);
    if (userRow) {
      userRow.querySelector("td:nth-child(7) span").textContent =
        updatedUser.status;
      userRow.querySelector(
        "td:nth-child(7) span"
      ).className = `status-badge status-${updatedUser.status}`;

      const verifiedCell = userRow.querySelector("td:nth-child(8)");
      verifiedCell.innerHTML = updatedUser.verified
        ? '<span class="verified-badge"><i class="fas fa-check-circle"></i> Yes</span>'
        : "<span>No</span>";
    }

    // Close modal
    document.getElementById("user-view-modal").style.display = "none";

    // Show success message
    alert("User updated successfully!");
  };

  // Delete user button
  document.getElementById("delete-user").onclick = () => {
    if (confirm("Are you sure you want to delete this user?")) {
      // In a real app, this would be an API call
      // deleteUser(userId);

      // Update UI
      const userRow = document.querySelector(`tr[data-id="${userId}"]`);
      if (userRow) {
        userRow.remove();
      }

      // Close modal
      document.getElementById("user-view-modal").style.display = "none";

      // Show success message
      alert("User deleted successfully!");
    }
  };
}

// Setup tab switching
function setupTabSwitching() {
  const menuItems = document.querySelectorAll(".admin-menu li");
  const tabs = document.querySelectorAll(".admin-tab");

  menuItems.forEach((item) => {
    item.addEventListener("click", function () {
      if (this.id === "admin-logout") {
        if (confirm("Are you sure you want to logout?")) {
          window.location.href = "index.html";
        }
        return;
      }

      const tabName = this.getAttribute("data-tab");

      // Update active menu item
      menuItems.forEach((menuItem) => menuItem.classList.remove("active"));
      this.classList.add("active");

      // Show selected tab
      tabs.forEach((tab) => {
        tab.classList.remove("active");
        if (tab.id === `${tabName}-tab`) {
          tab.classList.add("active");
        }
      });
    });
  });
}

// Setup settings forms
function setupSettingsForms() {
  const generalSettingsForm = document.getElementById("general-settings-form");
  const emailSettingsForm = document.getElementById("email-settings-form");

  if (generalSettingsForm) {
    generalSettingsForm.addEventListener("submit", (e) => {
      e.preventDefault();
      // In a real app, this would be an API call
      alert("General settings saved successfully!");
    });
  }

  if (emailSettingsForm) {
    emailSettingsForm.addEventListener("submit", (e) => {
      e.preventDefault();
      // In a real app, this would be an API call
      alert("Email settings saved successfully!");
    });
  }
}

// Setup modal close functionality
function setupModals() {
  const modals = document.querySelectorAll(".modal");
  const closeButtons = document.querySelectorAll(".close");

  closeButtons.forEach((button) => {
    button.addEventListener("click", function () {
      this.closest(".modal").style.display = "none";
    });
  });

  window.addEventListener("click", (e) => {
    modals.forEach((modal) => {
      if (e.target === modal) {
        modal.style.display = "none";
      }
    });
  });
}

// Check if user is admin
function checkAdminAuth() {
  const user = JSON.parse(localStorage.getItem("user") || "null");

  if (!user || user.role !== "admin") {
    alert("You do not have permission to access the admin panel.");
    window.location.href = "index.html";
    return false;
  }

  // Update admin UI
  document.getElementById("admin-name").textContent =
    user.fullName || user.username;
  document.getElementById("admin-avatar").src =
    user.profilePic || "/placeholder.svg?height=40&width=40";

  return true;
}

// Initialize the admin dashboard
document.addEventListener("DOMContentLoaded", () => {
  // Check if user is admin
  if (!checkAdminAuth()) return;

  // Load dashboard data
  loadDashboardStats();
  loadRecentActivity();
  loadUsersTable();
  loadPostsGrid();
  loadReportsTable();

  // Setup UI interactions
  setupTabSwitching();
  setupSettingsForms();
  setupModals();
});
