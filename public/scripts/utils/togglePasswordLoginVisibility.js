let checkboxLogin = document.querySelector('#show-login-password')

checkboxLogin.addEventListener('click', toggleVisibilityLogin)

function toggleVisibilityLogin() {
    let inputPassword = document.querySelector('#inputPassword')

    if(checkboxLogin.checked) {
        inputPassword.setAttribute('type', 'text')
    } else {
        inputPassword.setAttribute('type', 'password')
    }
}