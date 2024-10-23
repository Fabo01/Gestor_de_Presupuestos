function toggleFrame(frameId) {
    var frame = document.getElementById(frameId);
    if (frame.classList.contains('active')) {
        frame.classList.remove('active');
    } else {
        frame.classList.add('active');
    }
}

// Función para mostrar/ocultar la barra lateral
function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('sidebar-visible');
}