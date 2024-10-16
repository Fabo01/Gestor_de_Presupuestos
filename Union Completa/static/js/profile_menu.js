document.getElementById('profile-btn').addEventListener('click', function () {
    var menu = document.getElementById('profile-menu');
    menu.classList.toggle('show');
});

// Cerrar el menú al hacer clic fuera de él
window.addEventListener('click', function (e) {
    if (!e.target.matches('#profile-btn')) {
        var menu = document.getElementById('profile-menu');
        if (menu.classList.contains('show')) {
            menu.classList.remove('show');
        }
    }
});
