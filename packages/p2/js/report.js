document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const statusFilter = document.getElementById('status-filter');
    const startDate = document.getElementById('start-date');
    const endDate = document.getElementById('end-date');
    const tableBody = document.getElementById('reports-tbody');
    const originalRows = Array.from(tableBody.getElementsByTagName('tr'));

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const startValue = startDate.value;
        const endValue = endDate.value;

        originalRows.forEach(row => {
            const caseNo = row.cells[0].textContent.toLowerCase();
            const complainant = row.cells[1].textContent.toLowerCase();
            const incidentDateStr = row.cells[2].textContent;
            const type = row.cells[3].textContent.toLowerCase();

            const incidentDate = new Date(incidentDateStr);
            const rowDate = incidentDate.toISOString().split('T')[0];

            const searchMatch = caseNo.includes(searchTerm) || complainant.includes(searchTerm) || type.includes(searchTerm);
            const startDateMatch = !startValue || (rowDate >= startValue);
            const endDateMatch = !endValue || (rowDate <= endValue);

            if (searchMatch && startDateMatch && endDateMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', filterTable);
    startDate.addEventListener('change', filterTable);
    endDate.addEventListener('change', filterTable);
});