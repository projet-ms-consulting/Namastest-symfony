let searchbar = document.getElementById('searchbar-catalogue-detail');
searchbar.onkeyup = search_catalogue;

function search_catalogue() {
    // on récupère ce que l'utilisateur écrit dans la barre de recherche, et on le
    // met en minuscules
    let input = document.getElementById('searchbar-catalogue-detail').value
    input=input.toLowerCase();
    // on récupère les noms de tous les tests
    let x = document.getElementsByClassName('test-nom');

    // pour chaque nom
    for (let i = 0; i < x.length; i++) {
        // on récupère tout le tr parent contenant le test
        let parent = x[i].parentElement.parentElement;
        // si le test ne correspond pas à ce que l'utilisateur écrit
        if (!x[i].innerHTML.toLowerCase().includes(input)) {
            // on cache son parent
            parent.style.display="none";
            // on disable la checkbox de ce test
            parent.firstElementChild.firstElementChild.disabled = true;
        }
        else {
            parent.style.display="";
            parent.firstElementChild.firstElementChild.disabled = false;
        }
    }
}
