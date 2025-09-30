function initModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;

    const closeBtn = modal.querySelector('.close');
    const show = modal.dataset.show === "true";

    if (show) {
        modal.style.display = "block";
        // Optional: auto-close after 3 seconds
        setTimeout(() => { modal.style.display = "none"; }, 3000);
    }

    closeBtn.onclick = () => modal.style.display = "none";

    window.onclick = (event) => {
        if (event.target === modal) modal.style.display = "none";
    };
}
