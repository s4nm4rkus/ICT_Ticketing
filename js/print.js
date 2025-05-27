function printForm() {
    let printContent = document.getElementById("printableForm").innerHTML;
    let originalContent = document.body.innerHTML;

    document.body.innerHTML = `
        <html>
        <head>
            <title>Print Form</title>
            <style>
                @page {
                    size: A4;
                    margin: 20mm;
                }
                body {
                    font-family: Arial, sans-serif;
                }
            </style>
        </head>
        <body>
            ${printContent}
        </body>
        </html>
    `;

    window.print();
    location.reload(); // Restore the original page after printing
}
