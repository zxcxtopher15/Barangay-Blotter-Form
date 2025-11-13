'use strict';

document.addEventListener('DOMContentLoaded', () => {
    // --- 1. DOM Element & Chart Initialization ---
    const lineCanvas = document.getElementById('lineChart');
    const doughnutCanvas = document.getElementById('doughnutChart');

    if (!lineCanvas || !doughnutCanvas) {
        console.error('CRITICAL: Canvas element(s) not found. Aborting chart setup.');
        return;
    }

    const lineCtx = lineCanvas.getContext('2d');
    const doughnutCtx = doughnutCanvas.getContext('2d');
    
    // Chart Configurations
    const lineChart = new Chart(lineCtx, {
        type: 'line',
        data: { labels: [], datasets: [{ data: [], borderColor: 'orange', tension: 0.4, fill: { target: 'origin', above: 'rgba(255, 193, 7, 0.1)' }, pointBackgroundColor: 'orange' }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });

    const doughnutChart = new Chart(doughnutCtx, {
        type: 'doughnut',
        data: { labels: [], datasets: [{ data: [], backgroundColor: ['#36a2eb', '#ff6384', '#8a2be2', '#ffce56', '#95a5a6', '#4bc0c0'], hoverOffset: 4 }] },
        options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true, pointStyle: 'rectRounded' } } } }
    });

    // --- DOM Element References ---
    const monthlyBtn = document.getElementById('monthlyBtn');
    const yearlyBtn = document.getElementById('yearlyBtn');
    const yearSelector = document.getElementById('yearSelector');
    const monthSelector = document.getElementById('monthSelector');
    
    let currentPeriod = 'monthly'; // Keep track of the current view

    // --- 2. Core Data Handling and UI Update Functions ---
    async function syncDashboard() {
        const selectedYear = yearSelector.value;
        const selectedMonth = monthSelector.value;
        
        console.log(`Syncing dashboard for: ${currentPeriod}, Year: ${selectedYear}, Month: ${selectedMonth}`);
        
        try {
            const response = await fetch(`actions/get_dashboard_data.php?period=${currentPeriod}&year=${selectedYear}&month=${selectedMonth}`);
            if (!response.ok) {
                throw new Error(`Network Error: Status ${response.status}`);
            }
            const data = await response.json();

            if (data.error) {
                throw new Error(`Server-Side Error: ${data.error}`);
            }
            
            const displayPeriod = currentPeriod.charAt(0).toUpperCase() + currentPeriod.slice(1);
            updateChart(lineChart, data.lineChart.labels, data.lineChart.data);
            updateChart(doughnutChart, data.doughnutChart.labels, data.doughnutChart.data);
            updateTopIncidentsList(data.topIncidents, displayPeriod);
            updateTopIncidentCard(data.topIncident);

        } catch (error) {
            console.error('Dashboard Sync Failed:', error);
            document.getElementById('topIncidentsList').innerHTML = `<p class="text-red-500 text-center font-semibold">Could not load chart data. Error: ${error.message}</p>`;
        }
    }

    function updateChart(chart, newLabels, newData) {
        chart.data.labels = newLabels || [];
        chart.data.datasets[0].data = newData || [];
        chart.update();
    }

    function updateTopIncidentsList(incidentData, period) {
        const listContainer = document.getElementById('topIncidentsList');
        const periodSpan = document.getElementById('incident-period');
        periodSpan.textContent = period;
        listContainer.innerHTML = '';

        if (!incidentData || incidentData.length === 0) {
            listContainer.innerHTML = '<p class="text-gray-500 text-center">No incident data available for this period.</p>';
            return;
        }

        const maxCount = incidentData.length > 0 ? incidentData[0].count : 0;
        incidentData.slice(0, 10).forEach((incident, index) => {
            const percentageOfMax = maxCount > 0 ? (incident.count / maxCount) * 100 : 0;
            const percentageOfTotal = incident.total > 0 ? ((incident.count / incident.total) * 100).toFixed(1) : 0;

            const incidentElement = document.createElement('div');
            incidentElement.className = 'py-2';
            incidentElement.innerHTML = `
                <div class="flex items-center justify-between text-sm">
                    <span class="font-medium text-gray-700">
                        <span class="font-bold text-primary w-6 inline-block">${index + 1}.</span>
                        ${incident.name}
                    </span>
                    <span class="font-semibold text-gray-500">
                        ${incident.count} (${percentageOfTotal}%)
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                    <div class="bg-secondary h-2 rounded-full" style="width: ${percentageOfMax}%"></div>
                </div>
            `;
            listContainer.appendChild(incidentElement);
        });
    }

    function updateTopIncidentCard(topIncident) {
        const descriptionElement = document.getElementById('top-incident-description');
        const countElement = document.getElementById('top-incident-count');

        if (descriptionElement && countElement && topIncident) {
            descriptionElement.textContent = topIncident.description || 'No Data';
            countElement.textContent = `${topIncident.count || 0} Reports`;
        }
    }

    // --- 3. UI and Event Handlers ---
    function handlePeriodChange(newPeriod) {
        currentPeriod = newPeriod;
        
        // Update button styles
        monthlyBtn.classList.toggle('bg-secondary', newPeriod === 'monthly');
        monthlyBtn.classList.toggle('text-white', newPeriod === 'monthly');
        yearlyBtn.classList.toggle('bg-secondary', newPeriod === 'yearly');
        yearlyBtn.classList.toggle('text-white', newPeriod === 'yearly');

        // Show/hide the relevant dropdown selector
        monthSelector.style.display = newPeriod === 'monthly' ? 'block' : 'none';

        // Fetch new data
        syncDashboard();
    }

    // --- 4. Event Listeners ---
    monthlyBtn.addEventListener('click', () => handlePeriodChange('monthly'));
    yearlyBtn.addEventListener('click', () => handlePeriodChange('yearly'));
    yearSelector.addEventListener('change', syncDashboard);
    monthSelector.addEventListener('change', syncDashboard);

    // --- 5. Initial Load ---
    handlePeriodChange('monthly'); // Set the initial state to monthly view
});

