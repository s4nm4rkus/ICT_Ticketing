document.addEventListener("DOMContentLoaded", function() {
    fetchTickets('Pending', '.recent-tickets');
    fetchTickets('Approved', '.history-tickets');
});

function fetchTickets(status, tableClass) {
    fetch(`php/DTS_list.php?status=${status}`)
    .then(response => response.json())
    .then(data => {
        const tbody = document.querySelector(tableClass);
        tbody.innerHTML = '';

        if (data.length > 0) {
            data.forEach(ticket => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${ticket.id}</td>
                    <td>${ticket.requesterName}</td>
                    <td>${ticket.requestType}</td>
                    <td>${ticket.date}</td>
                `;

                row.addEventListener("click", function() {
                    window.location.href = `dts_form.html?id=${ticket.id}`;
                });

                tbody.appendChild(row);
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="5">No support tickets found.</td></tr>`;
        }
    })
    .catch(error => {
        console.error('Error fetching data:', error);
        tbody.innerHTML = `<tr><td colspan="5">Failed to retrieve data.</td></tr>`;
    });
}
