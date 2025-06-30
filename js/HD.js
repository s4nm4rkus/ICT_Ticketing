document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("myForm");

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(form);

    fetch("php/HDsubmit.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.text())
      .then((data) => {
        const trimmed = data.trim();

        if (trimmed === "success") {
          // Reset the form first
          form.reset();

          // Show alert before redirection
          alert(
            "Your request has been submitted. You will now be redirected to the Client Satisfaction Measurement (CSM) form."
          );

          // Redirect after a short delay
          setTimeout(() => {
            window.location.href =
              "https://forms.office.com/pages/responsepage.aspx?id=fgur1uNloUiDiyou2QxUpg56LmRXJX1Dtawq0RFTnpRUQjlCTEFOOFdRRFFHMjJHRTI0U0lVWE4zOC4u&route=shorturl";
          }, 100);
        } else {
          alert(
            "Error submitting the form. Please try again.\n\nServer response: " +
              trimmed
          );
        }
      })
      .catch((error) => {
        console.error("Fetch Error:", error);
        alert(
          "A network error occurred while submitting. Please check your internet connection and try again."
        );
      });
  });
});
