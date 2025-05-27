document.addEventListener("DOMContentLoaded", function () {
    let othersCheckbox = document.getElementById("others");
    let othersText = document.getElementById("others_text");

    othersText.disabled = true;

    othersCheckbox.addEventListener("change", function () {
        othersText.disabled = !this.checked;
        if (!this.checked) {
            othersText.value = ""; 
        }
    });

    document.getElementById("myForm").addEventListener("submit", function(event) {
        event.preventDefault(); 

        let formData = new FormData(this);

        fetch("php/TAsubmit.php", {
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

            othersText.disabled = true;
        })
        .catch(error => console.error("Error:", error));
    });
});
