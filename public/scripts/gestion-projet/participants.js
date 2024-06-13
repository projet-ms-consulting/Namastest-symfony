roleSelectElmts = document.querySelectorAll('select')

roleSelectElmts.forEach(select => {
    let path = select.getAttribute('data-path')
    let spinner = select.previousElementSibling

    select.addEventListener('change', function(event) {
        this.setAttribute('disabled', 'true')
        spinner.classList.remove('d-none')

        fetch(path, {
            method: 'PUT',
            body: `role=${this.value}`,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        }).then(async res => {
            let data = await res.json()

            if(data?.ok) {
                // Tout s'est bien déroulé
            } else if(data?.error) {
                alert(data.error)
            } else {
                alert("Une erreur inattendue est survenue lors de la requête.")
            }

            this.removeAttribute('disabled')
            spinner.classList.add('d-none')
        })
    })
})