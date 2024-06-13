let roleDroitsDiv = document.querySelector('#role_droits')
let selectAllBtn = document.querySelector('#selectAll')

roleDroitsDiv.classList.add('row',
    'row-cols-sm-2',
    'row-cols-xl-3',
    'g-2')

selectAllBtn.addEventListener('click', function(event) {
    if(this.textContent === 'Tout sélectionner') {
        document.querySelectorAll('form input[type="checkbox"]').forEach(elmt => {
            elmt.checked = true
        })

        this.textContent = 'Tout désélectionner'
    } else {
        document.querySelectorAll('form input[type="checkbox"]').forEach(elmt => {
            elmt.checked = false
        })

        this.textContent = 'Tout sélectionner'
    }
})