function changeChart(url) {
    document.getElementById('chartImage').src = url;
  }
  
  function loadContent(contentId) {
    const content = {
        seguridad: '<h3>Seguridad</h3><p>Aquí puedes configurar la seguridad de tu cuenta.</p>',
        personalizacion: '<h3>Personalización</h3><p>Aquí puedes cambiar la apariencia y preferencias de la cuenta.</p>',
        logros: '<h3>Logros</h3><p>Estos son tus logros alcanzados.</p>',
        configuracionCuenta: '<h3>Configuración de Cuenta</h3><p>Ajustes relacionados con tu cuenta de usuario.</p>',
        configuracionAhorros: '<h3>Configuración de Ahorros</h3><p>Gestiona tus planes de ahorro y objetivos financieros.</p>',
        grafico: `<div class="profile-chart">
                    <h3>Gráfico</h3>
                    <img id="chartImage" src="https://via.placeholder.com/600x400" alt="Gráfico de ejemplo">
                  </div>
                  <div class="chart-buttons">
                    <button onclick="changeChart('https://via.placeholder.com/600x400?text=Gráfico+1')">Gráfico 1</button>
                    <button onclick="changeChart('https://via.placeholder.com/600x400?text=Gráfico+2')">Gráfico 2</button>
                    <button onclick="changeChart('https://via.placeholder.com/600x400?text=Gráfico+3')">Gráfico 3</button>
                  </div>`
    };
  
    document.getElementById('dynamicContent').innerHTML = content[contentId];
  }
  