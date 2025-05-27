document.addEventListener("DOMContentLoaded", function() {
    fetchTickets('Pending', '.recent-tickets');
    fetchTickets('Approved', '.history-tickets');
});

function fetchTickets(Status, tableClass) {
    fetch(`php/ict_list.php?status=${Status}`)
    .then(response => response.json())
    .then(data => {
        const tbody = document.querySelector(tableClass);
        tbody.innerHTML = '';

        if (data.length > 0) {
            data.forEach(ticket => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${ticket.id}</td>
                    <td>${ticket.requester_name}</td>
                    <td>${ticket.assistance}</td>
                    <td>${ticket.date_reported}</td>
                `;
                row.addEventListener('click', function() {
                    window.location.href = `ict_form.html?id=${ticket.id}`;
                });
                tbody.appendChild(row);
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="4">No support tickets found.</td></tr>`;
        }
    })
    .catch(error => {
        console.error('Error fetching data:', error);
        tbody.innerHTML = `<tr><td colspan="4">Failed to retrieve data.</td></tr>`;
    });
}
