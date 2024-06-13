let searchbar = document.getElementById('searchbar-campagne-detail');
searchbar.onkeyup = search_campagne;

function search_campagne() {
    let input = document.getElementById('searchbar-campagne-detail').value
    input=input.toLowerCase();
    let x = document.getElementsByClassName('test-nom');

    for (let i = 0; i < x.length; i++) {
        let parent = x[i].parentElement;
        if (!x[i].innerHTML.toLowerCase().includes(input)) {
            parent.style.display="none";
            parent.firstElementChild.firstElementChild.disabled = true;
        }
        else {
            parent.style.display="";
            parent.firstElementChild.firstElementChild.disabled = false;
        }
    }
}
