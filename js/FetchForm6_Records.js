document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  const id = params.get("id");

  if (!id) {
    console.warn("No parent id found in URL");
    return;
  }

  // Set hidden parent_id
  const formIdInput = document.getElementById("form_id");
  if (formIdInput) formIdInput.value = id;

  // Fetch ALL related data
  fetchForm6Details(id);
  fetchChildForm(id);
  fetchAdminUpdate(id);
  fetchASDSSDSUpdate(id);
  fetchOtherDetails(id); // ✅ THIS IS YOUR APPROVED / DISAPPROVED INPUTS
  setEsignForSDSorASDS(id);

  // Personnel update button
  const personnelUpdateBtn = document.querySelector(".personnel-update");
  if (personnelUpdateBtn) {
    personnelUpdateBtn.addEventListener("click", (e) => {
      e.preventDefault();
      submitChildForm();
    });
  }

  // Admin approve button
  const adminApproveBtn = document.getElementById("admin-approve-btn");
  if (adminApproveBtn) {
    adminApproveBtn.addEventListener("click", handleAdminApproveButton);
  }

  // ASDS/SDS approve button
  const asdsSdsApproveBtn = document.getElementById("asdsSdsApproveBtn");
  if (asdsSdsApproveBtn) {
    asdsSdsApproveBtn.addEventListener("click", handleASDSSDSApproveButton);
  }

  console.log("DOM fully loaded with parent_id:", id);
});

// // Preview uploaded E-sign
// document
//   .getElementById("signature_upload_top_management")
//   .addEventListener("change", function () {
//     const file = this.files[0];
//     if (!file) return;

//     const reader = new FileReader();
//     reader.onload = function (e) {
//       document.getElementById("signature_image_top_management").src =
//         e.target.result;

//       document.getElementById("signature_wrapper").style.display = "flex";
//     };
//     reader.readAsDataURL(file);
//   });

/* ============================================
    MAIN PARENT FORM FETCH
============================================ */
function fetchForm6Details(id) {
  fetch(`php/form6.php?id=${id}`)
    .then((response) => response.json())
    .then((data) => {
      if (!data.success) return console.error("Fetch error:", data.message);

      const form = data.ticket;
      document.getElementById("form_id").value = form.id;

      const formStatus = form.status || "For Records Unit";

      /* ===============================
          BASIC FIELDS LOADING
      =============================== */
      document.getElementById("department").value = form.department || "";
      document.getElementById("last_name").value = form.last_name || "";
      document.getElementById("first_name").value = form.first_name || "";
      document.getElementById("middle_name").value = form.middle_name || "";
      document.getElementById("date_of_filing").value =
        form.date_of_filing || "";
      document.getElementById("position").value = form.position || "";
      document.getElementById("salary").value = form.salary || "";
      document.getElementById("number_of_days_applied").value =
        form.number_of_days_applied || "";
      document.getElementById("inclusive_days").value =
        form.inclusive_days || "";
      document.getElementById("name_of_official").value =
        form.name_of_official || "";
      document.getElementById("signatory_position").value =
        form.signatory_position || "";

      /* ===============================
          LEAVE TYPE CHECKBOXES
      =============================== */
      const leaveTypes = (form.typeofleave_A || "")
        .split(",")
        .map((t) => t.trim());

      document
        .querySelectorAll('input[name="selectedOptions[]"]')
        .forEach((cb) => {
          cb.checked = leaveTypes.some(
            (val) =>
              val.replace(/\s+/g, " ").trim() ===
              cb.value.replace(/\s+/g, " ").trim(),
          );
        });
      const specs = (form.specification_of_leave || "")
        .split(";")
        .map((s) => s.trim());
      specs.forEach((spec) => {
        if (spec.startsWith("Within Philippines:")) {
          document.getElementById("within_ph").checked = true;
          document.getElementById("within_ph_text").value = spec
            .replace("Within Philippines:", "")
            .trim();
          document.getElementById("within_ph_text").disabled = false;
        }
        if (spec.startsWith("Abroad:")) {
          document.getElementById("abroad").checked = true;
          document.getElementById("abroad_text").value = spec
            .replace("Abroad:", "")
            .trim();
          document.getElementById("abroad_text").disabled = false;
        }
        if (spec.startsWith("In Hospital:")) {
          document.getElementById("in_hospital").checked = true;
          document.getElementById("in_hospital_text").value = spec
            .replace("In Hospital:", "")
            .trim();
          document.getElementById("in_hospital_text").disabled = false;
        }
        if (spec.startsWith("Out Patient:")) {
          document.getElementById("out_patient").checked = true;
          document.getElementById("out_patient_text").value = spec
            .replace("Out Patient:", "")
            .trim();
          document.getElementById("out_patient_text").disabled = false;
        }
        if (spec.startsWith("Special Leave (Women):")) {
          document.getElementById("special_leave_BW_spec").value = spec
            .replace("Special Leave (Women):", "")
            .trim();
          document.getElementById("special_leave_BW_spec").disabled = false;
        }
        if (spec.includes("Completion of Master's Degree")) {
          document.getElementById("completion_of_masters_degree").checked =
            true;
        }
        if (spec.includes("BAR/Board Examination Review")) {
          document.getElementById("BAR_Board_exam").checked = true;
        }
      });

      // === COMMUNICATION OPTIONS ===
      const commTypes = (form.communication || "")
        .split(",")
        .map((t) => t.trim());
      document
        .querySelectorAll('input[name="communication[]"]')
        .forEach((cb) => {
          cb.checked = commTypes.includes(cb.value);
        });

      // === OTHER PURPOSE OPTIONS ===
      const savedOtherPurposes = (form.other_purpose || "")
        .split(",")
        .map((p) => p.trim());
      document
        .querySelectorAll('input[name="otherPurpose[]"]')
        .forEach((checkbox) => {
          checkbox.checked = savedOtherPurposes.includes(checkbox.value);
        });

      // === HANDLE “OTHERS” FIELD ===
      const othersCheckbox = document.getElementById("others");
      const othersText = document.getElementById("others_text");
      if (leaveTypes.includes("Others")) {
        othersCheckbox.checked = true;
        othersText.disabled = false;
        othersText.value = form.other_type_of_leave || "";
      }

      /* ===============================
          E-SIGN DISPLAY
      =============================== */
      if (form.e_signature) {
        const signatureImg = document.getElementById("signature_image");
        if (signatureImg) {
          signatureImg.src = `php/${form.e_signature}`;
        }
      }

      /* ===============================
          ADMIN UPDATE STATUS BUTTON
      =============================== */
      fetchAdminUpdate(form.id);

      /* ===============================
          WORKFLOW BUTTON HANDLER
      =============================== */
      const actionButton = document.querySelector(".print-btn");
      if (actionButton) {
        setActionButton(actionButton, form.id, formStatus);
      }
    })
    .catch((error) => console.error("Error fetching form details:", error));
}

