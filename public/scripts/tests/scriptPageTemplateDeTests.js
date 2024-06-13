let boutonDisplayInformations = document.getElementById('bouton-afficher-informations');
let boutonDisplayTests = document.getElementById('bouton-afficher-cas-de-test')


boutonDisplayInformations.onclick = showInformations;
boutonDisplayTests.onclick = showTests;


function showInformations(){
    let informations = document.querySelector('.information-templates-tests');
    informations.classList.toggle('d-none');
}

function showTests(){
    let tests = document.querySelector('.display-tests');
    tests.classList.toggle('d-none')

}
