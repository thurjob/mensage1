// Profile page JavaScript

// Mock user data
const mockUsers = {
  user1: {
    id: 1,
    username: "user1",
    fullName: "User One",
    profilePic: "/placeholder.svg?height=150&width=150",
    bio: "Photographer and traveler ✈️",
    website: "https://example.com",
    postsCount: 42,
    followersCount: 1024,
    followingCount: 256,
    verified: true,
    posts: [
      {
        id: 1,
        image: "/placeholder.svg?height=300&width=300",
        likes: 125,
        comments: 24,
      },
      {
        id: 2,
        image: "/placeholder.svg?height=300&width=300",
        likes: 89,
        comments: 12,
      },
      {
        id: 3,
        image: "/placeholder.svg?height=300&width=300",
        likes: 256,
        comments: 36,
      },
      {
        id: 4,
        image: "/placeholder.svg?height=300&width=300",
        likes: 78,
        comments: 8,
      },
      {
        id: 5,
        image: "/placeholder.svg?height=300&width=300",
        likes: 112,
        comments: 18,
      },
      {
        id: 6,
        image: "/placeholder.svg?height=300&width=300",
        likes: 45,
        comments: 5,
      },
    ],
    savedPosts: [
      {
        id: 7,
        image: "/placeholder.svg?height=300&width=300",
        likes: 67,
        comments: 9,
      },
      {
        id: 8,
        image: "/placeholder.svg?height=300&width=300",
        likes: 134,
        comments: 21,
      },
    ],
  },
  user2: {
    id: 2,
    username: "user2",
    fullName: "User Two",
    profilePic: "/placeholder.svg?height=150&width=150",
    bio: "Food lover and chef 🍕",
    website: "https://example.org",
    postsCount: 28,
    followersCount: 512,
    followingCount: 128,
    verified: false,
    posts: [
      {
        id: 9,
        image: "/placeholder.svg?height=300&width=300",
        likes: 45,
        comments: 7,
      },
      {
        id: 10,
        image: "/placeholder.svg?height=300&width=300",
        likes: 67,
        comments: 9,
      },
      {
        id: 11,
        image: "/placeholder.svg?height=300&width=300",
        likes: 89,
        comments: 12,
      },
    ],
    savedPosts: [],
  },
};

// Get username from URL
function getUsernameFromURL() {
  const urlParams = new URLSearchParams(window.location.search);
  return urlParams.get("username") || getCurrentUser().username;
}

// Get current logged in user
function getCurrentUser() {
  return JSON.parse(localStorage.getItem("user") || '{"username": "user1"}');
}

// Load user profile
async function loadUserProfile() {
  const username = getUsernameFromURL();
  const currentUser = getCurrentUser();

  try {
    // In a real app, this would be an API call
    // const response = await fetch(`api/users.php?username=${username}`);
    // const userData = await response.json();

    // Using mock data for demonstration
    const userData = mockUsers[username] || mockUsers["user1"];

    // Update profile information
    document.getElementById("profile-username").textContent = userData.username;
    document.getElementById("profile-fullname").textContent = userData.fullName;
    document.getElementById("profile-image").src = userData.profilePic;
    document.getElementById("profile-bio").textContent = userData.bio;

    const websiteLink = document.getElementById("profile-website");
    websiteLink.textContent = userData.website.replace(/^https?:\/\//, "");
    websiteLink.href = userData.website;

    document.getElementById("posts-count").textContent = userData.postsCount;
    document.getElementById("followers-count").textContent =
      userData.followersCount;
    document.getElementById("following-count").textContent =
      userData.followingCount;

    // Show verified badge if applicable
    const verifiedBadge = document.getElementById("verified-badge");
    if (userData.verified) {
      verifiedBadge.style.display = "inline";
    } else {
      verifiedBadge.style.display = "none";
    }

    // Show edit profile button only if viewing own profile
    const editProfileBtn = document.getElementById("edit-profile-btn");
    if (username === currentUser.username) {
      editProfileBtn.style.display = "block";
    } else {
      editProfileBtn.style.display = "none";
      // Show follow button instead (would be implemented in a real app)
    }

    // Load posts
    loadUserPosts(userData.posts);
    loadSavedPosts(userData.savedPosts);
  } catch (error) {
    console.error("Error loading user profile:", error);
    alert("Failed to load user profile. Please try again later.");
  }
}

// Load user posts
function loadUserPosts(posts) {
  const postsGrid = document.getElementById("posts-grid");
  if (!postsGrid) return;

  if (posts.length === 0) {
    postsGrid.innerHTML = '<p class="no-posts">No posts yet.</p>';
    return;
  }

  const postsHTML = posts.map((post) => createPostGridItem(post)).join("");
  postsGrid.innerHTML = postsHTML;
}

// Load saved posts
function loadSavedPosts(posts) {
  const savedGrid = document.getElementById("saved-grid");
  if (!savedGrid) return;

  if (posts.length === 0) {
    savedGrid.innerHTML = '<p class="no-posts">No saved posts yet.</p>';
    return;
  }

  const postsHTML = posts.map((post) => createPostGridItem(post)).join("");
  savedGrid.innerHTML = postsHTML;
}

// Create post grid item HTML
function createPostGridItem(post) {
  return `
          <div class="grid-item" data-id="${post.id}">
              <img src="${post.image}" alt="Post">
              <div class="grid-item-overlay">
                  <div class="grid-item-stats">
                      <div class="grid-item-stat">
                          <i class="fas fa-heart"></i>
                          <span>${post.likes}</span>
                      </div>
                      <div class="grid-item-stat">
                          <i class="fas fa-comment"></i>
                          <span>${post.comments}</span>
                      </div>
                  </div>
              </div>
          </div>
      `;
}

// Handle tab switching
function setupTabSwitching() {
  const tabButtons = document.querySelectorAll(".tab-btn");
  const tabContents = document.querySelectorAll(".tab-content");

  tabButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const tabName = this.getAttribute("data-tab");

      // Update active tab button
      tabButtons.forEach((btn) => btn.classList.remove("active"));
      this.classList.add("active");

      // Show selected tab content
      tabContents.forEach((content) => {
        content.classList.remove("active");
        if (content.id === `${tabName}-tab`) {
          content.classList.add("active");
        }
      });
    });
  });
}