/* ============================================
    WORKFLOW BUTTON SETTER
============================================ */
function setActionButton(button, id, status) {
  const updateBtn = document.querySelector(".personnel-update");
  const adminSignBtn = document.querySelector(".admin-approve-btn");
  const eSignInput = document.querySelector(".asds-sds-approve-btn");

  if (updateBtn)
    updateBtn.style.display =
      status === "For Personnel Unit" ? "inline-block" : "none";

  if (adminSignBtn)
    adminSignBtn.style.display =
      status === "For Admin Unit" ? "inline-block" : "none";

  eSignInput.style.display =
    status === "For SDS/ASDS/Records" ? "inline-block" : "none";

  if (status === "Approved" || status === "Disapproved") {
    eSignInput.style.display = "none";
  }

  switch (status) {
    case "For Records Unit":
      button.textContent = "Receive";
      button.onclick = () => updateStatus(id, "For Personnel Unit", button);
      break;

    case "For Personnel Unit":
      button.textContent = "Forward to Admin";
      button.onclick = () => updateStatus(id, "For Admin Unit", button);
      break;

    case "For Admin Unit":
      button.textContent = "Forward to SDS/ASDS";
      button.onclick = () => updateStatus(id, "For SDS/ASDS/Records", button);
      break;

    case "For SDS/ASDS/Records":
      button.textContent = "Complete";
      button.onclick = () => handleCompleteAction(id, button);
      break;

    case "Approved":
    case "Disapproved":
      button.textContent = "Print";
      button.onclick = () => printForm(id);
      break;
  }
}

let pendingRedirect = null;

function showConfirmModal(redirectUrl) {
  const overlay = document.getElementById("confirm-overlay");
  const doneBtn = overlay.querySelector(".card-button.primary");
  const closeBtn = overlay.querySelector(".exit-button");

  pendingRedirect = redirectUrl;

  overlay.classList.add("show");

  const closeModal = () => {
    overlay.classList.remove("show");

    setTimeout(() => {
      if (pendingRedirect) {
        window.location.replace(pendingRedirect);
      }
    }, 300); // wait for fade-out
  };

  doneBtn.onclick = closeModal;
  closeBtn.onclick = closeModal;
}

/* ============================================
    UPDATE WORKFLOW STATUS
============================================ */
function updateStatus(id, newStatus, button) {
  fetch("php/form6_update_status.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id=${id}&status=${encodeURIComponent(newStatus)}`,
  })
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) {
        alert("Failed to update status");
        return;
      }

      // Update UI state if needed
      setActionButton(button, id, newStatus);

      // ✅ Show success modal instead of redirecting immediately
      showConfirmModal("form6_list.php"); // or .php
    })
    .catch((err) => console.error("Error updating status:", err));
}

