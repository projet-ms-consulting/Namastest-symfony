$checkbox = document.querySelector('#show-password');

$checkbox.onclick = toggleVisibility;

function toggleVisibility() {

    let x = document.querySelector('#registration_form_plainPassword_first');
    if (x.type === "password" ) {
        x.type = "text";
    } else {
        x.type = "password";
    }
}