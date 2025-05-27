document.addEventListener("DOMContentLoaded", function () {
    const params = new URLSearchParams(window.location.search);
    const id = params.get('id');

    if (id) {
        fetchTicketDetails(id);
    }
});

function fetchTicketDetails(id) {
    fetch(`php/DTS_form.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const ticket = data.ticket;
                const formStatus = ticket.status || 'Pending';

                document.getElementById("dts_number").textContent = ticket.dtsNumber || "N/A";
                document.getElementById("date").textContent = ticket.date || "N/A";
                document.getElementById("name_requester").textContent = ticket.requesterName || "N/A";
                document.getElementById("mobile_number").textContent = ticket.mobileNumber || "N/A";
                document.getElementById("school").textContent = ticket.school || "N/A";

                checkAndPopulate("retrieve", ticket.requestType, "retrieve", {
                    "unitName": ticket.unitName,
                    "reason": ticket.reason
                });

                checkAndPopulate("edit-title", ticket.requestType, "editDocument", {
                    "newTitle": ticket.newTitle,
                    "editReason": ticket.editReason
                });

                checkAndPopulate("cancel-transaction", ticket.requestType, "cancelTransaction", {
                    "cancelReason": ticket.cancelReason
                });

                checkAndPopulate("reset-password", ticket.requestType, "resetPassword", {
                    "emailAddress": ticket.emailAddress
                });

                checkAndPopulate("new-user", ticket.requestType, "newUserEmail", {
                    "newUserEmail": ticket.newEmail
                });

                checkAndPopulate("add-document", ticket.requestType, "addDocument", {
                    "documentType": ticket.documentType,
                    "processDays": ticket.processDays
                });

                const actionButton = document.querySelector('.print-btn');
                if (actionButton) {
                    if (formStatus === 'Pending') {
                        actionButton.textContent = 'Approve';
                        actionButton.onclick = approveForm;
                    } else {
                        actionButton.textContent = 'Print';
                        actionButton.onclick = printForm;
                    }
                }
            } else {
                console.error("Error:", data.message);
            }
        })
        .catch(error => {
            console.error("Error fetching ticket details:", error);
        });
}

function checkAndPopulate(checkboxId, requestType, expectedType, fields) {
    if (requestType === expectedType) {
        const checkbox = document.getElementById(checkboxId);
        if (checkbox) {
            checkbox.checked = true;
            for (const field in fields) {
                const element = document.getElementById(field);
                if (element) {
                    element.textContent = fields[field] || "N/A";
                }
            }
        }
    }
}

function approveForm() {
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');
    
    fetch('php/approve_dtsform.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}&status=Approved`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Form has been approved!');
            location.reload();
        } else {
            alert('Failed to approve the form. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error updating status:', error);
        alert('Failed to approve the form. Please try again.');
    });
}