/* ============================================
    PERSONNEL UPDATE (CHILD FORM)
============================================ */
function submitChildForm() {
  const formData = new FormData(document.getElementById("leave_form"));
  const parentId = document.getElementById("form_id").value;

  formData.append("parent_id", parentId);

  fetch("php/F6submit_personnel_update_leave.php", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) return alert("Error: " + data.error);

      alert("Personnel update saved!");
      fetchChildForm(parentId);
    })
    .catch((err) => console.error("Error submitting child form:", err));
}

/* ============================================
    FETCH CHILD-PERSONNEL DATA
============================================ */
function fetchChildForm(parentId) {
  fetch(`php/fetch_form6_personnel_updates.php?parent_id=${parentId}`)
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) return;

      const child = data.data;

      document.getElementById("as_of").value = child.as_of || "";
      document.getElementById("vacation_leave_total_earned").value =
        child.vacation_leave_total_earned || "";
      document.getElementById("sick_leave_total_earned").value =
        child.sick_leave_total_earned || "";
      document.getElementById("vacation_leave_less_this_application").value =
        child.vacation_leave_less_this_application || "";
      document.getElementById("sick_leave_less_this_application").value =
        child.sick_leave_less_this_application || "";
      document.getElementById("vacation_leave_balance").value =
        child.vacation_leave_balance || "";
      document.getElementById("sick_leave_balance").value =
        child.sick_leave_balance || "";
    })
    .catch((err) => console.error("Error fetching child form:", err));
}

/* ============================================
    ADMIN APPROVAL + UNSIGN WITH MODAL
============================================ */
let pendingAction = null;

function handleAdminApproveButton() {
  const btn = document.getElementById("admin-approve-btn");
  const state = btn.getAttribute("data-state");

  if (state === "processing") {
    showAdminModal("Are you sure?", "approve");
  } else {
    showAdminModal("Are you sure?", "unsign");
  }
}

function showAdminModal(message, actionType) {
  document.getElementById("adminConfirmText").innerText = message;
  document.getElementById("adminConfirmModal").style.display = "flex";
  pendingAction = actionType;
}

document.getElementById("confirmNoBtn").addEventListener("click", () => {
  document.getElementById("adminConfirmModal").style.display = "none";
  pendingAction = null;
});

document.getElementById("confirmYesBtn").addEventListener("click", () => {
  document.getElementById("adminConfirmModal").style.display = "none";

  if (pendingAction === "approve") submitAdminApproval();
  else if (pendingAction === "unsign") submitAdminUnsign();

  pendingAction = null;
});

/* ============================================
    APPROVE (SIGN)
============================================ */
function submitAdminApproval() {
  const formData = new FormData(
    document.getElementById("leave_form_admin_update"),
  );
  const parentId = document.getElementById("form_id").value;

  formData.append("parent_id", parentId);
  formData.append("admin_esign", "Approved & Signed");

  fetch("php/F6submit_admin_update_leave.php", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) return alert("Error: " + data.error);

      alert("Admin approval recorded successfully!");
      setApproveButtonState("signed");
      fetchAdminUpdate(parentId);
    })
    .catch((err) => console.error("Error submitting admin approval:", err));
}

/* ============================================
    UNSIGN (RETURN TO PROCESSING)
============================================ */
function submitAdminUnsign() {
  const formData = new FormData(
    document.getElementById("leave_form_admin_update"),
  );
  const parentId = document.getElementById("form_id").value;

  formData.append("parent_id", parentId);
  formData.append("admin_esign", "processing");

  fetch("php/F6submit_admin_update_leave.php", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) return alert("Error: " + data.error);

      alert("Signature removed. Status reverted to processing.");
      setApproveButtonState("processing");
      fetchAdminUpdate(parentId);
    })
    .catch((err) => console.error("Error submitting admin unsign:", err));
}

/* ============================================
    UPDATE APPROVE BUTTON STATE UI
============================================ */
function setApproveButtonState(state) {
  const btn = document.getElementById("admin-approve-btn");
  const adminEsignWrapper = document.getElementById("admin_esign_wrapper");

  if (state === "signed") {
    btn.innerText = "Unsigned";
    btn.style.background = "#ff5252";
    btn.setAttribute("data-state", "signed");
    if (adminEsignWrapper) adminEsignWrapper.style.display = "inline-block";
  } else {
    btn.innerText = "Approve & Sign";
    btn.style.background = "#2291ff";
    btn.setAttribute("data-state", "processing");
    if (adminEsignWrapper) adminEsignWrapper.style.display = "none";
  }
}

