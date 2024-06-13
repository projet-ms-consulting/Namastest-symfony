let searchbar = document.getElementById('searchbar-campagne-list');
searchbar.onkeyup = search_campagne;

function search_campagne() {
    let input = document.getElementById('searchbar-campagne-list').value
    input=input.toLowerCase();
    let x = document.getElementsByClassName('campagne-nom');

    for (let i = 0; i < x.length; i++) {
        if (!x[i].innerHTML.toLowerCase().includes(input)) {
            x[i].parentElement.parentElement.parentElement.parentElement.parentElement.style.display="none";
            x[i].parentElement.parentElement.parentElement.parentElement.parentElement.classList.remove("d-flex");
        }
        else {
            x[i].parentElement.parentElement.parentElement.parentElement.parentElement.style.display="";
            x[i].parentElement.parentElement.parentElement.parentElement.parentElement.classList.add("d-flex");
        }
    }
}
