document.addEventListener("DOMContentLoaded", function() {
    fetch('/api/datos')
        .then(response => response.json())
        .then(data => {
            // Extraer los presupuestos y transacciones
            const presupuestos = data.presupuestos;
            const transacciones = data.transacciones;

            // Crear datos para el gráfico de barras (presupuestos)
            const categorias = presupuestos.map(p => `Categoría ${p.ID_categoria}`);
            const saldos = transacciones.map(p => p.saldo_restante);

            const ctxBar = document.getElementById('myChart').getContext('2d');
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: categorias,
                    datasets: [{
                        label: 'Saldo Restante',
                        data: saldos,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Crear datos para el gráfico circular de transacciones
            const transaccionesPorCategoria = transacciones.reduce((acc, trans) => {
                if (!acc[trans.ID_Categoria]) acc[trans.ID_Categoria] = 0;
                acc[trans.ID_Categoria] += trans.Monto;
                return acc;
            }, {});

            const categoriasTrans = Object.keys(transaccionesPorCategoria).map(id => `Categoría ${id}`);
            const montosTrans = Object.values(transaccionesPorCategoria);

            const ctxPie1 = document.getElementById('pieChart1').getContext('2d');
            new Chart(ctxPie1, {
                type: 'pie',
                data: {
                    labels: categoriasTrans,
                    datasets: [{
                        label: 'Gastos por Categoría',
                        data: montosTrans,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)'
                        ],
                        borderWidth: 1
                    }]
                }
            });
        })
        .catch(error => console.error('Error al obtener los datos:', error));
});