/* ============================================
    FETCH ADMIN UPDATE
============================================ */
function fetchAdminUpdate(parentId) {
  fetch(`php/fetch_form6_signatories_updates.php?parent_id=${parentId}`)
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) {
        console.error(data.error);
        return;
      }

      const child = data.data;
      let status = (child.admin_esign || "").toLowerCase().trim();

      // Normalize matching
      const isSigned =
        status.includes("approved") ||
        status.includes("signed") ||
        status.includes("approve");

      if (isSigned) {
        setApproveButtonState("signed");
      } else {
        setApproveButtonState("processing");
      }
    })
    .catch((err) => console.error("Error fetching admin update:", err));
}

let pendingASDSAction = null;

/* ============================================
  Handle ASDS/SDS Approve / Unsign click
============================================ */
function handleASDSSDSApproveButton() {
  console.log("Button clicked!");
  const btn = document.getElementById("asdsSdsApproveBtn");
  const state = btn.getAttribute("data-state");

  if (state === "processing") {
    showASDSSDSModal("Are you sure?", "approve");
  } else {
    showASDSSDSModal("Are you sure?", "unsign");
  }
}

/* ============================================
  Show modal
============================================ */
function showASDSSDSModal(message, actionType) {
  document.getElementById("ASDS_SDSConfirmText").innerText = message;
  document.getElementById("ASDS_SDSConfirmModal").style.display = "flex";
  pendingASDSAction = actionType;
}

/* ============================================
  Modal buttons
============================================ */
document
  .getElementById("ASDS_SDSconfirmNoBtn")
  .addEventListener("click", () => {
    document.getElementById("ASDS_SDSConfirmModal").style.display = "none";
    pendingASDSAction = null;
  });

document
  .getElementById("ASDS_SDSconfirmYesBtn")
  .addEventListener("click", () => {
    document.getElementById("ASDS_SDSConfirmModal").style.display = "none";

    if (pendingASDSAction === "approve") submitASDSApproval();
    else if (pendingASDSAction === "unsign") submitASDSSDSUnsign();

    pendingASDSAction = null;
  });

/* ============================================
  Submit ASDS/SDS Approval
============================================ */

function submitASDSApproval() {
  const parentId = document.getElementById("form_id").value;

  // Collect input values
  const daysWithPay = document.getElementById("daysWithPay").value || 0;
  const daysWithoutPay = document.getElementById("daysWithoutPay").value || 0;
  const others = document.getElementById("approveForOthers").value.trim();
  const disapprovedDueTo =
    document.getElementById("disapprovedDueTo").value || "";

  // Prepare FormData
  const formData = new FormData();
  formData.append("parent_id", parentId);
  formData.append("asds_sds_esign", "Approved & Signed");
  formData.append("approve_for_days_with_pay", daysWithPay);
  formData.append("approve_for_days_without_pay", daysWithoutPay);
  formData.append("approve_for_others", others);
  formData.append("disapproved_due_to", disapprovedDueTo);

  // Send POST request
  fetch("php/F6submit_asds_sds_update.php", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) return alert("Error: " + data.error);

      // Success message
      alert("ASDS/SDS approval recorded successfully!");

      // Update button and e-signature
      setASDSSDSButtonState("signed");

      // Refresh ASDS/SDS data if needed
      fetchASDSSDSUpdate(parentId);
    })
    .catch((err) => console.error("Error submitting ASDS/SDS approval:", err));
}

/* ============================================
  Submit Unsign / Revert to Processing
============================================ */
function submitASDSSDSUnsign() {
  const form = document.getElementById("leave_form_asds_sds_update");
  const parentId = document.getElementById("form_id").value;

  const formData = new FormData(form);
  formData.append("parent_id", parentId);
  formData.append("asds_sds_esign", "processing");

  fetch("php/F6submit_asds_sds_update.php", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) return alert("Error: " + data.error);
      alert("Signature removed. Status reverted to processing.");
      setASDSSDSButtonState("processing");
      fetchASDSSDSUpdate(parentId);
    })
    .catch((err) => console.error("Error submitting ASDS/SDS unsign:", err));
}

/* ============================================
  Update button state UI
============================================ */
function setASDSSDSButtonState(state) {
  const btn = document.getElementById("asdsSdsApproveBtn");
  const eSign = document.getElementById("e-sign-input-top-management");
  if (state === "signed") {
    btn.innerText = "Unsigned";
    btn.style.background = "#ff5252";
    btn.setAttribute("data-state", "signed");
    eSign.style.display = "inline-block";
  } else {
    btn.innerText = "Approve & Sign";
    btn.style.background = "#2291ff";
    btn.setAttribute("data-state", "processing");
    eSign.style.display = "none";
  }
}

/* ============================================
  Fetch current ASDS/SDS status
============================================ */
function fetchASDSSDSUpdate(parentId) {
  fetch(`php/fetch_form6_signatories_updates.php?parent_id=${parentId}`)
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) return console.error(data.error);
      const status = (data.data.asds_sds_esign || "").toLowerCase().trim();
      const isSigned = status.includes("approved") || status.includes("signed");
      setASDSSDSButtonState(isSigned ? "signed" : "processing");
    })
    .catch((err) => console.error("Error fetching ASDS/SDS update:", err));
}

