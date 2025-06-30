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

  document
    .getElementById("myForm")
    .addEventListener("submit", function (event) {
      event.preventDefault();

      let formData = new FormData(this);

      fetch("php/TAsubmit.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.text())
        .then((data) => {
          const trimmed = data.trim();

          if (trimmed === "success") {
            // Reset the form before redirect
            document.getElementById("myForm").reset();
            othersText.disabled = true;

            document.getElementById("notification").style.display = "block";
            setTimeout(() => {
              document.getElementById("notification").style.display = "none";
            }, 2000);

            // Show alert and then redirect
            alert(
              "Your request has been submitted. You will now be redirected to the Client Satisfaction Measurement (CSM) form."
            );

            window.location.href =
              "https://forms.office.com/pages/responsepage.aspx?id=fgur1uNloUiDiyou2QxUpg56LmRXJX1Dtawq0RFTnpRUQjlCTEFOOFdRRFFHMjJHRTI0U0lVWE4zOC4u&route=shorturl";
          } else {
            alert("Something went wrong. Please try again.");
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("A network error occurred.");
        });
    });
});
