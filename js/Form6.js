document.addEventListener("DOMContentLoaded", function () {
  // --- Elements ---
  let othersCheckbox = document.getElementById("others");
  let othersText = document.getElementById("others_text");

  // Part B fields
  let withinPh = document.getElementById("within_ph");
  let withinPhText = document.getElementById("within_ph_text");
  let abroad = document.getElementById("abroad");
  let abroadText = document.getElementById("abroad_text");

  let inHospital = document.getElementById("in_hospital");
  let inHospitalText = document.getElementById("in_hospital_text");
  let outPatient = document.getElementById("out_patient");
  let outPatientText = document.getElementById("out_patient_text");

  let specialLeaveBWSpec = document.getElementById("special_leave_BW_spec");
  let completionOfMastersDegree = document.getElementById(
    "completion_of_masters_degree"
  );
  let boardExamination = document.getElementById("BAR_Board_exam");

  // Part A (main leave options)
  let vacationLeave = document.getElementById("vacation_leave");
  let specialPrivilegeLeave = document.getElementById(
    "special_privilege_leave"
  );
  let sickLeave = document.getElementById("sick-leave");
  let specialLeaveBW = document.getElementById(
    "special_leave_benefits_for_women"
  );
  let studyLeave = document.getElementById("study_leave");

  // --- Initial disable ---
  othersText.disabled = true;
  [
    withinPh,
    withinPhText,
    abroad,
    abroadText,
    inHospital,
    inHospitalText,
    outPatient,
    outPatientText,
    specialLeaveBWSpec,
    completionOfMastersDegree,
    boardExamination,
  ].forEach((el) => (el.disabled = true));

  // --- Others toggle ---
  othersCheckbox.addEventListener("change", function () {
    othersText.disabled = !this.checked;
    if (!this.checked) othersText.value = "";
  });

  // --- Toggle Vacation/Special Privilege (→ Part B: Within PH / Abroad) ---
  function toggleVacationOrSpecial() {
    let enable = vacationLeave.checked || specialPrivilegeLeave.checked;

    [withinPh, abroad].forEach((el) => {
      el.disabled = !enable;
      if (!enable) el.checked = false;
    });

    [withinPhText, abroadText].forEach((el) => {
      el.disabled = true;
      el.value = "";
    });
  }

  // --- Toggle Sick Leave (→ Part B: In Hospital / Out Patient) ---
  function toggleSickLeave() {
    let enable = sickLeave.checked;

    [inHospital, outPatient].forEach((el) => {
      el.disabled = !enable;
      if (!enable) el.checked = false;
    });

    [inHospitalText, outPatientText].forEach((el) => {
      el.disabled = true;
      el.value = "";
    });
  }
  // --- Toggle Special Leave BW (→ Part B: Specify Illness) ---
  function toggleSpecialLeaveBWSpec() {
    let enable = specialLeaveBW.checked;

    specialLeaveBWSpec.disabled = !enable;
    if (!enable) specialLeaveBWSpec.value = "";
  }

  // --- Toggle Study Leave (→ Part B: Completion of Master's Degree / BAR/Board Examination) ---
  function toggleStudyLeave() {
    let enable = studyLeave.checked;

    [completionOfMastersDegree, boardExamination].forEach((el) => {
      el.disabled = !enable;
      if (!enable) el.value = "";
    });
  }

  // --- Wrapper for resetting Part B ---
  function togglePartB() {
    toggleVacationOrSpecial();
    toggleSickLeave();
    toggleSpecialLeaveBWSpec();
    toggleStudyLeave();
  }

  // Attach listeners
  vacationLeave.addEventListener("change", toggleVacationOrSpecial);
  specialPrivilegeLeave.addEventListener("change", toggleVacationOrSpecial);
  sickLeave.addEventListener("change", toggleSickLeave);
  specialLeaveBW.addEventListener("change", toggleSpecialLeaveBWSpec);
  studyLeave.addEventListener("change", toggleStudyLeave);

  withinPh.addEventListener("change", function () {
    withinPhText.disabled = !this.checked;
    if (!this.checked) withinPhText.value = "";
  });

  abroad.addEventListener("change", function () {
    abroadText.disabled = !this.checked;
    if (!this.checked) abroadText.value = "";
  });

  inHospital.addEventListener("change", function () {
    inHospitalText.disabled = !this.checked;
    if (!this.checked) inHospitalText.value = "";
  });

  outPatient.addEventListener("change", function () {
    outPatientText.disabled = !this.checked;
    if (!this.checked) outPatientText.value = "";
  });

  // --- Form submit ---
  document
    .getElementById("myForm")
    .addEventListener("submit", function (event) {
      event.preventDefault();

      // Require "Others" text if checked
      if (othersCheckbox.checked && othersText.value.trim() === "") {
        alert("Please specify your leave type in the 'Others' field.");
        othersText.focus();
        return;
      }

      let formData = new FormData(this);

      // Collect selected options
      let selectedOptions = [];
      document
        .querySelectorAll('input[name="selectedOptions[]"]:checked')
        .forEach((cb) => {
          selectedOptions.push(cb.value);
        });

      if (othersCheckbox.checked) {
        selectedOptions.push(othersText.value.trim());
      }

      formData.delete("selectedOptions[]");
      selectedOptions.forEach((value) => {
        formData.append("selectedOptions[]", value);
      });

      fetch("php/F6submit.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.text())
        .then((data) => {
          const trimmed = data.trim();

          if (trimmed === "success") {
            document.getElementById("myForm").reset();
            othersText.disabled = true;
            togglePartB(); // ✅ reset Part B state

            const notif = document.getElementById("notification");
            notif.innerText =
              "Your request has been submitted. Redirecting you to the CSM form...";
            notif.style.display = "block";

            setTimeout(() => {
              window.location.href =
                "https://forms.office.com/pages/responsepage.aspx?id=fgur1uNloUiDiyou2QxUpg56LmRXJX1Dtawq0RFTnpRUQjlCTEFOOFdRRFFHMjJHRTI0U0lVWE4zOC4u&route=shorturl";
            }, 2000);
          } else {
            alert("Something went wrong. Please try again.");
            console.error("Server response:", trimmed);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("A network error occurred.");
        });
    });
});