function printForm() {
    const printWindow = window.open('', '_blank', 'width=800,height=1300');
    const headContent = document.head.innerHTML;
    const formContainer = document.querySelector('.form-container');
    const printStyles = `
        <style>
            @page {
                size: A4 portrait;
                margin: 0.2in .5in;
            }
            
            body {
                margin: 0;
                padding: 0;
                background-color: white;
            }
            .department-header {
                text-align: center;
                margin-bottom: 10px;
            }
            
            .print-wrapper {
                width: 100%;
                margin: 0 auto;
                padding: 0;
                background-color: white;
                font-size: 0.9em; 
                line-height: 1;
            }
            
            .admin-back-btn, .print-btn, .admin-header, .admin-container > h1, .footer { /* .footer added to hide the one outside form-container */
                display: none !important;
            }
            
            .form-container {
                box-shadow: none;
                border: none;
                padding: 0; /* Padding is handled by @page margins now for the whole page content */
                max-width: 100%;
                margin: 0 auto;
            }
            .form-title {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            border-top: 1px solid #4c4c4c7c;
            border-bottom: 1px solid #4c4c4c7c;
            padding: 3px 0;
            margin-bottom: 2px;
            }

            /* Font styles for specified elements */
            .form-container .form-label,
            .form-container label.form-input,
            .form-container > p,
            .form-container .dts-form-container .box .form-group > label,
            .form-container .dts-form-container .box label.dts-label,
            .form-container .full-width > label[for], /* Targets labels associated with checkboxes */
            .form-container .full-width > label:not([for]), /* Targets static labels like "Reason:" if any, or data labels */
            .form-container .full-width label.dts-label,
            .form-container .signature-line,
            .form-container .add-document-box .form-group > label,
            .form-container .add-document-box label.dts-label,
            .form-container .ict-label,
            .form-container .ict-table td,
            .form-container .ict-table textarea.form-input {
                font-family: "Bookman Old Style", Georgia, serif !important;
                font-size: 9pt !important;
                padding: 1px !important;
                margin-bottom: 1px !important;
            }
            
            input[type="checkbox"] {
                -webkit-appearance: checkbox !important;
                -moz-appearance: checkbox !important;
                appearance: checkbox !important;
                display: inline-block !important;
                width: auto !important;
                print-color-adjust: exact !important;
            }
            
            input[type="checkbox"]:checked {
                background-color: #000 !important; /* May not render consistently across browsers for print */
                border-color: #000 !important;
            }
             input[type="checkbox"]:checked::before {
                content: "✔"; 
                display: block;
                text-align: center;
                color: white; 
                font-size: 10pt; 
                position: relative;
                top: -2px; 
                left: 0px;
            }
            
            input[type="text"], textarea { /* General styling for text inputs/textareas */
                border: 1px solid #ccc !important;
                padding: 2px !important;
                background-color: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            table {
                border-collapse: collapse !important;
                width: 100% !important;
            }
            
            td {
                border: 1px solid #000 !important;
                padding: 2px !important;
                vertical-align: top;
            }
            
            .footer-01 {
                background-color: #f8f9fa; 
                color: #333; 
                text-align: center;
                padding: 0px;
                position: relative;
                bottom: 0;
                width: 100%;
                font-size: 14px;
                font-weight: bold;
                margin-top: 1px;
                padding-bottom: 50px;
                margin-right:10 px;
            }
            .footer-container {
                margin-top: 0px !important;
                margin-right: 0px !important;
                width: 100% !important;
                display: flex !important;
                align-items: center !important;
            }
            
            .footer-left {
                display: flex !important;
                align-items: center !important;
            }
            
            .footer-logo {
                width: 40px !important;
                height: 30px !important;
                margin-right: 5px !important;
            }
            
            .footer-info {
                font-size: 10px !important; /* Example: Keep footer font size as is */
                line-height: 1.2 !important;
                margin: 0 5px !important;
                text-align: left;
            }
            
            .footer-table {
                font-size: 10px !important;
                border-collapse: collapse !important;
                width: auto !important;
                margin-left: 0px !important;
                margin-right: 10px !important;
            }
            
            .footer-table td {
                border: 1px solid #000 !important;
                padding: 2px 5px !important;
                font-size: 10px !important; /* Example: Keep footer font size as is */
            }

        </style>
    `;
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print DTS Request Form</title>
            ${headContent}
            ${printStyles}
        </head>
        <body>
            <div class="print-wrapper">
                ${formContainer.outerHTML}
            </div>
            <script>
                document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                    const originalCheckbox = opener.document.querySelector('#' + checkbox.id);
                    if (originalCheckbox) {
                        checkbox.checked = originalCheckbox.checked;
                    }
                });
                
                document.querySelectorAll('input[type="text"], textarea, label.form-input, label.dts-label').forEach(input => {
                    const selector = input.id ? '#' + input.id : (input.name ? 'input[name="' + input.name + '"], textarea[name="' + input.name + '"]' : null);
                    if (selector) {
                        const originalInput = opener.document.querySelector(selector);
                        if (originalInput) {
                            if (input.tagName === 'LABEL') {
                                input.textContent = originalInput.textContent;
                            } else {
                                input.value = originalInput.value;
                            }
                        }
                    }
                });
                
                const ictTable = document.querySelector('.ict-table');
                if (ictTable) {
                    const originalIctTable = opener.document.querySelector('.ict-table');
                    if (originalIctTable) {
                        const printTextareas = ictTable.querySelectorAll('textarea');
                        const originalTextareas = originalIctTable.querySelectorAll('textarea');
                        
                        for (let i = 0; i < Math.min(printTextareas.length, originalTextareas.length); i++) {
                            printTextareas[i].value = originalTextareas[i].value;
                        }
                    }
                }
                
                document.querySelectorAll('.admin-back-btn, .print-btn, .admin-header, .admin-container > h1').forEach(el => {
                    if(el) el.style.display = 'none';
                });

                
                function waitForImagesToLoad() {
                    const images = document.querySelectorAll('img');
                    const promises = Array.from(images).map(img => {
                        if (img.complete && img.naturalHeight !== 0) return Promise.resolve();
                        return new Promise(resolve => {
                            img.onload = resolve;
                            img.onerror = resolve; // Resolve on error too to not block printing
                        });
                    });
                    Promise.all(promises).then(() => {
                        setTimeout(() => {
                            window.print();
                             //  window.close(); // Optional: close window after print dialog
                        }, 500); // Delay to ensure rendering
                    });
                }
                waitForImagesToLoad();
            <\/script>
        </body>
        </html>
    `);
    
    printWindow.document.close();
}