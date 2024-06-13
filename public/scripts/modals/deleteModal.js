let deleteModal = document.getElementById('deleteModal')
let bootstrapModal = new bootstrap.Modal(deleteModal)

// Lorsqu'on affiche le modal
deleteModal.addEventListener('show.bs.modal', e => {
    // On récupère le bouton ayant servi à faire apparaitre le modal
    let triggerBtn = e.relatedTarget

    // On récupère le type d'élément sur lequel on veut agir (projet, template, catalogue, etc...)
    let type = triggerBtn.getAttribute('data-bs-type')
    // On récupère le nom ou libelle de l'élément sur lequel on veut agir
    let name = triggerBtn.getAttribute('data-bs-name')
    // On récupère l'id de l'élément sur lequel on veut agir
    let id = triggerBtn.getAttribute('data-bs-id')
    // On récupère le path pour le fetch
    let path = triggerBtn.getAttribute('data-bs-path')

    // On récupère les éléments à mettre à jour dans le modal
    let typeSpan = deleteModal.querySelector('.type')
    let nameSpan = deleteModal.querySelector('.name')
    let confirmBtn = deleteModal.querySelector('.confirmBtn')

    // On met à jour ces éléments avec les données récupérées précédemment
    // POINT SECURITE POUR LA SOUTENANCE: "using textContent can prevent XSS attacks." (https://developer.mozilla.org/en-US/docs/web/api/node/textcontent)
    typeSpan.textContent = type
    nameSpan.textContent = name
    confirmBtn.setAttribute('data-bs-path', path)
    confirmBtn.setAttribute('data-bs-id', id)

    // On assigne la méthode handleDelete au bouton de confirmation
    confirmBtn.addEventListener('click', handleDelete)
})

// Lorsque le modal est fermé
deleteModal.addEventListener('hide.bs.modal', e => {
    /*
     * On dé-assigne la méthode handleDelete au bouton de confirmation, sinon le
     * bouton appelera handleDelete autant de fois que la méthode aura été assignée
     */
    let deleteBtn = deleteModal.querySelector('.confirmBtn')
    deleteBtn.removeEventListener('click', handleDelete)
})

async function handleDelete(e) {
    let path = this.getAttribute('data-bs-path')
    let id = this.getAttribute('data-bs-id')

    let response = await fetch(path, {
        method: "DELETE"
    })

    let data = await response?.json()

    if(data?.ok) {
        document.querySelectorAll(`[data-id='${id}']`).forEach(elmt => {
            elmt.remove()
        })
    } else if(data?.error) {
        alert(data.error)
    } else {
        alert("Une erreur est survenue lors de la requête.")
    }

    bootstrapModal.hide()
    // LE RELOAD RECREE LE PROJET QUE L'ON A SUPPRIME S'IL VIENT D'ETRE CREE
    // window.location.reload(true);
    location.href = location
}