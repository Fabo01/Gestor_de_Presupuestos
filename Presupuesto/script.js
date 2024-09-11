let budgets = [];
let editIndex = null;

// Función para renderizar la lista de presupuestos
function renderBudgets() {
    const budgetsList = document.getElementById("budgets-list");
    budgetsList.innerHTML = "";

    budgets.forEach((budget, index) => {
        const budgetItem = document.createElement("div");
        budgetItem.className = "budget-item";
        budgetItem.innerHTML = `
            <span><strong>${budget.name}</strong> - ${budget.category} - $${budget.amount}</span>
            <button class="edit-btn" onclick="editBudget(${index})">Modificar</button>
            <button class="delete-btn" onclick="deleteBudget(${index})">Eliminar</button>
        `;
        budgetsList.appendChild(budgetItem);
    });
}

// Función para añadir o actualizar un presupuesto
document.getElementById("add-budget").addEventListener("click", function() {
    const name = document.getElementById("budget-name").value;
    const category = document.getElementById("category").value;
    const amount = parseFloat(document.getElementById("amount").value);

    if (name && !isNaN(amount)) {
        if (editIndex === null) {
            // Añadir nuevo presupuesto
            budgets.push({ name, category, amount });
        } else {
            // Actualizar presupuesto existente
            budgets[editIndex] = { name, category, amount };
            editIndex = null; // Reiniciar el índice de edición
        }

        // Limpiar campos
        document.getElementById("budget-name").value = "";
        document.getElementById("amount").value = "";
        renderBudgets();
    } else {
        alert("Por favor ingresa un nombre y cantidad válida.");
    }
});

// Función para editar un presupuesto
function editBudget(index) {
    const budget = budgets[index];
    document.getElementById("budget-name").value = budget.name;
    document.getElementById("category").value = budget.category;
    document.getElementById("amount").value = budget.amount;
    editIndex = index;
}

// Función para eliminar un presupuesto
function deleteBudget(index) {
    budgets.splice(index, 1);
    renderBudgets();
}
