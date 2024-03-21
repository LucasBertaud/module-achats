const modal = document.querySelector("#modal-alert");
document.querySelector("#btn-modal-alert").addEventListener("click", () => {
    if (modal.classList.contains("hidden")) {
        modal.classList.remove("hidden")
    } else {
        modal.classList.add("hidden")
    }
})