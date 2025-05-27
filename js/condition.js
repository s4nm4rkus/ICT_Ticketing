         document.addEventListener('DOMContentLoaded', function() {
       
            const radioButtons = document.querySelectorAll('input[name="requestType"]');
            
            document.getElementById('retrieveFields').style.display = 'block';
            
            radioButtons.forEach(function(radio) {
                radio.addEventListener('change', function() {

                    const conditionalFields = document.querySelectorAll('.conditional-fields');
                    conditionalFields.forEach(function(field) {
                        field.style.display = 'none';
                    });
                    
                    const selectedValue = this.value;
                    document.getElementById(selectedValue + 'Fields').style.display = 'block';
                });
            });
            
            document.getElementById('dtsForm').addEventListener('submit', function(e) {
                e.preventDefault();
                alert('Form submitted successfully!');
            });
            
            document.querySelector('.btn-cancel').addEventListener('click', function() {
                if (confirm('Are you sure you want to cancel?')) {
                    document.getElementById('dtsForm').reset();
                    document.querySelectorAll('.conditional-fields').forEach(function(field) {
                        field.style.display = 'none';
                    });
                    document.getElementById('retrieveFields').style.display = 'block';
                    document.getElementById('retrieve').checked = true;
                }
            });
        });