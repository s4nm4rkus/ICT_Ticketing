document.addEventListener("DOMContentLoaded", function () {
  fetchTickets("Pending", ".recent-tickets");
  fetchTickets("Approved", ".history-tickets");
});

function fetchTickets(status, tableClass) {
  fetch(`php/form6_list.php?status=${status}`)
    .then((response) => response.json())
    .then((data) => {
      const tbody = document.querySelector(tableClass);
      tbody.innerHTML = "";

      if (data.length > 0) {
        data.forEach((ticket) => {
          const middleInitial = ticket.middle_name
            ? ticket.middle_name.charAt(0).toUpperCase() + "."
            : "";

          const fullName = `${ticket.first_name} ${middleInitial} ${ticket.last_name}`;

          // Fallback: use other_type_of_leave if typeofleave_A is empty
          const leaveType =
            ticket.typeofleave_A && ticket.typeofleave_A.trim() !== ""
              ? ticket.typeofleave_A
              : ticket.other_type_of_leave || "N/A";

          const row = document.createElement("tr");
          row.innerHTML = `
            <td>${ticket.id}</td>
            <td>${fullName}</td>
            <td>${ticket.email}</td>
            <td>${leaveType}</td>
            <td>${ticket.inclusive_days}</td>
            <td>${ticket.date_of_filing}</td>
          `;

          row.addEventListener("click", function () {
            window.location.href = `form_6_printing.html?id=${ticket.id}`;
          });

          tbody.appendChild(row);
        });
      } else {
        tbody.innerHTML = `<tr><td colspan="6">No support tickets found.</td></tr>`;
      }
    })
    .catch((error) => {
      console.error("Error fetching data:", error);
      const tbody = document.querySelector(tableClass);
      tbody.innerHTML = `<tr><td colspan="5">Failed to retrieve data.</td></tr>`;
    });
}
