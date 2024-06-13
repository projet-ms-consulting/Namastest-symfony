let gestionModal = document.getElementById('listModal')
let bootstrapGestionModal = new bootstrap.Modal(gestionModal)

async function handleSelectTemplates(ev) {
    ev.preventDefault()

    let confirmBtn = gestionModal.querySelector('.confirmBtn')
    let templateElmts = gestionModal.querySelectorAll('input[type="checkbox"][name="templates"]')

    let formData = new FormData()

    templateElmts.forEach(tElmt => {
        if(tElmt.checked) formData.append(`${tElmt.name}[]`, tElmt.value)
    })

    let res = await fetch(confirmBtn.getAttribute('data-bs-action'), {
        method: 'post',
        body: formData
    })
    let data = await res.json()

    if(!data?.ok) {
        alert(data?.error)
    }

    location.reload()
}

gestionModal.addEventListener('show.bs.modal', async e => {
    let triggerBtn = e.relatedTarget
    let modalBody = gestionModal.querySelector('.modal-body')
    let confirmBtn = gestionModal.querySelector('.confirmBtn')

    let name = triggerBtn.getAttribute('data-bs-name')
    let id = triggerBtn.getAttribute('data-bs-id')
    let getElmtsPath = triggerBtn.getAttribute('data-bs-get')
    let actionPath = triggerBtn.getAttribute('data-bs-action')

    confirmBtn.setAttribute('data-bs-action', actionPath)
    modalBody.innerHTML = ''

    let res = await fetch(getElmtsPath)
    let data = await res.json()

    if(Array.isArray(data)) {
        if(data.length > 0) {
            confirmBtn.classList.remove('disabled')

            let table = document.createElement('table')
            table.className = 'table table-striped'

            table.innerHTML = `<thead>
                        <tr>
                            <th scope="col"><input type="checkbox" checked="true"></th>
                            <th scope="col">Nom</th>
                            <th scope="col">Description</th>
                        </tr>
                    </thead><tbody></tbody>`

            let selectAllCheckbox = table.querySelector('input[type="checkbox"]')
            let tbody = table.querySelector('tbody')

            for(let template of data) {
                if(template?.id && template?.nom && template?.description) {
                    let tr = document.createElement('tr')
                    tr.innerHTML = `<td><input type="checkbox" name="templates"></td>
                            <td class="nom"></td>
                            <td class="test-description"></td>`

                    tr.querySelector('input[type="checkbox"]').setAttribute('value', template.id)
                    tr.querySelector('.nom').append(document.createTextNode(template.nom))
                    tr.querySelector('.test-description').append(document.createTextNode(template.description))

                    if(template.checked) {
                        tr.querySelector('input[type="checkbox"]').checked = true
                    } else {
                        selectAllCheckbox.checked = false
                    }

                    tbody.append(tr)
                }
            }

            modalBody.append(table)
            selectAllCheckbox.addEventListener('change', function() {
                tbody.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = this.checked
                })
            })
            confirmBtn.addEventListener('click', handleSelectTemplates)
        } else {
            confirmBtn.classList.add('disabled')
            modalBody.innerHTML = `<p>Vous n'avez créé aucun test pour le moment.</p>`
        }
    } else if(data?.error) {
        confirmBtn.classList.add('disabled')
        modalBody.innerHTML = `<p>${data.error}</p>`
    } else {
        confirmBtn.classList.add('disabled')
        modalBody.innerHTML = `<p>Une erreur est survenue lors de la requête.</p>`
    }
})