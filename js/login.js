document.addEventListener("DOMContentLoaded", function () { 
    document.getElementById("password").addEventListener("keypress", function (event) {
        if (event.key === "Enter") {
            document.querySelector("form").submit();
        }
    });

    document.querySelector(".toggle-password").addEventListener("click", togglePassword);
});

function togglePassword() {
    const passwordField = document.getElementById("password");
    const eyeIcon = document.getElementById("eye-icon");

    if (passwordField.type === "password") {
        passwordField.type = "text";
        eyeIcon.src = "./Images/eye.png";
    } else {
        passwordField.type = "password";
        eyeIcon.src = "./Images/hidden.png";
    }
}