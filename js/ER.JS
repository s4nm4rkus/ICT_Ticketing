document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("myForm");

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(form);

    fetch("php/ERsubmit.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.text())
      .then((data) => {
        const trimmed = data.trim();

        if (trimmed === "success") {
          alert(
            "Your request has been submitted. You will now be redirected to the Client Satisfaction Measurement (CSM) form."
          );

          // Reset form fields before redirect
          form.reset();

          // Optional: hide appointment text input if toggled by other logic
          const appointmentLetter =
            document.getElementById("appointmentLetter");
          if (appointmentLetter) appointmentLetter.value = "";

          setTimeout(() => {
            window.location.href =
              "https://forms.office.com/pages/responsepage.aspx?id=fgur1uNloUiDiyou2QxUpg56LmRXJX1Dtawq0RFTnpRUQjlCTEFOOFdRRFFHMjJHRTI0U0lVWE4zOC4u&route=shorturl";
          }, 300); // slight delay for smooth UX
        } else if (trimmed === "error_file_upload") {
          alert("Error uploading file. Please try again.");
        } else {
          alert(
            "Error submitting form. Please try again.\n\nServer response: " +
              trimmed
          );
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert(
          "A network error occurred while submitting. Please check your connection and try again."
        );
      });
  });
});
