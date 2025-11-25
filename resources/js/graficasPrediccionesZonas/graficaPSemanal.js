document.addEventListener('DOMContentLoaded', function () {
    const zonaSelect = document.getElementById('zonaSelect');
    const chartContainer = document.getElementById('semanal-chart');
    let chart;

    // Definimos la función
    function fetchPredictions(zonaId) {
        fetch(`/prediccionesZonas/obtenerPredicciones?zona_id=${zonaId}`)
            .then(response => response.json())
            .then(data => {
                if (!data || !data.length) {
                    // Si no hay datos, limpiamos gráfico y salimos silenciosamente o con alert
                    if (chart) chart.destroy();
                    return;
                }

                const fechas = data.map(item => item.fecha);
                const valores = data.map(item => item.total_kg);

                const options = {
                    chart: { height: 350, type: "line", fontFamily: "Inter, sans-serif" },
                    series: [{ name: "Predicción de residuos (Kg)", data: valores, color: "#008FFB" }],
                    xaxis: { categories: fechas },
                    yaxis: { title: { text: 'Kg de residuos' } }
                };

                if (chart) chart.destroy();
                chart = new ApexCharts(chartContainer, options);
                chart.render();
            })
            .catch(error => {
                console.error('Error al obtener predicciones:', error);
            });
    }

    // --- CORRECCIÓN: PROTECCIÓN CONTRA NULOS ---
    // Solo ejecutamos lógica si el elemento existe en el HTML actual
    if (zonaSelect) {
        zonaSelect.addEventListener('change', function () {
            const zonaId = this.value;
            if (zonaId) fetchPredictions(zonaId);
        });

        // Carga inicial
        if (zonaSelect.value) {
            fetchPredictions(zonaSelect.value);
        }
    }
});