function setEsignForSDSorASDS(id) {
  fetch(`php/form6.php?id=${id}`)
    .then((response) => response.json())
    .then((data) => {
      if (!data.success) {
        console.error("Fetch error:", data.message);
        return;
      }

      const form = data.ticket;
      document.getElementById("form_id").value = form.id;

      const eSignImage = document.getElementById("top-management-esign");
      if (!eSignImage) return;

      const eSignName = form.name_of_official; // <-- adjust to actual DB field

      // Map names to signature images
      const eSignMap = {
        "CELEDONIO B. BALDERAS JR.": "sds_sampleSign.png",
        "SAN MARK ANABO MORCOSO": "sanmark_sampleSign.png",
        "HERBERT D. PEREZ": "asds_sampleSign.png",
      };

      const normalizedName = eSignName?.toUpperCase().trim();

      if (eSignMap[normalizedName]) {
        eSignImage.src = `php/uploads/e_signatures/authorized personnels/${eSignMap[normalizedName]}`;

        eSignImage.style.display = "inline-block";
      } else {
        console.warn("No e-signature found for:", eSignName);
        eSignImage.style.display = "none";
      }
    })
    .catch((err) => console.error("Network error:", err));
}

function fetchOtherDetails(parentId) {
  if (!parentId) {
    console.warn("fetchOtherDetails: missing parentId");
    return;
  }

  fetch(`php/fetch_form6_signatories_updates.php?parent_id=${parentId}`)
    .then((res) => {
      if (!res.ok) throw new Error("Network response was not ok");
      return res.json();
    })
    .then((result) => {
      if (!result.success) {
        console.error("Fetch error:", result.error);
        return;
      }

      const d = result.data || {};

      const daysWithPay = document.getElementById("daysWithPay");
      const daysWithoutPay = document.getElementById("daysWithoutPay");
      const approveForOthers = document.getElementById("approveForOthers");
      const disapprovedDueTo = document.getElementById("disapprovedDueTo");

      if (
        !daysWithPay ||
        !daysWithoutPay ||
        !approveForOthers ||
        !disapprovedDueTo
      ) {
        console.error("One or more input elements not found in DOM");
        return;
      }

      daysWithPay.value = d.approve_for_days_with_pay ?? "";
      daysWithoutPay.value = d.approve_for_days_without_pay ?? "";
      approveForOthers.value = d.approve_for_others ?? "";
      disapprovedDueTo.value = d.disapproved_due_to ?? "";

      console.log("Other details populated:", d);
    })
    .catch((err) => console.error("fetchOtherDetails failed:", err));
}

function showInfoModal(title, message) {
  const overlay = document.getElementById("info-overlay");
  if (!overlay) return console.error("info-overlay not found");

  const doneBtn = overlay.querySelector(".card-button.primary");
  const closeBtn = overlay.querySelector(".exit-button");

  overlay.querySelector(".card-heading").textContent = title;
  overlay.querySelector(".card-description").textContent = message;

  overlay.classList.add("show");

  const closeModal = () => {
    overlay.classList.remove("show");

    // ⏳ wait for fade-out animation, then redirect
    setTimeout(() => {
      window.location.reload();
    }, 300); // match your CSS animation duration
  };

  doneBtn.onclick = closeModal;
  closeBtn.onclick = closeModal;
}

async function handleCompleteAction(parentId, button) {
  if (!parentId) return console.error("Parent ID missing");

  try {
    // Fetch current signatures
    const res = await fetch(
      `php/fetch_form6_signatories_updates.php?parent_id=${parentId}`,
    );
    const data = await res.json();

    if (!data.success) {
      console.error("Failed to fetch signatures:", data.error);
      return;
    }

    // Normalize: trim, remove multiple spaces, lowercase
    function normalize(str) {
      return (str || "").replace(/\s+/g, " ").trim().toLowerCase();
    }

    const adminSign = normalize(data.data.admin_esign);
    const asdsSign = normalize(data.data.asds_sds_esign);

    let newStatus = "";

    if (adminSign === "approved & signed" && asdsSign === "approved & signed") {
      newStatus = "Approved";
    } else {
      newStatus = "Disapproved";
    }

    console.log(
      "Admin Sign:",
      adminSign,
      "ASDS/SDS Sign:",
      asdsSign,
      "New Status:",
      newStatus,
    );

    // Update status in database using form-urlencoded
    const formData = new FormData();
    formData.append("id", parentId);
    formData.append("status", newStatus);

    const updateRes = await fetch("php/form6_update_status.php", {
      method: "POST",
      body: formData,
    });

    const updateData = await updateRes.json();

    if (updateData.success) {
      // ✅ Show modal instead of alert
      showInfoModal(
        "Process Completed",
        newStatus === "Approved"
          ? "The request has been fully approved and completed."
          : "The request process is complete and was disapproved.",
      );

      button.textContent = "Print";
      button.onclick = () => printForm(parentId);
    } else {
      console.error("Failed to update status:", updateData.error);
    }
  } catch (err) {
    console.error("Error handling Complete action:", err);
  }
}

