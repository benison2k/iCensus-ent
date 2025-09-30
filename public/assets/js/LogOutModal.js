document.addEventListener("DOMContentLoaded", () => {
  const logoutBtn = document.getElementById("logoutBtn");
  const logoutModal = document.getElementById("logoutModal");
  const confirmLogout = document.getElementById("confirmLogout");
  const cancelLogout = document.getElementById("cancelLogout");

  if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
      logoutModal.style.display = "flex";
    });
  }

  if (cancelLogout) {
    cancelLogout.addEventListener("click", () => {
      logoutModal.style.display = "none";
    });
  }

  if (confirmLogout) {
    confirmLogout.addEventListener("click", () => {
      // Corrected Logout URL
      window.location.href = "/iCensus-ent/public/logout";
    });
  }

  window.addEventListener("click", (e) => {
    if (e.target === logoutModal) {
      logoutModal.style.display = "none";
    }
  });
});