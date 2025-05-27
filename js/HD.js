document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('myForm').addEventListener('submit', function(e) {
        e.preventDefault(); 

        const formData = new FormData(this);

        fetch('php/HDsubmit.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === "success") {
                const notification = document.getElementById('notification');
                notification.style.display = 'block';
                setTimeout(() => {
                    notification.style.display = 'none';
                    document.getElementById('myForm').reset();
                }, 3000);
            } else {
                alert("Error submitting form. Please try again.");
            }
        })
        .catch(error => console.error('Error:', error));
    });
});
