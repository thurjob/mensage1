// Create Post Page JavaScript

// Check if user is logged in
document.addEventListener("DOMContentLoaded", () => {
  // Check if user is logged in (from auth.js)
  // Mock checkAuth function for demonstration purposes. In a real application,
  // this would likely be defined in a separate auth.js file and imported.
  function checkAuth() {
    // Replace this with your actual authentication logic.
    // For example, check for the existence of a token in localStorage.
    const token = localStorage.getItem("token");
    return !!token; // Return true if the user is authenticated, false otherwise.
  }

  const isLoggedIn = checkAuth();
  if (!isLoggedIn) {
    return; // Stop execution if not logged in
  }

  // Get user data
  const user = JSON.parse(localStorage.getItem("user") || "null");
  if (user) {
    // Update user info
    document.getElementById("username").textContent = user.username;
    document.getElementById("user-avatar").src =
      user.profilePic || "/placeholder.svg?height=40&width=40";
  }

  // Setup file upload
  setupFileUpload();

  // Setup form actions
  setupFormActions();
});

// Setup file upload functionality
function setupFileUpload() {
  const uploadArea = document.getElementById("upload-area");
  const uploadPlaceholder = document.getElementById("upload-placeholder");
  const previewArea = document.getElementById("preview-area");
  const fileUpload = document.getElementById("file-upload");
  const uploadBtn = document.getElementById("upload-btn");
  const imagePreview = document.getElementById("image-preview");
  const changeImageBtn = document.getElementById("change-image");
  const shareBtn = document.getElementById("share-btn");

  // Handle drag and drop
  uploadArea.addEventListener("dragover", (e) => {
    e.preventDefault();
    uploadPlaceholder.classList.add("drag-over");
  });

  uploadArea.addEventListener("dragleave", () => {
    uploadPlaceholder.classList.remove("drag-over");
  });

  uploadArea.addEventListener("drop", (e) => {
    e.preventDefault();
    uploadPlaceholder.classList.remove("drag-over");

    if (e.dataTransfer.files.length) {
      handleFile(e.dataTransfer.files[0]);
    }
  });

  // Handle file selection via button
  uploadBtn.addEventListener("click", () => {
    fileUpload.click();
  });

  fileUpload.addEventListener("change", function () {
    if (this.files.length) {
      handleFile(this.files[0]);
    }
  });

  // Handle change image button
  changeImageBtn.addEventListener("click", () => {
    uploadPlaceholder.style.display = "flex";
    previewArea.style.display = "none";
    shareBtn.disabled = true;
  });

  // Handle file processing
  function handleFile(file) {
    if (!file.type.match("image.*")) {
      alert("Please select an image file.");
      return;
    }

    const reader = new FileReader();

    reader.onload = (e) => {
      imagePreview.src = e.target.result;
      uploadPlaceholder.style.display = "none";
      previewArea.style.display = "block";
      shareBtn.disabled = false;
    };

    reader.readAsDataURL(file);
  }
}

// Setup form actions
function setupFormActions() {
  const discardBtn = document.getElementById("discard-btn");
  const shareBtn = document.getElementById("share-btn");
  const captionInput = document.getElementById("caption");
  const locationInput = document.getElementById("location");
  const tagsInput = document.getElementById("tags");
  const altTextInput = document.getElementById("alt-text");
  const commentsAllowed = document.getElementById("comments-allowed");

  // Handle discard button
  discardBtn.addEventListener("click", () => {
    if (confirm("Discard this post? Your changes will not be saved.")) {
      window.location.href = "index.html";
    }
  });

  // Handle share button
  shareBtn.addEventListener("click", () => {
    // Get form data
    const postData = {
      caption: captionInput.value,
      location: locationInput.value,
      tags: tagsInput.value.split(",").map((tag) => tag.trim()),
      altText: altTextInput.value,
      commentsAllowed: commentsAllowed.checked,
      image: document.getElementById("image-preview").src,
    };

    // In a real app, this would be an API call
    // createPost(postData);

    // Show loading state
    shareBtn.disabled = true;
    shareBtn.textContent = "Sharing...";

    // Simulate API call
    setTimeout(() => {
      alert("Post created successfully!");
      window.location.href = "index.html";
    }, 1500);
  });
}
