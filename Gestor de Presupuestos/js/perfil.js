function toggleFrame(frameId) {
    // Ocultar todos los frames
    const allFrames = document.querySelectorAll('.frame');
    allFrames.forEach(frame => frame.style.display = 'none');

    // Mostrar el frame correspondiente
    const targetFrame = document.getElementById(frameId);
    if (targetFrame) {
        targetFrame.style.display = 'block';
    }
}

// Configuración inicial: ocultar todos los frames al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    const allFrames = document.querySelectorAll('.frame');
    allFrames.forEach(frame => frame.style.display = 'none');
});


const themeToggle = document.getElementById("theme-toggle");
const body = document.body;

// Verifica la preferencia guardada del usuario
if (localStorage.getItem("theme") === "dark") {
    body.classList.add("dark-mode");
    themeToggle.textContent = "Modo Claro";
}

// Cambia entre modos claro y oscuro
themeToggle.addEventListener("click", () => {
    body.classList.toggle("dark-mode");
    
    if (body.classList.contains("dark-mode")) {
        localStorage.setItem("theme", "dark");
        themeToggle.textContent = "Modo Claro";
    } else {
        localStorage.setItem("theme", "light");
        themeToggle.textContent = "Modo Oscuro";
    }
});

document.addEventListener("DOMContentLoaded", () => {
    const toggleButton = document.getElementById("mode-toggle");

    // Cambiar el modo entre claro y oscuro
    toggleButton.addEventListener("click", () => {
        document.body.classList.toggle("dark-mode");
        toggleButton.textContent = document.body.classList.contains("dark-mode")
            ? "Modo Claro"
            : "Modo Oscuro";
    });
});

