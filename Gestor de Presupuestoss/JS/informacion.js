// Función para abrir una pestaña específica
function openTab(evt, tabName) {
    // Obtener todos los elementos con clase "tab-content" y ocultarlos
    var tabContent = document.getElementsByClassName('tab-content');
    for (var i = 0; i < tabContent.length; i++) {
        tabContent[i].style.display = 'none';
    }

    // Obtener todos los elementos con clase "tab-button" y remover la clase "active"
    var tabButtons = document.getElementsByClassName('tab-button');
    for (var i = 0; i < tabButtons.length; i++) {
        tabButtons[i].className = tabButtons[i].className.replace(' active', '');
    }

    // Mostrar la pestaña actual y añadir la clase "active" al botón que abrió la pestaña
    document.getElementById(tabName).style.display = 'block';
    evt.currentTarget.className += ' active';
}
