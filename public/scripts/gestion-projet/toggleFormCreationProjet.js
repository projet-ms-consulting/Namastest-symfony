let bouton = document.getElementById("bouton-creer");
let bouton2 = document.getElementById("bouton-cacher");
let error = document.querySelector(".invalid-feedback");

if (error !== null) {
    showForm();
}

bouton.onclick = showForm;
bouton2.onclick = showForm;

function showForm() {
    let form = document.querySelector(".form-projet");
    form.classList.toggle("d-none");
    let icone = document.getElementById("bouton-creer");
    icone.classList.toggle("d-none");
}
