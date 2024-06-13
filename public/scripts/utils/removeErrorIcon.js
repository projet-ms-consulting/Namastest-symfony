errors = document.querySelectorAll('.form-error-icon');

if (errors !== null) {
    errors.forEach((element) => {
        element.classList.add('d-none');
    })
}
