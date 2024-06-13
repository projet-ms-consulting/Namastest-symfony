// TOGGLE LA VISIBILITE DU FORMULAIRE

let bouton = document.getElementById("bouton-afficher");
let bouton2 = document.getElementById("bouton-cacher");
let error = document.querySelector(".invalid-feedback");

if (error !== null) {
    showForm();
}

bouton.onclick = showForm;
bouton2.onclick = showForm;

function showForm() {
    let form = document.querySelector("#form-add-campagne");
    form.classList.toggle("d-none");
    let afficher = document.querySelector("#bouton-afficher");
    afficher.classList.toggle("d-none");
    let cacher = document.querySelector("#bouton-cacher");
    cacher.classList.toggle("d-none");
}

let infos = document.querySelector('#bouton-afficher-informations');

infos.addEventListener('click', function () {
    let info = document.querySelector(".information-templates-tests");
    let titre = document.querySelector(".title-info");
    titre.classList.toggle("d-none");
    info.classList.toggle("d-none");
})
