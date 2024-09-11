let categories = {};
let nextId = 1;  // Variable para llevar la cuenta de las IDs

function generateUniqueId() {
    return nextId++;
}

function createCategory() {
    const name = document.getElementById('categoryName').value;
    const type = document.getElementById('categoryType').value;
    if (name && type) {
        const id = generateUniqueId();
        categories[id] = { name, type };
        updateCategoryList();
        alert('Categoría creada con ID: ' + id);
        document.getElementById('categoryName').value = '';
        document.getElementById('categoryType').value = '';
    } else {
        alert('Por favor, complete todos los campos.');
    }
}

function loadCategory() {
    const id = parseInt(prompt('Ingrese el ID de la categoría a cargar:'), 10);
    if (id && categories[id]) {
        const category = categories[id];
        document.getElementById('categoryName').value = category.name;
        document.getElementById('categoryType').value = category.type;
        alert('Categoría cargada.');
    } else {
        alert('Categoría no encontrada.');
    }
}

function modifyCategory() {
    const id = parseInt(prompt('Ingrese el ID de la categoría a modificar:'), 10);
    if (id && categories[id]) {
        const name = document.getElementById('categoryName').value;
        const type = document.getElementById('categoryType').value;
        if (name && type) {
            categories[id] = { name, type };
            updateCategoryList();
            alert('Categoría modificada.');
        } else {
            alert('Por favor, complete todos los campos.');
        }
    } else {
        alert('Categoría no encontrada.');
    }
}

function deleteCategory() {
    const id = parseInt(prompt('Ingrese el ID de la categoría a eliminar:'), 10);
    if (id && categories[id]) {
        delete categories[id];
        updateCategoryList();
        alert('Categoría eliminada.');
    } else {
        alert('Categoría no encontrada.');
    }
}

function updateCategoryList() {
    const list = document.getElementById('categoryList');
    list.innerHTML = '';
    for (const id in categories) {
        if (categories.hasOwnProperty(id)) {
            const category = categories[id];
            const listItem = document.createElement('li');
            listItem.textContent = `ID: ${id}, Nombre: ${category.name}, Tipo: ${category.type}`;
            list.appendChild(listItem);
        }
    }
}
