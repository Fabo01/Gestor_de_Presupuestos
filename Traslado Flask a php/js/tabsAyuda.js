function openTab(evt, tabName) {
    // Obtén todos los elementos con la clase "tab-content" y oculta todas las secciones
    var tabContent = document.getElementsByClassName("tab-content");
    for (var i = 0; i < tabContent.length; i++) {
        tabContent[i].style.display = "none";
    }

    // Elimina la clase "active" de todos los botones de pestañas
    var tabButtons = document.getElementsByClassName("tab-button");
    for (var i = 0; i < tabButtons.length; i++) {
        tabButtons[i].classList.remove("active");
    }

    // Muestra la pestaña actual y añade la clase "active" al botón que fue clickeado
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.classList.add("active");
}
