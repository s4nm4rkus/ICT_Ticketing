document.addEventListener('DOMContentLoaded', function() {
    let allRecords = []; // Holds all APPROVED records fetched initially
    const sortTypeDropdown = document.getElementById('sortTypeDropdown');
    const startDateInput = document.getElementById('start');
    const endDateInput = document.getElementById('end');
    const filterButton = document.getElementById('filterButton');
    const resetButton = document.getElementById('resetButton');
    const printButton = document.getElementById('printButton');
    const tableBody = document.querySelector('table tbody');

    // Initial Fetch (PHP now filters by 'Approved' status)
    fetch(`php/GenerateReport.php`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
         })
        .then(data => {
            if (data.success) {
                allRecords = data.data; // Store the initially fetched (approved) records
                applyFilters(); // Display records based on initial filter settings (All, no date)
            } else {
                console.error('Error fetching data:', data.error);
                displayError('Error fetching data: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            displayError('Error fetching data. Please check the console and PHP script.');
        });

    // Event Listeners
    filterButton.addEventListener('click', applyFilters);
    sortTypeDropdown.addEventListener('change', applyFilters); // Apply filters when dropdown changes

    resetButton.addEventListener('click', function() {
        startDateInput.value = '';
        endDateInput.value = '';
        sortTypeDropdown.value = 'All'; // Reset dropdown to 'All'
        applyFilters(); // Re-apply filters (which will show all approved records)
    });

    printButton.addEventListener('click', function(event) {
        event.preventDefault();
        printForm();
    });

    // Central function to apply all filters
    function applyFilters() {
        const selectedType = sortTypeDropdown.value;
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;

        let filteredRecords = allRecords; // Start with all approved records

        // 1. Apply Type Filter
        if (selectedType !== 'All') {
            filteredRecords = filteredRecords.filter(record => record.request_type === selectedType);
        }

        // 2. Apply Date Filter (to the potentially type-filtered list)
        if (startDate || endDate) {
            filteredRecords = filteredRecords.filter(record => {
                // Gracefully handle potential invalid date strings from DB
                if (!record.date_requested) return false;
                const recordDate = new Date(record.date_requested);
                 if (isNaN(recordDate.getTime())) {
                    console.warn(`Invalid date_requested found: ${record.date_requested} for ID ${record.request_id}`);
                    return false; // Skip records with invalid dates
                }


                // Parse start and end dates - ensure they are valid Dates or null
                const start = startDate ? new Date(startDate) : null;
                const end = endDate ? new Date(endDate) : null;

                 // Validate parsed dates
                if (start && isNaN(start.getTime())) return false;
                if (end && isNaN(end.getTime())) return false;


                // Adjust end date to include the entire day
                if (end) {
                    end.setHours(23, 59, 59, 999);
                }

                // Perform date comparison
                if (start && end) {
                    return recordDate >= start && recordDate <= end;
                } else if (start) {
                    return recordDate >= start;
                } else if (end) {
                    return recordDate <= end;
                }
                // If only type filter is applied, this point is not reached
                // If no date filters are set, return true (keep record)
                return true;
            });
        }

        displayRecords(filteredRecords);
    }

    // Function to display records in the table
    function displayRecords(records) {
        tableBody.innerHTML = ''; // Clear previous results

        if (!records || records.length === 0) {
            const row = document.createElement('tr');
            row.innerHTML = '<td colspan="6" style="text-align: center;">No approved records found matching the criteria</td>';
            tableBody.appendChild(row);
            return;
        }

        records.forEach(record => {
            const row = document.createElement('tr');
            // Store identifiers for potential updates (like date completed)
            row.dataset.requestType = record.request_type;
            row.dataset.requestId = record.request_id;

            const requestDate = record.date_requested ? new Date(record.date_requested) : null;
            const formattedRequestDate = requestDate && !isNaN(requestDate) ? requestDate.toLocaleDateString() : 'Invalid Date';

            const completedDate = record.date_completed ? new Date(record.date_completed) : null;
             // Format completed date for input field (YYYY-MM-DD)
            const completedDateForInput = completedDate && !isNaN(completedDate) ? completedDate.toISOString().split('T')[0] : '';
            // Format completed date for display (optional, could use the same as input)
            const formattedCompletedDate = completedDate && !isNaN(completedDate) ? completedDate.toLocaleDateString() : '';


            row.innerHTML = `
                <td>${record.request_type || 'N/A'}</td>
                <td>${record.requestor || 'N/A'}</td>
                <td>${record.office_school || 'N/A'}</td>
                <td>${record.request_details || 'N/A'}</td>
                <td>${formattedRequestDate}</td>
                <td>
                    <input type="date" class="completed-date"
                           value="${completedDateForInput}"
                           data-original-value="${completedDateForInput}"
                           data-request-type="${record.request_type}"
                           data-request-id="${record.request_id}">
                </td>
            `;

            tableBody.appendChild(row);
        });

        // Re-attach event listeners for the date completed inputs
        attachCompletionDateListeners();
    }

    // Function to display errors on the page
    function displayError(message) {
        tableBody.innerHTML = ''; // Clear table
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="6" style="text-align: center; color: red; font-weight: bold;">${message}</td>`;
        tableBody.appendChild(row);
    }


    // Attach listeners to dynamically created date inputs
    function attachCompletionDateListeners() {
        document.querySelectorAll('.completed-date').forEach(input => {
            // Remove existing listener to prevent duplicates if called multiple times
            input.removeEventListener('change', handleCompletionDateChange);
            // Add the listener
            input.addEventListener('change', handleCompletionDateChange);
        });
    }

    // Handler function for date change
    function handleCompletionDateChange() {
        const requestType = this.dataset.requestType;
        const requestId = this.dataset.requestId;
        const completedDate = this.value; // YYYY-MM-DD format

        // Optional: Basic validation if needed
        if (!completedDate) {
             console.log("Date cleared, attempting to set to null in DB");
             // Allow clearing the date, send empty string or null representation
        }

        updateCompletionDate(requestType, requestId, completedDate);
    }


    // Function to update completion date via Fetch API
    function updateCompletionDate(requestType, requestId, completedDate) {
        fetch('php/UpdateCompletionDate.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                requestType: requestType,
                requestId: requestId,
                // Send the date as YYYY-MM-DD string, or null if empty
                completedDate: completedDate ? completedDate : null
            })
        })
        .then(response => response.json())
        .then(data => {
            const input = document.querySelector(`input[data-request-type="${requestType}"][data-request-id="${requestId}"]`);
            if (data.success) {
                console.log('Date updated successfully');
                if (input) {
                    input.dataset.originalValue = completedDate || ''; // Update original value
                    input.style.backgroundColor = '#d4edda'; // Success feedback
                    setTimeout(() => {
                        input.style.backgroundColor = '';
                    }, 1500);
                }
                 // Optional: Re-fetch or update local 'allRecords' if needed immediately elsewhere
                 // Example: Find the record in allRecords and update its date_completed
                 const recordIndex = allRecords.findIndex(r => r.request_id == requestId && r.request_type == requestType);
                 if (recordIndex > -1) {
                     allRecords[recordIndex].date_completed = completedDate ? completedDate + ' 00:00:00' : null; // Adjust format if needed
                 }

            } else {
                console.error('Error updating date:', data.error);
                alert('Error updating date: ' + data.error);
                if (input) {
                    input.value = input.dataset.originalValue; // Revert on error
                    input.style.backgroundColor = '#f8d7da'; // Error feedback
                     setTimeout(() => {
                        input.style.backgroundColor = '';
                    }, 1500);
                }
            }
        })
        .catch(error => {
            console.error('Network/Fetch Error:', error);
            alert('Error updating date. Check network connection or console.');
            const input = document.querySelector(`input[data-request-type="${requestType}"][data-request-id="${requestId}"]`);
            if (input) {
                input.value = input.dataset.originalValue; // Revert on error
                 input.style.backgroundColor = '#f8d7da'; // Error feedback
                 setTimeout(() => {
                    input.style.backgroundColor = '';
                }, 1500);
            }
        });
    }

    // Function to prepare and trigger printing
    function printForm() {
        const printWindow = window.open('', '_blank', 'width=1200,height=800');
        if (!printWindow) {
            alert("Please allow popups for this site to print.");
            return;
        }
        const headContent = document.head.innerHTML;
        // Clone the specific container needed for printing
        const recordContainerNode = document.querySelector('.record-container');
        if (!recordContainerNode) {
             console.error("Could not find .record-container element for printing.");
             printWindow.close();
             return;
        }
        const recordContainerClone = recordContainerNode.cloneNode(true);

        // Remove elements not needed for print from the clone
        const adminHeader = recordContainerClone.querySelector('.admin-header');
        if(adminHeader) adminHeader.remove();
        // Ensure the filter container itself is not in the print clone if it was accidentally copied
        const filterContainerInClone = recordContainerClone.querySelector('.date-filter-container');
         if(filterContainerInClone) filterContainerInClone.remove();


        // Add specific styles for printing
       const printStyles = `
            <style>
                @media print {
                    @page {
                        size: A4 landscape; /* Or Letter landscape */
                        margin: 1cm;
                    }
                    body {
                        font-family: 'Times New Roman', Times, serif; /* Common for official docs */
                        font-size: 11pt; /* Adjust as needed */
                        margin: 0;
                        padding: 0;
                        background-color: white !important; /* Ensure white background for print */
                        -webkit-print-color-adjust: exact; /* Try to force printing of backgrounds */
                        print-color-adjust: exact;
                    }

                    .print-wrapper {
                        width: 100%;
                        margin: 0 auto;
                        padding: 0;
                        background-color: white; /* Ensure wrapper is white */
                    }

                    .department-header {
                        display: block !important; /* Ensure visibility */
                        text-align: center;
                        margin-bottom: 10px; /* Space before the <hr> */
                        color: black !important;
                        background-color: white !important; /* Ensure no screen background bleeds */
                        box-shadow: none !important; /* Remove screen shadows */
                    }
                    .department-logos-011 { /* Container for Kagawaran/DepEd logos */
                        margin-bottom: 10px;
                         gap: 2px;
                    }
                    .department-logo-011 { /* Individual Kagawaran/DepEd logos */
                        height: 40px; /* Adjust size as needed */
                        width: 40px;
                        margin: 0 10px;
                        vertical-align: middle;
                       
                    }
                    /* .admin-header was removed from this list, now we hide .admin-header .admin-back-btn specifically */
                    .print-btn, .admin-header .admin-back-btn, .footer, .date-filter-container, #printButton, footer.footer {
                        display: none !important;
                        visibility: hidden !important;
                    }

                    .Recordpage { /* Ensure Recordpage itself doesn't add extra margins/styles in print */
                         margin: 0;
                         padding: 0;
                         border: none;
                         box-shadow: none;
                    }
                     .record01-container {
                         padding: 0; /* Remove padding if any */
                     }
                    .record-container { /* This is the main content area for the report */
                        border: none; /* Remove screen border if any */
                        box-shadow: none; /* Remove screen shadow if any */
                        padding: 0; /* Remove screen padding if any */
                        margin: 0;
                    }

                    /* Table styles for print */
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 15px;
                        margin-bottom: 15px;
                        font-size: 10pt; /* Smaller font for table data */
                    }
                    th, td {
                        border: 1px solid black;
                        text-align: left;
                        padding: 2px 4px;
                        word-wrap: break-word; /* Prevent long text from breaking layout */
                    }
                    th {
                        background-color: #e9ecef !important; /* Light gray background for headers */
                        font-weight: bold;
                        -webkit-print-color-adjust: exact; /* Ensure background color prints */
                        print-color-adjust: exact;
                    }
                     /* Hide the date input element in print, show only the value */
                     td input[type="date"].completed-date {
                         display: none !important; /* Hide input box */
                     }
                     /* Ensure the span holding the date value is visible */
                     td span.printed-date {
                        display: inline !important; /* Make span visible if it was hidden for screen */
                     }

                    /* Signature and Footer styles for the report content */
                    .signature-section {
                        margin-top: 30px; /* More space before signatures */
                        display: flex;
                        justify-content: space-around; /* Space out signatures */
                        font-size: 11pt;
                     }
                     .signature { text-align: center; }
                     .signature-line {
                        border-bottom: 1px solid black;
                        width: 200px; /* Adjust width as needed */
                        margin: 30px auto 5px auto; /* Space above and below line */
                     }
                     .footer-container { /* This is the report's own footer section */
                         margin-top: 20px;
                         font-size: 9pt; /* Smaller footer text */
                     }
                     .footer-01 { display: flex; align-items: center; }
                     .footer-left { flex-shrink: 0; margin-right: 15px;}
                     .footer-left img { height: 30px; margin-right: 5px; }
                     .footer-info { flex-grow: 1; }
                     .record-table { /* Table within the report's footer */
                         font-size: 8pt;
                         margin-left: 20px;
                         border: 1px solid black;
                     }
                      .record-table td { padding: 2px 4px; border: 1px solid black; }


                    /* Filter info display for print */
                    .print-filter-info {
                        font-size: 10pt;
                        margin-bottom: 10px;
                        font-style: italic;
                        border-bottom: 1px dashed #ccc;
                        padding-bottom: 5px;
                    }
                } 
            </style>
        `;

        // Prepare filter information string for printing
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        const selectedType = sortTypeDropdown.options[sortTypeDropdown.selectedIndex].text; // Get text label
        let filterInfo = '<div class="print-filter-info">Report Filters Applied: ';
        let filtersApplied = false;

        if (selectedType !== 'All') {
            filterInfo += `Type: ${selectedType}`;
            filtersApplied = true;
        } else {
             filterInfo += `Type: All`; // Explicitly mention All
             filtersApplied = true; // Consider 'All' as a filter setting
        }

        if (startDate || endDate) {
            filterInfo += (filtersApplied && selectedType !== 'All' ? '; ' : ''); // Add separator if type filter was also applied
            if (startDate && endDate) {
                filterInfo += ` Dates: ${new Date(startDate).toLocaleDateString()} to ${new Date(endDate).toLocaleDateString()}`;
            } else if (startDate) {
                filterInfo += ` Dates: From ${new Date(startDate).toLocaleDateString()}`;
            } else { // Only endDate
                filterInfo += ` Dates: Until ${new Date(endDate).toLocaleDateString()}`;
            }
            filtersApplied = true;
        }

        if (!filtersApplied || (selectedType === 'All' && !startDate && !endDate) ) {
             filterInfo += 'None (Showing all approved records)';
        }


        filterInfo += '</div>';

         // Modify the cloned table to show date values instead of input fields
        const tableToPrint = recordContainerClone.querySelector('table');
        if(tableToPrint) {
            tableToPrint.querySelectorAll('td input[type="date"].completed-date').forEach(input => {
                const dateValue = input.value; // Get the current value (YYYY-MM-DD)
                let displayDate = '';
                if (dateValue) {
                    try {
                         // Attempt to format it nicely (e.g., MM/DD/YYYY)
                        displayDate = new Date(dateValue + 'T00:00:00').toLocaleDateString();
                    } catch (e) {
                        displayDate = dateValue; // Fallback to YYYY-MM-DD if formatting fails
                    }
                }
                const parentTd = input.parentNode;
                if (parentTd) {
                     input.style.display = 'none'; // Hide input just in case CSS fails
                     const span = document.createElement('span');
                     span.className = 'printed-date';
                     span.textContent = displayDate;
                     // Check if span already exists to prevent duplicates on re-print attempts
                     if (!parentTd.querySelector('span.printed-date')) {
                        parentTd.appendChild(span);
                     }
                }
            });
        }


        // Write the content to the new window
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Generated Report - Print</title>
                ${headContent} ${/* Include original head content (like linked CSS if needed) */''}
                ${printStyles}  ${/* Add print-specific styles */''}
            </head>
            <body>
                <div class="print-wrapper">
                    ${recordContainerClone.innerHTML} ${/* Add the cloned content */''}
                </div>
                <script>
                     // Give images time to load before printing
                    function attemptPrint() {
                         // Check if all images are loaded (simple check)
                         const images = document.images;
                         let loaded = true;
                         for (let i = 0; i < images.length; i++) {
                             if (!images[i].complete || images[i].naturalWidth === 0) {
                                 loaded = false;
                                 // Optional: Add onerror/onload handlers for more robust check
                                 break;
                             }
                         }

                         if (loaded) {
                             // Add a small delay for rendering final layout adjustments
                             setTimeout(() => {
                                 window.print();
                                 window.close();
                             }, 500); // 500ms delay
                         } else {
                             // Wait a bit longer and try again
                             console.log("Waiting for images to load...");
                             setTimeout(attemptPrint, 500);
                         }
                     }

                     // Start the process when the document is ready
                     if (document.readyState === 'complete') {
                         attemptPrint();
                     } else {
                         window.onload = attemptPrint;
                     }
                </script>
            </body>
            </html>
        `);
        printWindow.document.close(); // Necessary for some browsers
    }

}); // End DOMContentLoaded