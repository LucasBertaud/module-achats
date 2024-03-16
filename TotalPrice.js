const categories = document.querySelectorAll("#category");
const prices = document.querySelectorAll("#price");
const quantities = document.querySelectorAll("#quantity");

categories.forEach((category, i)=>category.addEventListener("change", (e) => handler(e, i)));
quantities.forEach((quantity, i)=>quantity.addEventListener("keyup", (e)=> handler(e, i)));
quantities.forEach((quantity, i)=>quantity.addEventListener("change", (e)=> handler(e, i)));

function handler(e, i) {
    const quantity = quantities[i].value;
    const option = categories[i].options[categories[i].selectedIndex];
    const unitPrice = option.dataset["unitPrice"];
    prices[i].value = unitPrice * quantity;
}