(function () {
    document.querySelectorAll('canvas[data-chart-labels]').forEach(function (canvas) {
        const labels = JSON.parse(canvas.dataset.chartLabels || '[]');
        const values = JSON.parse(canvas.dataset.chartValues || '[]');

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: canvas.dataset.chartLabel || '',
                    data: values,
                    backgroundColor: canvas.dataset.chartColor || '#86efac'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    });

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/service-worker.js')
            .catch(function (error) {
                console.log('SW failed', error);
            });
    }
})();
