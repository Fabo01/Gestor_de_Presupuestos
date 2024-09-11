document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('transaction-form');//crea la etiqueta <li> 
    form.addEventListener('submit', function(e) { 
        e.preventDefault();

        const type = document.getElementById('transaction-type').value;
        const description = document.getElementById('transaction-desc').value;
        const amount = document.getElementById('transaction-mount').value;

        if (description && amount) {
            addTransaction(type, description, amount);
            form.reset();
            document.getElementById('transaction-form').reset();
        } else {
            alert("Por favor, completa todos los campos.");
        }
    });

    function addTransaction(type, description, amount) {
        const transactionList = document.getElementById('transaction-list');
        const listItem = document.createElement('li');
        listItem.innerHTML = `
        ${type}: ${description} - $${amount}
        <span class="delete-transaction" onclick="deleteTransaction(this)">X</span>`;
        transactionList.appendChild(listItem);
    }

    window.deleteTransaction = function(button) {
        const listItem = button.parentElement;
        listItem.remove();
    };
});
