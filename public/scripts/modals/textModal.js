let textModal = document.querySelector('#textModal')

textModal.addEventListener('show.bs.modal', function(e) {
    // On récupère le bouton ayant servi à faire apparaitre le modal
    let triggerBtn = e.relatedTarget

    // On récupère le titre
    let title = triggerBtn.getAttribute('title')
    // On récupère le message
    let message = triggerBtn.getAttribute('data-bs-message')
    // On récupère l'action
    let action = triggerBtn.getAttribute('data-bs-action')

    // On récupère le bouton qui sert à confirmer la modal
    let confirmBtn = this.querySelector('.confirmBtn')

    this.querySelector('.modal-title').textContent = `${title} ?`
    this.querySelector('.modal-body').textContent = message

    if(triggerBtn.tagName.toLowerCase() === 'a') {
        confirmBtn.setAttribute('href', action)
    }/* else if(triggerBtn.getAttribute('type').toLowerCase() === 'submit') {
        confirmBtn.addEventListener('click', function(event) {
            event.preventDefault()
            triggerBtn.closest('form').submit()
        })
    }*/
})

// document.querySelectorAll('[type="submit"][data-bs-toggle]').forEach(function(elmt) {
//     elmt.addEventListener('click', e => {
//         e.preventDefault()
//     })
// })

