// Открытие/закрытие навбара на мобильных устройствах
$(document).ready(function() {
    $('#bars').click(function() {
        $('#mobile-menu-outter').toggle(); 
    });
});

// При клике на иконку стрелки вниз открывем меню выбора языка
document.getElementById("mobile-menu-icon").addEventListener("click", function(event) {
    const menu = document.getElementById("dropout-menu-outter");
    const arrowIcon = document.getElementById("mobile-menu-icon");
    
    menu.style.display = (menu.style.display === "block") ? "none" : "block";
    
    if (menu.style.display === "block") {
        arrowIcon.classList.remove("fa-chevron-down");
        arrowIcon.classList.add("fa-chevron-up");
    }
    else {
        arrowIcon.classList.remove("fa-chevron-up");
        arrowIcon.classList.add("fa-chevron-down");
    }
    
    event.stopPropagation();
});

// Закрываем меню, если кликнуть за пределами
document.addEventListener("click", function(event) {
    const menu = document.getElementById("dropout-menu-outter");
    const menuTrigger = document.getElementById("arrow-down");
    
    if (!menuTrigger.contains(event.target) && !menu.contains(event.target)) {
        menu.style.display = "none";

        const arrowIcon = document.getElementById("mobile-menu-icon");
        arrowIcon.classList.remove("fa-chevron-up");
        arrowIcon.classList.add("fa-chevron-down");
    }
});

// Слайдер
document.addEventListener('DOMContentLoaded', function () {
    const splide = new Splide('.splide');
    splide.mount();
});