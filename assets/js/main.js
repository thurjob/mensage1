// Toggle mobile menu
document.addEventListener("DOMContentLoaded", () => {
  const menuBtn = document.querySelector(".menu-btn")
  const mobileMenu = document.querySelector(".mobile-menu")

  if (menuBtn && mobileMenu) {
    menuBtn.addEventListener("click", () => {
      mobileMenu.classList.toggle("show")
    })
  }

  // Close mobile menu when clicking outside
  document.addEventListener("click", (event) => {
    if (
      mobileMenu &&
      mobileMenu.classList.contains("show") &&
      !event.target.closest(".menu-btn") &&
      !event.target.closest(".mobile-menu")
    ) {
      mobileMenu.classList.remove("show")
    }
  })

  // Handle dark mode toggle
  const darkModeToggle = document.querySelector(".dark-mode-toggle")
  if (darkModeToggle) {
    darkModeToggle.addEventListener("click", () => {
      document.body.classList.toggle("dark-mode")

      // Save preference to server
      fetch("update-dark-mode.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "dark_mode=" + (document.body.classList.contains("dark-mode") ? 1 : 0),
      })
    })
  }
})

// Like post functionality
function likePost(postId, button) {
  fetch("like-post.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "post_id=" + postId,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Toggle like button appearance
        button.classList.toggle("liked")

        // Update SVG fill
        const svg = button.querySelector("svg")
        if (data.liked) {
          svg.setAttribute("fill", "currentColor")
        } else {
          svg.setAttribute("fill", "none")
        }

        // Update like count if available
        const likesCount = document.querySelector('.likes-count[data-post-id="' + postId + '"]')
        if (likesCount) {
          likesCount.textContent = data.likes + " likes"
        }
      }
    })
    .catch((error) => {
      console.error("Error:", error)
    })
}

// Follow user functionality
function followUser(userId, button) {
  fetch("follow-user.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "user_id=" + userId,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Toggle follow button appearance
        if (data.following) {
          button.textContent = "Following"
          button.classList.add("following")
        } else {
          button.textContent = "Follow"
          button.classList.remove("following")
        }

        // Update followers count if available
        const followersCount = document.querySelector(".followers-count")
        if (followersCount) {
          followersCount.textContent = data.followers_count
        }
      }
    })
    .catch((error) => {
      console.error("Error:", error)
    })
}

