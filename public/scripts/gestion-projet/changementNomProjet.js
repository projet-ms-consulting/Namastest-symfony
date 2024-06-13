let input_modifier=document.createElement("input");
let button_modifier=document.createElement("button");
let button_annuler=document.createElement("button");

function renommer(nom, id){
    let tableau=document.getElementsByTagName('h3');
    for(let titre of tableau){
        titre.setAttribute("style", "display:initial;");
    }

    let titre_projet=document.getElementById(nom);

    input_modifier.type="text";
    input_modifier.value=nom;
    input_modifier.name= "input" + id;
    input_modifier.setAttribute("style", "width: 70%;");
    //input_modifier.setAttribute("style", "height: 37px;");

    button_annuler.setAttribute("class", "btn btn-primary py-2");
    button_annuler.innerHTML="<i class='fas fa-times-circle'></i>";
    button_annuler.setAttribute("style", "margin-left: 5px;");
    button_annuler.title="Annuler";

    button_annuler.onclick=function (){
        window.location.reload()
    }

    button_modifier.setAttribute("class", "btn btn-primary py-2");
    button_modifier.innerHTML="<i class='fa fa-check'></i>";
    button_modifier.setAttribute("style", "margin-left: 5px;")
    button_modifier.title="Valider";

    titre_projet.after(input_modifier);
    titre_projet.setAttribute("style", "display:none;");
    input_modifier.focus();
    input_modifier.after(button_annuler);

    input_modifier.addEventListener("keydown", function () {

        button_annuler.setAttribute("style", "display:none;");
        input_modifier.after(button_modifier);

    })

    input_modifier.onblur=function (){
        setTimeout(function(){
            let parent=button_modifier.parentElement;

            input_modifier.setAttribute("style", "display:none;");
            button_annuler.setAttribute("style", "display:none;");

            for(let titre of tableau){
                titre.setAttribute("style", "display:initial;");
            }

            if(parent){
                parent.removeChild(button_modifier);
            }

        }, 300);
    }
}