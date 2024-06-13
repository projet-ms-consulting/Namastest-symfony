let searchbar = document.getElementById('searchbar-catalogue-list');
searchbar.onkeyup = search_catalogue;

function search_catalogue() {
    let input = document.getElementById('searchbar-catalogue-list').value
    input=input.toLowerCase();
    let x = document.getElementsByClassName('catalogue-nom');

    for (let i = 0; i < x.length; i++) {
        if (!x[i].innerHTML.toLowerCase().includes(input)) {
            x[i].parentElement.parentElement.parentElement.style.display="none";
            x[i].parentElement.parentElement.parentElement.classList.remove("d-flex");
        }
        else {
            x[i].parentElement.parentElement.parentElement.style.display="";
            x[i].parentElement.parentElement.parentElement.classList.add("d-flex");
        }
    }
}
