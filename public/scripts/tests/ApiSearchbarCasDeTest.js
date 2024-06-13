let containerTemplateTests = document.getElementById('sortable');
let tableTemplates = document.querySelector('#table-template-tests');
let dataTemplates = null;

fetch(tableTemplates.getAttribute('data-source'))
    .then(response => response.json())
    .then(data =>{
            dataTemplates = data
            data.forEach(test =>{
                createTemplateTest(test)
            });
        listBySearchingBar();
    })

function listBySearchingBar(){
    const inputSearch = document.getElementById('searchbar-templates-tests');
    inputSearch.addEventListener('input',(e) =>{
        containerTemplateTests.innerHTML=``
        dataTemplates?.forEach(test =>{
            if(test?.nom.toLowerCase().includes(inputSearch.value.toLowerCase()) || inputSearch.value == "") {
                createTemplateTest(test)
            }
        })
    })
}



function createTemplateTest(test){
    const testBox = document.createElement('tr')
    testBox.setAttribute('data-id', `${test.id}`);
    testBox.setAttribute('rel', `${test.id}`);
    testBox.innerHTML=`
                                    <td>
                                        <input class="test-checkboxes" type="checkbox" id="template_test_and_catalogue_choice_boxes_test_${test.id}" 
                                        name="template_test_and_catalogue_choice_boxes[test][]" value="${test.id}">
                                    </td>
                                    <td class="ordre position-test"></td>
                                    <td>   
                                        <label for="template_test_and_catalogue_choice_boxes_test_${test.id}}} " class="nom-test"></label>
                                    </td>
                                    <td class="test-description description-test"></td> 
                                    <td class="actions-test">
                                    
                                        <div class="d-flex align-items-start">
                                            <a href="detail/${test.id}" class="btn btn-fonce py-2" title="Consulter le Cas de Test">
                                                <i class="fa fa-arrow-up" aria-hidden="true"></i>
                                            </a>
                                            
                                            <div class="nav-item dropdown ms-1">
                                                <a class="btn btn-primary py-2" role="button" data-bs-toggle="dropdown" >
                                                    <i class="fa fa-cog"></i></a>
                                                    
                                                <ul class="dropdown-menu">
                                                
                                                    <li><a class="dropdown-item" href="update/${test.id}"  title="Modifier le template de test">  Modifier</a></li>
                                                      
                                                    <li><a href="copier/${test.id}" 
                                                        class="dropdown-item" title="Dupliquer le template de test">  Dupliquer</a></li>
                                                        
                                                    <li><a href="copier/${test.id}?modifier='modifier'"
                                                        class="dropdown-item" title="Dupliquer et modifier le template de test">  Dupliquer et Modifier</a></li>
                                                        
                                                    <li><hr class="dropdown-divider"></li>
                                                        
                                                    <li><a href="effacer/${test.id}"
                                                        class="dropdown-item text-danger delete" title="Supprimer le template de test" id="confirm" 
                                                        onclick="return confirm('Voulez vous vraiment effacer ce template de Test?')">  Supprimer</a></li>
                                                        
                                                </ul>         
                                            </div>
                                        </div>
                                    </td>
                               </tr>
                               
            `
    testBox.querySelector('.nom-test').textContent=test.nom
    testBox.querySelector('.position-test').textContent=test.position
    testBox.querySelector('.description-test').textContent=test.description

    containerTemplateTests.append(testBox);


    //function tronquer pour la description d'un test

    const testsDescription= document.querySelectorAll('.test-description');

    testsDescription.forEach(test => {
        truncate(test.innerHTML, 50, test);
    });

}

function truncate(string, number, test){
    if(string.length <= number){
        return test.innerHTML = string
    } else {
        return test.innerHTML = string.slice(0, number).concat('...')
    }
}

