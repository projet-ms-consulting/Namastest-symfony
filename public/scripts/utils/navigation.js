//menu toggle
let menuToggle = document.querySelector('.toggle');
let navigation = document.querySelector('.navigation');
let main = document.querySelector('main');

menuToggle.onclick = function (){

    menuToggle.classList.toggle('active');
    navigation.classList.toggle('active');

    if (navigation.classList.contains('active'))
    {
        localStorage.setItem('menu', 'active');
        main.classList.add('activemenu');
    }
    else
    {
        localStorage.setItem('menu', 'inactive');
        main.classList.remove('activemenu');
    }
}

if (localStorage.getItem('menu') !== "")
{
    if (localStorage.getItem('menu') === 'active')
    {
        menuToggle.classList.add('active');
        navigation.classList.add('active');
        main.classList.add('activemenu');
    }
    else if (localStorage.getItem('menu') === 'inactive')
    {
        menuToggle.classList.remove('active');
        navigation.classList.remove('active');
        main.classList.remove('activemenu');
    }
}

//add active class in selected list item
let liens = document.querySelectorAll('.navigation a');

liens.forEach(function (val, index) {

    if (window.location.href.match(window.location.hostname + val.getAttribute('href') + '$'))
    {
        val.parentNode.className = 'list active';
    }
})

//mode mobile
let menuToggleMobile = document.querySelector('.menu-toggler');


menuToggleMobile.addEventListener('click', function (e) {
    if (navigation.classList.contains("active")) {
        navigation.classList.remove("active");
        navigation.classList.remove("menu-mobile");
    } else {
        navigation.classList.add("active");
        navigation.classList.add("menu-mobile");
    }


});
