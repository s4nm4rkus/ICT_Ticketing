document.addEventListener("DOMContentLoaded", function() {
    fetchTickets('Pending', '.recent-tickets');
    fetchTickets('Approved', '.history-tickets');
});

function fetchTickets(status, tableClass) {
    fetch(`php/help_list.php?status=${status}`)
    .then(response => response.json())
    .then(data => {
        const tbody = document.querySelector(tableClass);
        tbody.innerHTML = '';

        if (data.length > 0) {
            data.forEach(ticket => {
                const row = document.createElement('tr');
                
                row.addEventListener("click", function() {
                    window.location.href = `help_form.html?id=${ticket.id}`;
                });

                row.style.cursor = "pointer";
                
                row.innerHTML = `
                
                    <td>${ticket.id}</td>
                    <td>${ticket.requesting_official_name}</td>
                    <td>${ticket.date_requested_assistance}</td>
                    <td>${ticket.time_requested_assistance}</td>
                    <td>${ticket.date_filed}</td>
                `;
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
