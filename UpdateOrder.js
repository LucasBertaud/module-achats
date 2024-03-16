const updateBtn = document.querySelectorAll("#update-btn");
const hiddenId = document.querySelector("#hidden-id")

updateBtn.forEach((e) => e.addEventListener("click", (e) => handleUpdate(e)));

function handleUpdate(e) {
    orderId = e.target.dataset["orderId"];
    hiddenId.value = orderId;
}