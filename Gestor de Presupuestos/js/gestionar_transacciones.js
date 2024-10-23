document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const transaccionesList = document.querySelector('.lista-transacciones');
    const totalTransaccionesElement = document.querySelector('.total-transacciones');
    let totalTransacciones = 0;

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        const descripcion = document.querySelector('#descripcion').value;
        const monto = document.querySelector('#monto').value;
        const idCategoria = form.querySelector('input[name="id_categoria"]').value;

        if (!descripcion || !monto) {
            alert('La descripción y el monto son obligatorios.');
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'gestionar_transacciones.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    if (response.success) {
                        const newTransaction = `
                            <li>
                                <strong>${response.descripcion}</strong> - Monto: $${response.monto} - Fecha: ${response.fecha}
                            </li>`;
                        transaccionesList.insertAdjacentHTML('beforeend', newTransaction);

                        totalTransacciones += parseFloat(response.monto.replace(',', ''));
                        totalTransaccionesElement.textContent = `Total de transacciones: $${totalTransacciones.toFixed(2)}`;

                        form.reset();
                    } else {
                        alert('Error: ' + response.error);
                    }
                } catch (e) {
                    console.error('Error al analizar la respuesta JSON:', e);
                }
            }
        };

        xhr.send(`descripcion=${encodeURIComponent(descripcion)}&monto=${encodeURIComponent(monto)}&id_categoria=${idCategoria}`);
    });
});
