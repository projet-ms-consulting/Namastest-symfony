let isEnPrep = document.currentScript.getAttribute('data-isEnPrep');
let isEnCours = document.currentScript.getAttribute('data-isEnCours');

if (isEnPrep === 'true')
{
    // TOGGLE LA VISIBILITE DU FORMULAIRE D'AJOUT DE CATALOGUE

    let bouton = document.getElementById("bouton-afficher");
    let bouton2 = document.getElementById("bouton-cacher");
    let error = document.querySelector(".invalid-feedback");

    if (error !== null) {
        showForm();
    }

    bouton.onclick = showForm;
    bouton2.onclick = showForm;

    function showForm() {
        let form = document.querySelector("#form-add-catalogue");
        form.classList.toggle("d-none");
        let icone = document.getElementById("bouton-afficher");
        icone.classList.toggle("d-none");

        let textes = document.querySelectorAll(".text-ab-link");
        textes.forEach((text) => {
            if (screen.width >= 990) {
                if (text.parentElement.classList.contains('btn-form-cat')) {
                    text.classList.toggle("d-none");
                }
                else {
                    text.classList.toggle("d-none");
                    text.parentElement.classList.toggle("petit-btn");
                }

            } else {
                if (text.parentElement.classList.contains('btn-form-cat')) {
                    text.parentElement.classList.toggle("petit-btn");
                }
            }
        })

    }
}

if (isEnCours === 'false') {
    //TOGGLE LES DETAILS DU TEST
    const tests = document.querySelectorAll('.test');

    tests.forEach((test) => {
        test.addEventListener("click", function(e) {

            let longue = this.querySelector('#test-description-long');
            let courte = this.querySelector('#test-description-short');
            let nomParent = this.querySelector('#template-nom-parent');
            let descrParent = this.querySelector('#template-description-parent');
            let catalogueShort = this.querySelector('#catalogue-short');
            let catalogueLong = this.querySelector('#catalogue-long');

            longue.classList.toggle("d-none");
            courte.classList.toggle("d-none");
            if (isEnPrep === 'true') {
                nomParent.classList.toggle("d-none");
                descrParent.classList.toggle("d-none");
            }
            catalogueShort.classList.toggle("d-none");
            catalogueLong.classList.toggle("d-none");
        })
    });
}


//PERMET DE NE PAS AFFICHER LES INFOS QUAND ON CLIQUE SUR UN ENFANT AYANT LA CLASSE no-info
$(".no-info").click(function(e) {
    e.stopPropagation();
});

if (isEnPrep === 'true' || isEnCours === 'true') {
//AFFICHER LES INFORMATIONS
    let infos = document.querySelector('#bouton-afficher-informations');

    infos.addEventListener('click', function () {
        let info = document.querySelector(".information-templates-tests");
        let titre = document.querySelector(".title-info");
        titre.classList.toggle("d-none");
        info.classList.toggle("d-none");
    })
}