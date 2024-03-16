const buttons = document.querySelectorAll("#delete-btn");
const confirmDelete = document.querySelector("#confirm-delete");
const url = window.location.href;
let orderId;

buttons.forEach((button) => button.addEventListener("click", (e) => getOrderId(e)));

confirmDelete.addEventListener("click", processDelete);

function getOrderId(e){
    orderId = e.target.dataset["orderId"];
}

function processDelete(){
    const xhttp = new XMLHttpRequest();

    xhttp.onload = function() {
        location.reload(true);
    }

    xhttp.open("DELETE", url, false);
    xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhttp.send(`id=${orderId}`);
}