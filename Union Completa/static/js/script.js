document.addEventListener("DOMContentLoaded", function() {
    fetch('/api/datos')
        .then(response => response.json())
        .then(data => {
            // Extraer los presupuestos y transacciones
            const presupuestos = data.presupuestos;
            const transacciones = data.transacciones;

            // Crear un mapa para agrupar transacciones por categoría
            const transaccionesPorCategoria = {};
            const nombresTransacciones = transacciones.map(t => t.desc);

            transacciones.forEach(transaccion => {
                const categoriaId = transaccion.ID_Categoria;
                if (!transaccionesPorCategoria[categoriaId]) {
                    transaccionesPorCategoria[categoriaId] = [];
                }
                transaccionesPorCategoria[categoriaId].push(transaccion.Monto);
            });

            // Crear datos para las etiquetas (nombres de categorías)
            const categorias = presupuestos.map(p => `Categoría ${p.nombre}`); // Aquí uso `p.nombre` suponiendo que existe un campo `nombre` en Presupuestos

            // Crear los datasets, uno por cada categoría
            const datasets = presupuestos.map((presupuesto, index) => {
                const categoriaId = presupuesto.ID_categoria;
                const data = transaccionesPorCategoria[categoriaId] || [];

                return {
                    label: `Transacciones`, // Usar el nombre de la categoría
                    data: data,  // Monto de las transacciones en esa categoría
                    backgroundColor: `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 0.2)`,
                    borderColor: `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 1)`,
                    borderWidth: 1
                };
            });

            // Crear el gráfico de barras
            const ctxBar = document.getElementById('myChart').getContext('2d');
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: nombresTransacciones,  // Aquí mostramos las categorías como etiquetas
                    datasets: datasets   // Aquí incluimos los datasets de transacciones por categoría
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error al obtener los datos:', error));
        
});
