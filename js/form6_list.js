document.addEventListener("DOMContentLoaded", function () {
  // Define which tables each role can access
  const roleAccess = {
    "unit-head": ["recommendation", "processed"],
    records: ["records", "processed"],
    personnel: ["personnel", "processed"],
    admin: ["admin", "processed"],
    "asds-sds": ["sds", "processed"],
    ict: [
      "recommendation",
      "records",
      "personnel",
      "admin",
      "sds",
      "processed",
    ],
  };

  // USER_ROLE comes from admin.php <script> tag
  const allowed = roleAccess[USER_ROLE] || [];

  // Hide all sections by default
  const sections = {
    recommendation: document.querySelector(".recommendation-section"),
    records: document.querySelector(".records-section"),
    personnel: document.querySelector(".personnel-section"),
    admin: document.querySelector(".admin-section"),
    sds: document.querySelector(".sds-section"),
    processed: document.querySelector(".processed-section"),
  };

  for (let key in sections) {
    if (sections[key]) sections[key].style.display = "none";
  }

  // Show only allowed sections
  allowed.forEach((key) => {
    if (sections[key]) sections[key].style.display = "block";
  });

  // Fetch tickets only for allowed sections
  if (allowed.includes("recommendation")) {
    fetchTickets("For Recommendation", ".recommendation-tickets");
  }
  if (allowed.includes("records")) {
    fetchTickets("For Records Unit", ".records-tickets");
  }

  if (allowed.includes("personnel")) {
    fetchTickets("For Personnel Unit", ".personnel-tickets");
  }

  if (allowed.includes("admin")) {
    fetchTickets("For Admin Unit", ".admin-tickets");
  }

  if (allowed.includes("sds")) {
    fetchTickets("For SDS/ASDS/Records", ".sds-tickets");
  }

  // Everyone who can see "processed" will fetch Approved/Disapproved
  if (allowed.includes("processed")) {
    fetchTickets(["Approved", "Disapproved"], ".approved-tickets");
  }
});

let loaderStartTime = 0;
const MIN_LOADER_TIME = 0; // 0.8 seconds

function showLoader() {
  const loader = document.getElementById("loader-overlay");
  if (loader) {
    loader.style.display = "flex";
    loaderStartTime = Date.now();
  }
}

function hideLoader() {
  const loader = document.getElementById("loader-overlay");
  if (!loader) return;

  const elapsed = Date.now() - loaderStartTime;
  const remaining = MIN_LOADER_TIME - elapsed;

  if (remaining > 0) {
    setTimeout(() => {
      loader.style.display = "none";
    }, remaining);
  } else {
    loader.style.display = "none";
  }
}

function fetchTickets(status, tableClass) {
  const tbody = document.querySelector(tableClass);
  if (!tbody) return;

  showLoader();

  // Convert status to query string
  const statusQuery = Array.isArray(status)
    ? status.map(encodeURIComponent).join(",")
    : encodeURIComponent(status);

  fetch(`php/form6_list.php?status=${statusQuery}`)
    .then((response) => response.json())
    .then((data) => {
      tbody.innerHTML = "";

      const headerCount =
        tbody.parentElement.querySelector("thead tr").children.length;

      const hasRemarks = headerCount === 8; // Processed section

      if (data.length > 0) {
        data.forEach((ticket) => {
          const middleInitial = ticket.middle_name
            ? ticket.middle_name.charAt(0).toUpperCase() + "."
            : "";
          const fullName = `${ticket.first_name} ${middleInitial} ${ticket.last_name}`;

          const leaveType =
            ticket.typeofleave_A && ticket.typeofleave_A.trim() !== ""
              ? ticket.typeofleave_A
              : ticket.other_type_of_leave || "N/A";

          const remarksColumn = hasRemarks ? `<td>${ticket.status}</td>` : "";

          const row = document.createElement("tr");
          row.innerHTML = `
            <td>${ticket.id}</td>
            <td>${fullName}</td>
            <td>${ticket.email}</td>
            <td>${leaveType}</td>
            <td>${ticket.inclusive_days}</td>
            <td>${ticket.date_of_filing}</td>
            ${remarksColumn}
            <td>${ticket.department}</td>
          `;

          row.addEventListener("click", () => {
            window.location.href = `form_6_records.html?id=${ticket.id}`;
          });

          tbody.appendChild(row);
        });
      } else {
        tbody.innerHTML = `<tr><td colspan="${headerCount}">No tickets found.</td></tr>`;
      }
    })
    .catch((error) => {
      console.error("Error fetching data:", error);
    })
    .finally(() => {
      hideLoader();
    });
}
