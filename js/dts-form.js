document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("myForm");
  const radioButtons = document.querySelectorAll('input[name="requestType"]');

  // Default: show retrieveFields
  document.getElementById("retrieveFields").style.display = "block";

  // Toggle conditional fields on radio button change
  radioButtons.forEach(function (radio) {
    radio.addEventListener("change", function () {
      document.querySelectorAll(".conditional-fields").forEach((field) => {
        field.style.display = "none";
      });

      const selectedField = document.getElementById(this.value + "Fields");
      if (selectedField) {
        selectedField.style.display = "block";
      }
    });
  });

  // Submit form
  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(form);

    fetch("php/DTSSubmit.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.text())
      .then((data) => {
        const trimmed = data.trim();
        console.log("Response:", trimmed);

        if (trimmed === "success") {
          // Reset form and conditional fields BEFORE redirect
          form.reset();
          document.querySelectorAll(".conditional-fields").forEach((field) => {
            field.style.display = "none";
          });
          document.getElementById("retrieveFields").style.display = "block";
          document.getElementById("retrieve").checked = true;

          // Show notification
          const notif = document.getElementById("notification");
          notif.innerText =
            "Your request has been submitted. Redirecting you to the CSM form...";
          notif.style.display = "block";

          // Alert and redirect
          setTimeout(() => {
            window.location.href =
              "https://forms.office.com/pages/responsepage.aspx?id=fgur1uNloUiDiyou2QxUpg56LmRXJX1Dtawq0RFTnpRUQjlCTEFOOFdRRFFHMjJHRTI0U0lVWE4zOC4u&route=shorturl";
          }, 200);
        } else {
          alert("Something went wrong. Please try again.");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("A network error occurred. Please try again.");
      });
  });

  // Cancel button behavior
  document.querySelector(".cancel-btn").addEventListener("click", function () {
    if (confirm("Are you sure you want to cancel?")) {
      form.reset();
      document.querySelectorAll(".conditional-fields").forEach((field) => {
        field.style.display = "none";
      });
      document.getElementById("retrieveFields").style.display = "block";
      document.getElementById("retrieve").checked = true;
    }
  });
});