// Handle edit profile
function setupEditProfile() {
  const editProfileBtn = document.getElementById("edit-profile-btn");
  const editProfileModal = document.getElementById("edit-profile-modal");
  const editProfileForm = document.getElementById("edit-profile-form");
  const closeBtn = editProfileModal.querySelector(".close");

  // Open modal
  editProfileBtn.addEventListener("click", () => {
    // Populate form with current user data
    const user = getCurrentUser();
    document.getElementById("edit-fullname").value = user.fullName || "";
    document.getElementById("edit-username").value = user.username || "";
    document.getElementById("edit-bio").value = user.bio || "";
    document.getElementById("edit-website").value = user.website || "";
    document.getElementById("edit-email").value = user.email || "";

    editProfileModal.style.display = "block";
  });

  // Close modal
  closeBtn.addEventListener("click", () => {
    editProfileModal.style.display = "none";
  });

  // Close when clicking outside the modal
  window.addEventListener("click", (e) => {
    if (e.target === editProfileModal) {
      editProfileModal.style.display = "none";
    }
  });

  // Submit form
  editProfileForm.addEventListener("submit", (e) => {
    e.preventDefault();

    const updatedUser = {
      ...getCurrentUser(),
      fullName: document.getElementById("edit-fullname").value,
      username: document.getElementById("edit-username").value,
      bio: document.getElementById("edit-bio").value,
      website: document.getElementById("edit-website").value,
      email: document.getElementById("edit-email").value,
    };

    // In a real app, this would be an API call
    // updateUserProfile(updatedUser);

    // For demo, just update local storage
    localStorage.setItem("user", JSON.stringify(updatedUser));

    // Update UI
    document.getElementById("profile-fullname").textContent =
      updatedUser.fullName;
    document.getElementById("profile-username").textContent =
      updatedUser.username;
    document.getElementById("profile-bio").textContent = updatedUser.bio;

    const websiteLink = document.getElementById("profile-website");
    websiteLink.textContent = updatedUser.website.replace(/^https?:\/\//, "");
    websiteLink.href = updatedUser.website;

    // Close modal
    editProfileModal.style.display = "none";

    // Show success message
    alert("Profile updated successfully!");
  });
}

// Mock checkAuth function (replace with actual implementation)
function checkAuth() {
  // In a real application, this function would check if the user is authenticated.
  // For this example, we'll just log a message to the console.
  console.log("Authentication check placeholder.");
}

// Initialize the page
document.addEventListener("DOMContentLoaded", () => {
  // Check if user is logged in (from auth.js)
  if (typeof checkAuth === "function") {
    checkAuth();
  }

  // Load user profile
  loadUserProfile();

  // Setup tab switching
  setupTabSwitching();

  // Setup edit profile functionality
  setupEditProfile();
});
