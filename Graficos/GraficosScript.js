
const links = document.querySelectorAll('nav ul li a');
const sections = document.querySelectorAll('section');

function showSection(sectionId) {
    sections.forEach(section => {
        if (section.id === sectionId) {
            section.classList.remove('hidden');
        } else {
            section.classList.add('hidden');
        }
    });
}

links.forEach(link => {
    link.addEventListener('click', function(event) {
        event.preventDefault();
        const targetSection = link.getAttribute('href').substring(1);
        showSection(targetSection);
    });
});

function updateChart(lineChartId, pieChartId, inputId) {
    const inputData = document.getElementById(inputId).value.split(',').map(Number);

    const lineChart = Chart.getChart(lineChartId);
    lineChart.data.datasets[0].data = inputData;
    lineChart.update();

    const pieChart = Chart.getChart(pieChartId);
    pieChart.data.datasets[0].data = inputData;
    pieChart.update();
}

function updateLine(lineChartId, inputId) {
    const inputData = document.getElementById(inputId).value.split(',').map(Number);

    const lineChart = Chart.getChart(lineChartId);
    lineChart.data.datasets[0].data = inputData;
    lineChart.update();
}

function updatePie(pieChartId, inputId) {
    const inputData = document.getElementById(inputId).value.split(',').map(Number);

    const pieChart = Chart.getChart(pieChartId);
    pieChart.data.datasets[0].data = inputData;
    pieChart.update();
}

var ctxLine1 = document.getElementById("lineChart1").getContext("2d");
var lineChart1 = new Chart(ctxLine1, {
    type: "line",
    data: {
        labels: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
        datasets: [{
            label: "Resumen anual",
            data: [],
            borderColor: "rgba(75, 192, 192, 1)",
            backgroundColor: "rgba(75, 192, 192, 0.2)",
            borderWidth: 1
        }]
    },
// Plugin de internet para agregar simbolo $ a los graficos de torta ---------------------------------------------------------------------------------//
    options: {
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value;
                    }
                }
            }
        }
    }
});
var ctxPie1 = document.getElementById("pieChart1").getContext("2d");
var pieChart1 = new Chart(ctxPie1, {
    type: "pie",
    data: {
        labels: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
        datasets: [{
            label: "Resumen anual",
            data: [],
            backgroundColor: ["rgba(255, 99, 132, 0.2)", "rgba(54, 162, 235, 0.2)", "rgba(255, 206, 86, 0.2)", "rgba(75, 192, 192, 0.2)"],
            borderColor: ["rgba(255, 99, 132, 1)", "rgba(54, 162, 235, 1)", "rgba(255, 206, 86, 1)", "rgba(75, 192, 192, 1)"],
            borderWidth: 1
        }]
    },
//------------------------------------------------------------------------------------------------------------------------------------------------//
    options: {
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(tooltipItem) {
                        return '$' + tooltipItem.raw;
                    }
                }
            }
        }
    }
});

var ctxLine2 = document.getElementById("lineChart2").getContext("2d");
var lineChart2 = new Chart(ctxLine2, {
    type: "line",
    data: {
        labels: ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado", "Domingo"],
        datasets: [{
            label: "Resumen semanal",
            data: [],
            borderColor: "rgba(153, 102, 255, 1)",
            backgroundColor: "rgba(153, 102, 255, 0.2)",
            borderWidth: 1
        }]
    },
//------------------------------------------------------------------------------------------------------------------------------------------------//
    options: {
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value;
                    }
                }
            }
        }
    }
});

var ctxPie2 = document.getElementById("pieChart2").getContext("2d");
var pieChart2 = new Chart(ctxPie2, {
    type: "pie",
    data: {
        labels: ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado", "Domingo"],
        datasets: [{
            label: "Resumen semanal",
            data: [],
            backgroundColor: ["rgba(153, 102, 255, 0.2)", "rgba(255, 159, 64, 0.2)", "rgba(75, 192, 192, 0.2)", "rgba(54, 162, 235, 0.2)"],
            borderColor: ["rgba(153, 102, 255, 1)", "rgba(255, 159, 64, 1)", "rgba(75, 192, 192, 1)", "rgba(54, 162, 235, 1)"],
            borderWidth: 1
        }]
    },
    options: {
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(tooltipItem) {
                        return '$' + tooltipItem.raw;
                    }
                }
            }
        }
    }
});

var ctxLine3 = document.getElementById("lineChart3").getContext("2d");
var lineChart3 = new Chart(ctxLine3, {
    type: "line",
    data: {
        labels: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31],
        datasets: [{
            label: "Resumen por dia",
            data: [],
            borderColor: "rgba(255, 206, 86, 1)",
            backgroundColor: "rgba(255, 206, 86, 0.2)",
            borderWidth: 1
        }]
    },
//------------------------------------------------------------------------------------------------------------------------------------------------//
    options: {
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value;
                    }
                }
            }
        }
    }
});

var ctxPie4 = document.getElementById("pieChart4").getContext("2d");
var pieChart4 = new Chart(ctxPie4, {
    type: "pie",
    data: {
        labels: ["Supermercado", "Ocio", "Gastos basicos", "Deudas"],
        datasets: [{
            label: "Resumen por categorias",
            data: [],
            backgroundColor: ["rgba(255, 206, 86, 0.2)", "rgba(75, 192, 192, 0.2)", "rgba(153, 102, 255, 0.2)", "rgba(255, 159, 64, 0.2)"],
            borderColor: ["rgba(255, 206, 86, 1)", "rgba(75, 192, 192, 1)", "rgba(153, 102, 255, 1)", "rgba(255, 159, 64, 1)"],
            borderWidth: 1
        }]
    },
//------------------------------------------------------------------------------------------------------------------------------------------------//
    options: {
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(tooltipItem) {
                        return '$' + tooltipItem.raw;
                    }
                }
            }
        }
    }
});