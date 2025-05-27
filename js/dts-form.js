document.addEventListener('DOMContentLoaded', function () {
    const radioButtons = document.querySelectorAll('input[name="requestType"]');

    document.getElementById('retrieveFields').style.display = 'block';

    radioButtons.forEach(function (radio) {
        radio.addEventListener('change', function () {
            const conditionalFields = document.querySelectorAll('.conditional-fields');
            conditionalFields.forEach(field => field.style.display = 'none');

            const selectedValue = this.value;
            const selectedField = document.getElementById(selectedValue + 'Fields');
            if (selectedField) {
                selectedField.style.display = 'block';
            }
        });
    });

    document.getElementById('myForm').addEventListener('submit', function (e) {
        e.preventDefault();

        let formData = new FormData(this);

        fetch("php/DTSSubmit.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            console.log(data); 

            document.getElementById("notification").style.display = "block";

            setTimeout(() => {
                document.getElementById("notification").style.display = "none";
            }, 2000);

            document.getElementById("myForm").reset();

            document.querySelectorAll('.conditional-fields').forEach(field => field.style.display = 'none');
            document.getElementById('retrieveFields').style.display = 'block';
            document.getElementById('retrieve').checked = true;
        })
        .catch(error => console.error("Error:", error));
    });

    document.querySelector('.cancel-btn').addEventListener('click', function () {
        if (confirm('Are you sure you want to cancel?')) {
            document.getElementById('myForm').reset();
            document.querySelectorAll('.conditional-fields').forEach(field => field.style.display = 'none');
            document.getElementById('retrieveFields').style.display = 'block';
            document.getElementById('retrieve').checked = true;
        }
    });
});
