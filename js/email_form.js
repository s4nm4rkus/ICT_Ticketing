document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');
    
    if (id) {
        fetch(`php/email_form.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data) {
                document.getElementById('date_reported').textContent = data.date_filed;
                document.getElementById('full_name').textContent = data.full_name;
                document.getElementById('personal_email').textContent = data.personal_email;
                document.getElementById('cellphone').textContent = data.cellphone_number;
                document.getElementById('school').textContent = data.school_name;
                
                const printBtn = document.querySelector('.print-btn');
                if (data.status === 'Pending') {
                    printBtn.textContent = 'Approve';
                } else if (data.status === 'Approved') {
                    printBtn.textContent = 'Print';
                }

                if (data.request_type) {
                    const radioButton = document.querySelector(`input[name="request-type"][value="${data.request_type}"]`);
                    if (radioButton) {
                        radioButton.checked = true;
                    }
                }

                if (data.appointment_letter) {
                    document.querySelector('.admin-form-note').innerHTML = `
                        <p>Attached File: <a href="php/${data.appointment_letter}" download>Download Appointment Letter</a></p>
                    `;
                } else {
                    document.querySelector('.admin-form-note').innerHTML = `<p>No appointment letter attached.</p>`;
                }
            }
        })
        .catch(error => console.error('Error fetching data:', error));
    }
    
    const printBtn = document.querySelector('.print-btn');
    printBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        if (printBtn.textContent === 'Approve') {
            fetch('php/email_form.php', {
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
                size: A4 portrait;
                margin: 0;
            }
            body {
                margin: 0;
                padding: 0;
                background-color: white;
                width: 8.5in;
            }
            .print-wrapper {
                width: 8.5in;
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
                padding: 0.5in;
                max-width: 100%;
                margin: 0 auto;
            }
            input[type="radio"] {
                -webkit-appearance: radio;
                -moz-appearance: radio;
                appearance: radio;
                display: inline-block;
                width: auto;
            }
            input[type="radio"]:checked {
                background-color: #000;
                border-color: #000;
            }
            .footer {
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                align-items: flex-start;
                margin-top: 20px;
                width: 100%;
            }
            .footer-left {
                display: flex;
                align-items: center;
            }
            .footer-logo {
                width: 40px;
                height: 30px;
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
                padding: 2px 5px;
                font-size: 10px;
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
                document.querySelectorAll('input[type="radio"]').forEach(radio => {
                    const selector = 'input[name="' + radio.name + '"][value="' + radio.value + '"]';
                    const originalRadio = opener.document.querySelector(selector);
                    if (originalRadio) {
                        radio.checked = originalRadio.checked;
                    }
                });
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