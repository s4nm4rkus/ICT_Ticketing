document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');

    if (id) {
        fetch(`php/help_form.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data) {
                document.getElementById('admin-date-filed').querySelector('.form-input1').textContent = data.date_filed;
                document.getElementById('admin-requesting-office').querySelector('.form-input1').textContent = data.requesting_official_office;
                document.getElementById('admin-requesting-name').querySelector('.form-input1').textContent = data.requesting_official_name;
                document.getElementById('admin-request-details').querySelector('.form-input1').textContent = data.details_request;
                document.getElementById('admin-assistance-datetime').querySelector('.form-input1').textContent = data.date_requested_assistance + " " + data.time_requested_assistance;
                document.getElementById('admin-other-instructions').querySelector('.form-input1').textContent = data.specific_instructions;
                
                const printBtn = document.querySelector('.print-btn');
                if (data.status === 'Pending') {
                    printBtn.textContent = 'Approve';
                } else if (data.status === 'Approved') {
                    printBtn.textContent = 'Print';
                }
            } else {
                console.error('No data found for this ticket.');
            }
        })
        .catch(error => console.error('Error fetching data:', error));
    }
    
    const printBtn = document.querySelector('.print-btn');
    printBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        if (printBtn.textContent === 'Approve') {
            fetch('php/help_form.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=approve&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Request approved successfully!');
                    printBtn.textContent = 'Print';
                } else {
                    alert('Failed to approve request.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while approving the request.');
            });
        } else if (printBtn.textContent === 'Print') {
            printForm();
        }
    });
});

function printForm() {
    const printWindow = window.open('', '_blank', 'width=800,height=1300');
    const headContent = document.head.innerHTML;
    const formContainer = document.querySelector('.form-container');
    const printStyles = `
        <style>
            @page {
                size: legal portrait;
                margin: 0;
            }
            body {
                margin: 0;
                padding: 0;
                background-color: white;
                width: 8.5in;
                height: 10in;
            }
            .print-wrapper {
                width: 8in;
                margin: 0 auto;
                padding: 0;
                background-color: white;
            }
            .admin-back-btn, .print-btn, .admin-header, .admin-container > h1 {
                display: none !important;
            }
            .form-container {
                box-shadow: none;
                border: none;
                padding: 0;
                max-width: 100%;
                margin: 0 auto;
                margin-top: 40px;
            }
            table {
                border-collapse: collapse;
                width: 100%;
            }
            td, th {
                border: 1px solid #000;
                border-collapse: collapse;
                padding: 0px;
            }
            .admin-signature-section {
                display: flex;
                justify-content: space-between;
                margin: 0 px 0;
            }
            .admin-signature-block {
                text-align: center;
                width: 45%;
            }
            .admin-signature-line {
                border-bottom: 1px solid #000;
                margin: 0px 0 5px;
            }
            .admin-signature-name {
                font-size: 12px;
            }
            .admin-noted-by {
                text-align: center;
                margin-top: 20px;
            }
            .admin-name {
                text-align: center;
                font-weight: bold;
            }
            .admin-noted-position {
                text-align: center;
                font-size: 12px;
            }
            .footer {
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                align-items: flex-start;
                margin-top: 10px;
                width: 100%;
            }
            .footer-left {
                display: flex;
                align-items: center;
            }
            .footer-logo {
                width: 40px;
                height: 40px;
                margin-right: 5px;
            }
            .footer-info {
                font-size: 10px;
                line-height: 1.2;
                margin: 0 15px;
            }
            .footer-table {
                font-size: 10px;
                border-collapse: collapse;
                width: auto;
            }
            .footer-table td {
                border: 1px solid #000;
                padding: 0px 0px;
                font-size: 10px;
            }
        </style>
    `;
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print Help Desk Form</title>
            ${headContent}
            ${printStyles}
        </head>
        <body>
            <div class="print-wrapper">
                ${formContainer.outerHTML}
            </div>
            <script>
                document.querySelectorAll('.admin-back-btn, .print-btn, .admin-header, .admin-container > h1').forEach(el => {
                    if (el) {
                        el.style.display = 'none';
                    }
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
