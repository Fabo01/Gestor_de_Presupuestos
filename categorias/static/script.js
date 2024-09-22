// Cargar categorías al abrir la página
document.addEventListener('DOMContentLoaded', function() {
    loadCategory();  // Cargar las categorías automáticamente al cargar la página
});

// Función para cargar y mostrar las categorías
function loadCategory() {
    fetch('/categorias')
    .then(response => response.json())
    .then(categorias => {
        let lista = document.getElementById('categoryList');  // Asegúrate de tener este elemento en tu HTML
        lista.innerHTML = '';  // Limpiar la lista actual

        categorias.forEach(categoria => {
            let li = document.createElement('li');
            li.innerHTML = `${categoria.nombre} (${categoria.tipo}) 
                            <button onclick="editCategory(${categoria.id_categoria}, '${categoria.nombre}', '${categoria.tipo}', ${categoria.id_presupuesto})">Editar</button>
                            <button onclick="deleteCategory(${categoria.id_categoria})">Eliminar</button>`;
            lista.appendChild(li);
        });
    })
    .catch(error => console.error('Error al cargar las categorías:', error));
}

// Función para crear una nueva categoría
function createCategory() {
    let nombre = document.getElementById('categoryName').value;
    let tipo = document.getElementById('categoryType').value;
    let id_presupuesto = document.getElementById('categoryBudget').value;

    if (!nombre || !tipo || !id_presupuesto) {
        alert('Por favor, completa todos los campos.');
        return;
    }

    fetch('/categorias', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            nombre: nombre,
            tipo: tipo,
            id_presupuesto: parseInt(id_presupuesto)
        })
    })
    .then(response => {
        if (response.status === 201) {
            return response.json();
        } else {
            return response.json().then(data => { throw new Error(data.mensaje); });
        }
    })
    .then(data => {
        alert(data.mensaje);
        loadCategory();  // Recargar la lista de categorías después de añadir una nueva
        // Limpiar los campos
        document.getElementById('categoryName').value = '';
        document.getElementById('categoryType').value = '';
        document.getElementById('categoryBudget').value = '';
    })
    .catch(error => alert('Error al crear la categoría: ' + error.message));
}

// Función para editar una categoría
function editCategory(id_categoria, nombreActual, tipoActual, id_presupuestoActual) {
    let nuevoNombre = prompt('Nuevo nombre de la categoría:', nombreActual);
    if (nuevoNombre === null) return;  // Cancelado

    let nuevoTipo = prompt('Nuevo tipo (Ingreso/Egreso):', tipoActual);
    if (nuevoTipo === null) return;  // Cancelado

    let nuevoPresupuesto = prompt('Nuevo ID de Presupuesto:', id_presupuestoActual);
    if (nuevoPresupuesto === null || isNaN(nuevoPresupuesto)) return;  // Cancelado o no es un número

    fetch(`/categorias/${id_categoria}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            nombre: nuevoNombre,
            tipo: nuevoTipo,
            id_presupuesto: parseInt(nuevoPresupuesto)
        })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.mensaje);
        loadCategory();  // Recargar la lista de categorías después de la modificación
    })
    .catch(error => console.error('Error al modificar la categoría:', error));
}

// Función para eliminar una categoría por su ID
function deleteCategory(id_categoria) {
    if (confirm('¿Estás seguro de que deseas eliminar esta categoría?')) {
        fetch(`/categorias/${id_categoria}`, {
            method: 'DELETE'
        })
        .then(response => {
            if (response.status === 404) {
                return response.json().then(data => { throw new Error(data.mensaje); });
            } else {
                return response.json();
            }
        })
        .then(data => {
            alert(data.mensaje);
            loadCategory();  // Actualizar la lista de categorías después de eliminar
        })
        .catch(error => alert('Error: ' + error.message));
    }
}
