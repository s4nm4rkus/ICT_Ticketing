
    document.getElementById('phone').addEventListener('input', function (e) {
        this.value = this.value.replace(/\D/g, ''); // Remove non-numeric characters
        if (this.value.length > 11) {
            this.value = this.value.slice(0, 11); // Limit to 11 digits
        }
    });