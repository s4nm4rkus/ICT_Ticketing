function showConfirmation() {
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('confirmationDialog').style.display = 'block';
}

function hideConfirmation() {
    document.getElementById('overlay').style.display = 'none';
    document.getElementById('confirmationDialog').style.display = 'none';
}

function confirmLogout() {
    window.location.href = 'php/logout.php'; 
}