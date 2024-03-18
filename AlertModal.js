document.querySelector("#btn-modal-alert").addEventListener("click", () => {
    const modal = document.querySelector("#modal-alert");
    
    if (modal.classList.contains("hidden")) {
        modal.classList.remove("hidden")
    } else {
        modal.classList.add("hidden")
    }
})