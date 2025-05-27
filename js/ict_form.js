document.addEventListener("DOMContentLoaded", function () {
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');
    let formStatus = '';

    if (!id) {
        window.location.href = 'ict_list.html';
    }

    const actionButton = document.querySelector('.print-btn');

    fetch(`php/ict_form.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('requester_name').textContent = data.requester_name;
            document.getElementById('date_reported').textContent = data.date_reported;
            document.getElementById('position').textContent = data.position;
            document.getElementById('department').textContent = data.department;
            document.getElementById('other_assistance').textContent = data.other_assistance;

            const assistance = data.assistance.split(', ');
            assistance.forEach(item => {
                const checkbox = document.querySelector(`input[value="${item.trim()}"]`);
                if (checkbox) checkbox.checked = true;
            });

            document.getElementById('description').textContent = data.description;
            
            formStatus = data.status;

            if (formStatus === 'Pending') {
                actionButton.textContent = 'Approve';
                actionButton.onclick = approveForm;
            } else {
                actionButton.textContent = 'Print';
                actionButton.onclick = printForm;

                if (formStatus === 'Approved') {
                    document.getElementById('completed').checked = true;
                }
            }
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            alert('Failed to load form data.');
        });
});

function approveForm() {
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');
    
    fetch('php/approve_ictform.php', {
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
    const actionTakenTextarea = document.querySelector('.ict-table tr:nth-child(3) td:nth-child(1) textarea');
    const remarksTextarea = document.querySelector('.ict-table tr:nth-child(3) td:nth-child(2) textarea');
    
    const actionTakenValue = actionTakenTextarea ? actionTakenTextarea.value : '';
    const remarksValue = remarksTextarea ? remarksTextarea.value : '';
    
    const receivedByInput = document.querySelector('.ict-table tr:nth-child(1) td:nth-child(1) input');
    const receivedByValue = receivedByInput ? receivedByInput.value : '';
    
    const dateTimeStarted = document.querySelector('.ict-table tr:nth-child(4) td:nth-child(1) input');
    const dateTimeStartedValue = dateTimeStarted ? dateTimeStarted.value : '';
    
    const dateTimeCompleted = document.querySelector('.ict-table tr:nth-child(4) td:nth-child(2) input:nth-child(1)');
    const dateTimeCompletedValue = dateTimeCompleted ? dateTimeCompleted.value : '';
    
    const icsNumber = document.querySelector('.ict-table tr:nth-child(4) td:nth-child(2) input:nth-child(3)');
    const icsNumberValue = icsNumber ? icsNumber.value : '';
    
    const printWindow = window.open('', '_blank', 'width=800,height=1300');
    const headContent = document.head.innerHTML;
    const formContainer = document.querySelector('.form-container');
    
   const printStyles = `
        <style>
            @page {
                size: A4 portrait;
                margin: 0.5in .5in;
            }
            
            body {
                margin: 0;
                padding: 0;
                background-color: white;
                /* Removed fixed width/height for body, @page handles paper size */
            }
            .department-title {
                font-family: 'Old English', serif;
                font-size: 16x !important;
                margin-bottom: -2px !important;
                font-weight: normal;
            }
            
            .department-title-01 {
                font-family: 'Trajan Pro', serif;
                font-size: 12px !important;
                margin: 0;
                text-transform: uppercase;
                font-weight: normal;
                letter-spacing: 1px;
                text-align: center;
                margin-top: 1opx;
            }
            
            .department-subtitle {
                font-family: 'Old English', serif;
                font-size: 28px !important;
                margin-top -2px !important;
                font-weight: normal;
            }
            
            .department-logo-011 {
                width: 50px !important;
                height: 50px !important;
            }
            
            .print-wrapper {
                width: 8.5in; /* Or 100% if body is sized by @page */
                margin: 0 auto;
                padding: 0;
                background-color: white;
            }
            
            .admin-back-btn, .print-btn, .admin-header, .admin-container > h1, .footer {
                display: none !important;
            }
            
            .form-container {
                box-shadow: none;
                border: none;
                padding: 0.1in; /* Padding for content within the printable area */
                max-width: 100%;
                margin: 0 auto;
            }
            
            input[type="checkbox"] {
                -webkit-appearance: checkbox !important; /* Ensure appearance for printing */
                -moz-appearance: checkbox !important;
                appearance: checkbox !important;
                display: inline-block !important;
                width: auto !important;
                print-color-adjust: exact !important; /* Try to force browser to print checked state */
            }
            
            input[type="checkbox"]:checked {
                /* Forcing a visual cue for checked state in print if default doesn't show well */
                /* This might need more advanced handling or a JS solution to replace with an image for print */
                background-color: #000 !important; /* May not render in all browsers for print */
                border: 1px solid #000 !important;
            }
            input[type="checkbox"]:checked::before {
                /* A common trick for custom checkbox appearance, might work for print */
                content: "✔"; /* Or "X", or an SVG */
                display: block;
                text-align: center;
                color: white; /* If background is dark */
                font-size: 12pt; /* Adjust size */
                position: relative;
                top: -2px; /* Adjust position */
            }
            
            /* Modified rule for inputs and textareas within the form-container */
            .form-container input[type="text"], 
            .form-container textarea {
                font-family: "Bookman Old Style", Georgia, serif !important;
                font-size: 12pt !important;
                border: 1px solid #ccc !important; /* Ensure border prints */
                padding: 5px !important;
                background-color: white !important;
                -webkit-print-color-adjust: exact !important; /* Ensure background and border print */
                print-color-adjust: exact !important;
            }
            
            /* General text styling for specified form parts */
            .form-container .form-title,
            .form-container .form-label,
            .form-container span.form-input,
            .form-container .checkbox-container .checkbox-field label,
            .form-container .checkbox-container .other_assistance-label,
            .form-container .checkbox-container span.other_assistance-input,
            .form-container .description-field .description-label,
            .form-container .description-field span.description-input,
            .form-container .signature-line,
            .form-container .signature-label,
            .form-container .ict-section .ict-label,
            .form-container .ict-section .ict-table td,
            .form-container .ict-section .status-container > div,
            .form-container .ict-section .status-field label,
            .form-container .superintendent .superintendent-name,
            .form-container .superintendent > div:not(.superintendent-name) {
                font-family: "Bookman Old Style", Georgia, serif !important;
                font-size: 12pt !important;
                color: #000 !important; /* Ensure text is black for printing */
            }

            /* Specific adjustment for small text, inherits family */
            .form-container .ict-section .ict-table td small {
                font-size: 12pt !important; /* Adjusted for better readability if 12pt is too large for "small" */
            }
            
            table {
                border-collapse: collapse !important;
                width: 100% !important;
            }
            
            td {
                border: 1px solid #000 !important;
                padding: 5px !important;
                vertical-align: top; /* Better alignment for cells with mixed content */
            }
            
            .footer-container {
                margin-top: 5px !important;
                width: 100% !important;
                display: flex !important;
                align-items: center !important;
                /* Footer font styles are separate and should remain as they are in the original */
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
                margin: 0 15px !important;
            }
            
            .footer-table {
                font-size: 10px !important;
                border-collapse: collapse !important;
                width: auto !important;
                margin-left: 150px !important;
                margin-right: 0 !important;
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
            <title>Print ICT Technical Assistance Form</title>
            ${headContent}
            ${printStyles}
        </head>
        <body>
            <div class="print-wrapper">
                ${formContainer.outerHTML}
            </div>
            <script>
                // Set checkboxes
                document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                    const selector = 'input[name="' + checkbox.name + '"][value="' + checkbox.value + '"]';
                    const originalCheckbox = opener.document.querySelector(selector);
                    if (originalCheckbox) {
                        checkbox.checked = originalCheckbox.checked;
                    }
                });
                
                // Set the ICT Unit section values directly
                const actionTakenTextarea = document.querySelector('.ict-table tr:nth-child(3) td:nth-child(1) textarea');
                if (actionTakenTextarea) {
                    actionTakenTextarea.value = "${actionTakenValue.replace(/"/g, '\\"')}";
                }
                
                const remarksTextarea = document.querySelector('.ict-table tr:nth-child(3) td:nth-child(2) textarea');
                if (remarksTextarea) {
                    remarksTextarea.value = "${remarksValue.replace(/"/g, '\\"')}";
                }
                
                const receivedByInput = document.querySelector('.ict-table tr:nth-child(1) td:nth-child(1) input');
                if (receivedByInput) {
                    receivedByInput.value = "${receivedByValue.replace(/"/g, '\\"')}";
                }
                
                const dateTimeStarted = document.querySelector('.ict-table tr:nth-child(4) td:nth-child(1) input');
                if (dateTimeStarted) {
                    dateTimeStarted.value = "${dateTimeStartedValue.replace(/"/g, '\\"')}";
                }
                
                const dateTimeCompleted = document.querySelector('.ict-table tr:nth-child(4) td:nth-child(2) input:nth-child(1)');
                if (dateTimeCompleted) {
                    dateTimeCompleted.value = "${dateTimeCompletedValue.replace(/"/g, '\\"')}";
                }
                
                const icsNumber = document.querySelector('.ict-table tr:nth-child(4) td:nth-child(2) input:nth-child(3)');
                if (icsNumber) {
                    icsNumber.value = "${icsNumberValue.replace(/"/g, '\\"')}";
                }
                
                // Hide elements that shouldn't be in the print
                document.querySelectorAll('.admin-back-btn, .print-btn, .admin-header, .admin-container > h1, .footer').forEach(el => {
                    el.style.display = 'none';
                });
                
                function waitForImagesToLoad() {
                    const images = document.querySelectorAll('img');
                    const promises = Array.from(images).map(img => {
                        if (img.complete) return Promise.resolve();
                        return new Promise(resolve => {
                            img.onload = resolve;
                            img.onerror = resolve;
                        });
                    });
                    
                    Promise.all(promises).then(() => {
                        setTimeout(() => {
                            window.print();
                        }, 500);
                    });
                }
                
                waitForImagesToLoad();
            </script>
        </body>
        </html>
    `);
    
    printWindow.document.close();
}