function printForm() {
  const printWindow = window.open("", "_blank", "width=900,height=1300");
  const formContainer = document.querySelector(".form-container");
  const headContent = document.querySelector("head").innerHTML;

  // ✅ Clone the form content into a temporary div so we can sync values
  const tempClone = formContainer.cloneNode(true);

  // 🔧 Sync all input/select/textarea values
  tempClone.querySelectorAll("input, select, textarea, img").forEach((el) => {
    if (el.tagName.toLowerCase() === "input") {
      if (el.type === "checkbox" || el.type === "radio") {
        if (el.checked) el.setAttribute("checked", "checked");
        else el.removeAttribute("checked");
      } else {
        el.setAttribute("value", el.value);
      }
    } else if (el.tagName.toLowerCase() === "textarea") {
      el.textContent = el.value;
    } else if (el.tagName.toLowerCase() === "select") {
      const selectedOption = el.options[el.selectedIndex];
      if (selectedOption) {
        el.querySelectorAll("option").forEach((opt) =>
          opt.removeAttribute("selected"),
        );
        selectedOption.setAttribute("selected", "selected");
      }
    } else if (el.tagName.toLowerCase() === "img") {
      if (el.src) el.setAttribute("src", el.src);
    }
  });

  // ✅ Use only the inner content of the cloned form (no border)
  const clonedContent = tempClone.innerHTML;

  const page2HTML = `
    <div class="page-break"></div>
      <div class="page-two">
        <div  style="margin-bottom: 0; border: 1px solid;">
          <p style="text-align:center; padding: 0px; margin: 5px 0; font-weight: 600;">INSTRUCTIONS AND REQUIREMENTS</p>
        </div>
        <div style="display: flex; margin-top: 0; font-size: 11px">
          <div style="width: 49%; margin-right: .5rem;">
            <p style="margin: 10px 0">
              Application for any type of leave shall <span style="font-weight: 600;border-bottom: 1px solid;">be made on this Form and to be
              accomplished at least in duplicate</span> with documentary requirements, as
              follows:
            </p> 

              <p style="font-weight: 600; margin-bottom: 5px">
              1. Vacation leave*
              </p>
              <p style="margin-bottom: 10px; margin-left: .8rem;">
              It shall be filed five (5) days in advance, whenever possible, of the
              effective date of such leave. Vacation leave within in the Philippines or
              abroad shall be indicated in the form for purposes of securing travel
              authority and completing clearance from money and work
              ccountabilities.
              </p>
              <p style="font-weight: 600; margin-bottom: 3px">
              2. Mandatory/Forced leave
              </p>
              <p style="margin-bottom: 10px; margin-left: .8rem;">
                Annual five-day vacation leave shall be forfeited if not taken during the
                year. In case the scheduled leave has been cancelled in the exigency
                of the service by the head of agency, it shall no longer be deducted from
                the accumulated vacation leave. Availment of one (1) day or more
                Vacation Leave (VL) shall be considered for complying the
                mandatory/forced leave subject to the conditions under Section 25, Rule
                XVI of the Omnibus Rules Implementing E.O. No. 292.
              </p>
              <p style="font-weight: 600; margin-bottom: 3px">
              3. Sick leave*
              </p>
              <p style="margin-bottom: 5px; margin-left: .8rem;">
                 It shall be filed immediately upon employee's return from such leave.
              </p>
              <p style="margin-bottom: 10px; margin-left: .8rem;">
                 If filed in advance or exceeding five (5) days, application shall be
                accompanied by a medical certificate. In case medical consultation
                was not availed of, an affidavit should be executed by an applicant.
              </p>
              <p style="font-weight: 600; margin-bottom: 3px">
              4. Maternity leave* – 105 days
              </p>
              <p style="margin-bottom: 5px; margin-left: .8rem;">
                 Proof of pregnancy e.g. ultrasound, doctor’s certificate on the
                expected date of delivery
              </p>
              <p style="margin-bottom: 5px; margin-left: .8rem;">
                 Accomplished Notice of Allocation of Maternity Leave Credits (CS
                Form No. 6a), if needed
              </p>
              <p style="margin-bottom: 10px; margin-left: .8rem;">
                 Seconded female employees shall enjoy maternity leave with full pay
                in the recipient agency.
              </p>  
              <p style="font-weight: 600; margin-bottom: 3px">
              5. Paternity leave – 7 days
              </p>
             <p style="margin-bottom: 10px; margin-left: .8rem;">
                Proof of child’s delivery e.g. birth certificate, medical certificate and
                marriage contract
              </p>
              <p style="font-weight: 600; margin-bottom: 3px">
              6. Special Privilege leave – 3 days</p>
              <p style="margin-bottom: 10px; margin-left: .8rem;">
                It shall be filed/approved for at least one (1) week prior to availment,
                except on emergency cases. Special privilege leave within the
                Philippines or abroad shall be indicated in the form for purposes of
                securing travel authority and completing clearance from money and work
                accountabilities.
              </p>  
              <p style="font-weight: 600; margin-bottom: 3px">
              7. Solo Parent leave – 7 days
              </p>
              <p style="margin-bottom: 10px; margin-left: .8rem;">
                It shall be filed in advance or whenever possible five (5) days before
                going on such leave with updated Solo Parent Identification Card.
              </p>  
              <p style="font-weight: 600; margin-bottom: 3px">
              8. Study leave* – up to 6 months
              </p>
              <p style="margin-bottom: 5px; margin-left: .8rem;">  
                 Shall meet the agency’s internal requirements, if any;
              </p>  
              <p style="margin-bottom: 10px; margin-left: .8rem;">
                 Contract between the agency head or authorized representative and
                the employee concerned.
              </p>  
              <p style="font-weight: 600; margin-bottom: 3px">
              9. VAWC leave – 10 days
              </p>
              <p style="margin-bottom: 5px; margin-left: .8rem;">
                 It shall be filed in advance or immediately upon the woman
                employee’s return from such leave.
              </p>
              <p style="margin-bottom: 5px; margin-left: .8rem;">
                 It shall be accompanied by any of the following supporting documents:
              </p>  
              <p style="margin-bottom: 5px; margin-left: 1.5rem;">
                a. Barangay Protection Order (BPO) obtained from the barangay;
              </p>  
              <p style="margin-bottom: 5px; margin-left: 1.5rem;">
                b. Temporary/Permanent Protection Order (TPO/PPO) obtained from
                the court;
              </p>
              <p style="margin-bottom: 10px; margin-left: 1.5rem;">
                c. If the protection order is not yet issued by the barangay or the court,
                a certification issued by the Punong Barangay/Kagawad or
                Prosecutor or the Clerk of Court that the application for the BPO,______________________
              </p>
          </div>

          <div style="width: 49%; margin-top: .8rem; ">
              <p style="margin-bottom: 10px; margin-left: 1.5rem; ">
              TPO or PPO has been filed with the said office shall be sufficient
              to support the application for the ten-day leave; or
              </p>
              <p style="margin-bottom: 10px; margin-left: 1.5rem;">
              d. In the absence of the BPO/TPO/PPO or the certification, a police
              report specifying the details of the occurrence of violence on the
              victim and a medical certificate may be considered, at the
              discretion of the immediate supervisor of the woman employee
              concerned. 
              </p>

              </p>
              <p style="font-weight: 600; margin-bottom: 3px">
              10. Rehabilitation leave* – up to 6 months
              </p>
              <p style="margin-bottom: 5px; margin-left: .8rem;">
                  Application shall be made within one (1) week from the time of the
                   accident except when a longer period is warranted.
              </p>
              <p style="margin-bottom: 5px; margin-left: .8rem;">
                  Letter request supported by relevant reports such as the police
                 report, if any,
              </p>
              <p style="margin-bottom: 5px; margin-left: .8rem;">
                 Medical certificate on the nature of the injuries, the course of
                treatment involved, and the need to undergo rest, recuperation, and
                rehabilitation, as the case may be
              </p>
              <p style="margin-bottom: 10px; margin-left: .8rem;">
                 Written concurrence of a government physician should be obtained
                relative to the recommendation for rehabilitation if the attending
                physician is a private practitioner, particularly on the duration of the
                period of rehabilitation.
              </p>
              <p style="font-weight: 600; margin-bottom: 3px">
              11. Special leave benefits for women* – up to 2 months
              </p>
              <p style="margin-bottom: 5px; margin-left: .8rem;">
                 The application may be filed in advance, that is, at least five (5) days
                prior to the scheduled date of the gynecological surgery that will be
                undergone by the employee. In case of emergency, the application
                for special leave shall be filed immediately upon employee’s return
                but during confinement the agency shall be notified of said surgery.
              </p>
              <p style="margin-bottom: 10px; margin-left: .8rem;">
                 The application shall be accompanied by a medical certificate filled
                out by the proper medical authorities, e.g. the attending surgeon
                accompanied by a clinical summary reflecting the gynecological
                disorder which shall be addressed or was addressed by the said
                surgery; the histopathological report; the operative technique used
                for the surgery; the duration of the surgery including the perioperative period 
                (period of confinement around surgery); as well as
                the employees estimated period of recuperation for the same.
              </p>
              <p style="font-weight: 600; margin-bottom: 3px">
              12. Special Emergency (Calamity) leave – up to 5 days
              </p>
             <p style="margin-bottom: 5px; margin-left: .8rem;">
                 The special emergency leave can be applied for a maximum of five
                (5) straight working days or staggered basis within thirty (30) days
                from the actual occurrence of the natural calamity/disaster. Said
                privilege shall be enjoyed once a year, not in every instance of
                calamity or disaster
              </p>
              <p style="margin-bottom: 10px; margin-left: .8rem;">
                 The head of office shall take full responsibility for the grant of special
                emergency leave and verification of the employee’s eligibility to be
                granted thereof. Said verification shall include: validation of place of
                residence based on latest available records of the affected
                employee; verification that the place of residence is covered in the
                declaration of calamity area by the proper government agency; and
                such other proofs as may be necessary.
              </p>

              <p style="font-weight: 600; margin-bottom: 3px">
               13. Monetization of leave credits</p>
              <p style="margin-bottom: 10px; margin-left: .8rem;">
                Application for monetization of fifty percent (50%) or more of the
                accumulated leave credits shall be accompanied by letter request to
                the head of the agency stating the valid and justifiable reasons.
              </p>  
              <p style="font-weight: 600; margin-bottom: 3px">
              7. Solo Parent leave – 7 days
              </p>
              <p style="margin-bottom: 10px; margin-left: .8rem;">
                It shall be filed in advance or whenever possible five (5) days before
                going on such leave with updated Solo Parent Identification Card.
              </p>  
              <p style="font-weight: 600; margin-bottom: 3px">
              14. Terminal leave*
              </p>
              <p style="margin-bottom: 5px; margin-left: .8rem;">  
                Proof of employee’s resignation or retirement or separation from the
                service. 
              </p> 
              <p style="font-weight: 600; margin-bottom: 3px">
              15. Adoption Leave
              </p>
              <p style="margin-bottom: 5px; margin-left: .8rem;">
                 Application for adoption leave shall be filed with an authenticated
                copy of the Pre-Adoptive Placement Authority issued by the
                Department of Social Welfare and Development (DSWD).
              </p>
          </div>
          
        </div>
        <div>
          <p style="text-align: justify; margin: 0; font-size: 10px">
          *For leave of absence for thirty (30) calendar days or more and terminal leave, application
          shall be accompanied by a clearance from money, property and
          work-related accountabilities (pursuant to CSC Memorandum Circular No. 2, s. 1985). </p>
        </div>  
     </div>
    <style>
    .page-two p{
    text-align: justify;
    margin: 0;
    }
    </style>
  `;

  // === Styles for printing ===
  const printStyles = `
  <style>
    @page {
      size: A4 portrait;
      margin: 0.5in;
    }
    body {
      width: 100% !important;
      margin: 0 !important;
      padding: 0 !important;
      background: white;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      font-family: "Arial", sans-serif !important;
    }
    .print-wrapper {
      width: 210mm; /* A4 width */
      min-height: 297mm; /* A4 height */
      padding: 0 !important;
      margin: 0 !important;
      box-sizing: border-box;
      background: white;
      font-size: 10pt;
      line-height: 1.2;
    }

    @media print {

      .input_sign_top_management input,
       {
        display: none !important;
        visibility: hidden !important;
      }

    .form6-tag { 
     margin-top: 0; 
     }
    
    .department-logos-01 { margin: 0; } 
    .department-logo { margin-left: 50px; }
    .stamp{ margin-right: 70px; } 
    .formgroup_b input[type="text"] { 
     width: 150px !important }
    .printing-form-group { 
      min-width: 150px; 
    }

       .page-break {
        page-break-before: always;
      }
      .page-two {
        margin-top: 15mm;
      }

    /* Hide unwanted UI elements */
    .admin-back-btn,
    .print-btn,
    .admin-header,
    .footer,
    .header,
    nav,
    header {
      display: none !important;
      visibility: hidden !important;
    }

    /* Remove borders/padding */
    .form-container,
    .main-container {
      border: none !important;
      box-shadow: none !important;
      margin: 0 !important;
      padding: 0 !important;
    }
  </style>
  `;

  // === Write content to print window ===
  printWindow.document.open();
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
      <head>
        <title>Print Form 6</title>
        ${headContent}
        ${printStyles}
      </head>
      <body>
        <div class="print-wrapper">
          ${clonedContent}
          ${page2HTML}
      </div>
        <script>
          // Wait for all images to load before printing
          const imgs = document.querySelectorAll('img');
          const promises = Array.from(imgs).map(img =>
            img.complete && img.naturalHeight !== 0 ? Promise.resolve() :
            new Promise(res => { img.onload = res; img.onerror = res; })
          );
          Promise.all(promises).then(() => {
            setTimeout(() => window.print(), 500);
          });
        <\/script>
      </body>
    </html>
  `);

  printWindow.document.close();
}
