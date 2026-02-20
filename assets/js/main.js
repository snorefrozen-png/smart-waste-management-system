/**
 * ============================================================
 * NAIROBI CITY COUNCIL SMART WASTE MANAGEMENT SYSTEM
 * Main JavaScript File
 * ============================================================
 * Handles: form validation, image map interactions, alert auto-dismiss
 */

document.addEventListener("DOMContentLoaded", function () {
  // ── Auto-dismiss alerts after 5 seconds ──────────────────
  const alerts = document.querySelectorAll(".alert-dismissible");
  alerts.forEach(function (alert) {
    setTimeout(function () {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
      bsAlert.close();
    }, 5000);
  });

  // ── Form Validation ──────────────────────────────────────
  const forms = document.querySelectorAll(".needs-validation");
  forms.forEach(function (form) {
    form.addEventListener("submit", function (event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add("was-validated");
    });
  });

  // ── Login Form Validation ────────────────────────────────
  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", function (event) {
      const email = document.getElementById("email");
      const password = document.getElementById("password");
      let valid = true;

      // Email validation
      if (!email.value || !email.value.includes("@")) {
        email.classList.add("is-invalid");
        valid = false;
      } else {
        email.classList.remove("is-invalid");
        email.classList.add("is-valid");
      }

      // Password validation
      if (!password.value || password.value.length < 6) {
        password.classList.add("is-invalid");
        valid = false;
      } else {
        password.classList.remove("is-invalid");
        password.classList.add("is-valid");
      }

      if (!valid) {
        event.preventDefault();
        event.stopPropagation();
      }
    });
  }

  // ── Report Form Validation ───────────────────────────────
  const reportForm = document.getElementById("reportForm");
  if (reportForm) {
    reportForm.addEventListener("submit", function (event) {
      const description = document.getElementById("description");
      const location = document.getElementById("location");
      let valid = true;

      if (!description.value || description.value.length < 10) {
        description.classList.add("is-invalid");
        valid = false;
      } else {
        description.classList.remove("is-invalid");
        description.classList.add("is-valid");
      }

      if (!location.value) {
        location.classList.add("is-invalid");
        valid = false;
      } else {
        location.classList.remove("is-invalid");
        location.classList.add("is-valid");
      }

      // Validate image type if uploaded
      const imageInput = document.getElementById("image");
      if (imageInput && imageInput.files.length > 0) {
        const allowedTypes = [
          "image/jpeg",
          "image/png",
          "image/gif",
          "image/webp",
        ];
        if (!allowedTypes.includes(imageInput.files[0].type)) {
          imageInput.classList.add("is-invalid");
          valid = false;
        } else {
          imageInput.classList.remove("is-invalid");
        }
      }

      if (!valid) {
        event.preventDefault();
        event.stopPropagation();
      }
    });
  }

  // ── Registration Form Validation ─────────────────────────
  const registerForm = document.getElementById("registerForm");
  if (registerForm) {
    registerForm.addEventListener("submit", function (event) {
      const name = document.getElementById("name");
      const email = document.getElementById("email");
      const password = document.getElementById("password");
      const confirmPassword = document.getElementById("confirm_password");
      let valid = true;

      if (!name.value || name.value.length < 2) {
        name.classList.add("is-invalid");
        valid = false;
      } else {
        name.classList.remove("is-invalid");
        name.classList.add("is-valid");
      }

      if (!email.value || !email.value.includes("@")) {
        email.classList.add("is-invalid");
        valid = false;
      } else {
        email.classList.remove("is-invalid");
        email.classList.add("is-valid");
      }

      if (!password.value || password.value.length < 6) {
        password.classList.add("is-invalid");
        valid = false;
      } else {
        password.classList.remove("is-invalid");
        password.classList.add("is-valid");
      }

      if (confirmPassword && password.value !== confirmPassword.value) {
        confirmPassword.classList.add("is-invalid");
        valid = false;
      } else if (confirmPassword) {
        confirmPassword.classList.remove("is-invalid");
        confirmPassword.classList.add("is-valid");
      }

      if (!valid) {
        event.preventDefault();
        event.stopPropagation();
      }
    });
  }

  // ── Image Map Interaction (Admin Dashboard) ──────────────
  // Handles clicks on image map <area> elements to filter reports
  const mapAreas = document.querySelectorAll("area[data-location]");
  mapAreas.forEach(function (area) {
    area.addEventListener("click", function (e) {
      e.preventDefault();
      const location = this.getAttribute("data-location");
      filterReportsByLocation(location);
    });
  });

  // ── Preview uploaded image ───────────────────────────────
  const imageInput = document.getElementById("image");
  if (imageInput) {
    imageInput.addEventListener("change", function () {
      const preview = document.getElementById("imagePreview");
      if (preview && this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
          preview.src = e.target.result;
          preview.style.display = "block";
        };
        reader.readAsDataURL(this.files[0]);
      }
    });
  }
});

/**
 * Filter reports table by location (used by Image Map feature)
 * @param {string} location - Location name to filter by
 */
function filterReportsByLocation(location) {
  // Show active filter indicator
  const filterInfo = document.getElementById("mapFilterInfo");
  if (filterInfo) {
    filterInfo.innerHTML =
      '<div class="alert alert-info alert-dismissible fade-in">' +
      '<i class="bi bi-funnel me-2"></i>Showing reports for: <strong>' +
      location +
      "</strong>" +
      '<button type="button" class="btn-close" onclick="clearLocationFilter()"></button></div>';
  }

  // Filter the table rows
  const tableRows = document.querySelectorAll("#reportsTable tbody tr");
  tableRows.forEach(function (row) {
    const locationCell = row.querySelector(".report-location");
    if (locationCell) {
      const rowLocation = locationCell.textContent.trim().toLowerCase();
      if (rowLocation.includes(location.toLowerCase())) {
        row.style.display = "";
      } else {
        row.style.display = "none";
      }
    }
  });

  // Scroll to reports table
  const table = document.getElementById("reportsTable");
  if (table) {
    table.scrollIntoView({ behavior: "smooth", block: "start" });
  }
}

/**
 * Clear the location filter and show all reports
 */
function clearLocationFilter() {
  const tableRows = document.querySelectorAll("#reportsTable tbody tr");
  tableRows.forEach(function (row) {
    row.style.display = "";
  });
  const filterInfo = document.getElementById("mapFilterInfo");
  if (filterInfo) {
    filterInfo.innerHTML = "";
  }
}
