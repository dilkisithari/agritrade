document.addEventListener("DOMContentLoaded", () => {
  // Mobile menu toggle
  const mobileMenuToggle = document.querySelector(".mobile-menu-toggle")
  const nav = document.querySelector("nav ul")

  if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener("click", () => {
      nav.classList.toggle("show")
    })
  }

  // Form validation
  const forms = document.querySelectorAll("form")

  forms.forEach((form) => {
    form.addEventListener("submit", (event) => {
      const requiredFields = form.querySelectorAll("[required]")
      let isValid = true

      requiredFields.forEach((field) => {
        if (!field.value.trim()) {
          isValid = false

          // Create or update error message
          let errorMessage = field.nextElementSibling
          if (!errorMessage || !errorMessage.classList.contains("error")) {
            errorMessage = document.createElement("div")
            errorMessage.classList.add("error")
            field.parentNode.insertBefore(errorMessage, field.nextSibling)
          }

          errorMessage.textContent = "This field is required"

          // Add error styling
          field.style.borderColor = "#f44336"
        } else {
          // Remove error styling if field is valid
          field.style.borderColor = ""

          // Remove error message if it exists
          const errorMessage = field.nextElementSibling
          if (errorMessage && errorMessage.classList.contains("error")) {
            errorMessage.remove()
          }
        }
      })

      if (!isValid) {
        event.preventDefault()
      }
    })
  })

  // Password confirmation validation
  const passwordConfirmFields = document.querySelectorAll('input[name="confirm_password"]')

  passwordConfirmFields.forEach((field) => {
    field.addEventListener("input", function () {
      const passwordField = this.form.querySelector('input[name="password"]')

      if (passwordField.value !== this.value) {
        // Create or update error message
        let errorMessage = this.nextElementSibling
        if (!errorMessage || !errorMessage.classList.contains("error")) {
          errorMessage = document.createElement("div")
          errorMessage.classList.add("error")
          this.parentNode.insertBefore(errorMessage, this.nextSibling)
        }

        errorMessage.textContent = "Passwords do not match"

        // Add error styling
        this.style.borderColor = "#f44336"
      } else {
        // Remove error styling
        this.style.borderColor = ""

        // Remove error message if it exists
        const errorMessage = this.nextElementSibling
        if (errorMessage && errorMessage.classList.contains("error")) {
          errorMessage.remove()
        }
      }
    })
  })

  // Notification system
  const notificationBell = document.querySelector(".notification-bell")

  if (notificationBell) {
    notificationBell.addEventListener("click", () => {
      const notificationDropdown = document.querySelector(".notification-dropdown")
      notificationDropdown.classList.toggle("show")

      // Mark notifications as read when opened
      if (notificationDropdown.classList.contains("show")) {
        const unreadNotifications = document.querySelectorAll(".notification.unread")

        unreadNotifications.forEach((notification) => {
          const notificationId = notification.dataset.id

          // Send AJAX request to mark notification as read
          const xhr = new XMLHttpRequest()
          xhr.open("POST", "mark_notification_read.php", true)
          xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
          xhr.send("notification_id=" + notificationId)

          // Update UI
          notification.classList.remove("unread")
        })

        // Update notification count
        const notificationCount = document.querySelector(".notification-count")
        if (notificationCount) {
          notificationCount.textContent = "0"
          notificationCount.style.display = "none"
        }
      }
    })
  }

  // Close dropdowns when clicking outside
  document.addEventListener("click", (event) => {
    // Close notification dropdown
    const notificationDropdown = document.querySelector(".notification-dropdown")
    const notificationBell = document.querySelector(".notification-bell")

    if (notificationDropdown && notificationBell) {
      if (!notificationBell.contains(event.target) && !notificationDropdown.contains(event.target)) {
        notificationDropdown.classList.remove("show")
      }
    }

    // Close mobile menu
    if (nav && mobileMenuToggle) {
      if (!nav.contains(event.target) && !mobileMenuToggle.contains(event.target)) {
        nav.classList.remove("show")
      }
    }
  })

  // Image preview for file uploads
  const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]')

  imageInputs.forEach((input) => {
    input.addEventListener("change", function () {
      const previewContainer = document.querySelector(".image-preview")

      if (previewContainer) {
        previewContainer.innerHTML = ""

        if (this.files && this.files[0]) {
          const reader = new FileReader()

          reader.onload = (e) => {
            const img = document.createElement("img")
            img.src = e.target.result
            img.classList.add("preview-image")
            previewContainer.appendChild(img)
          }

          reader.readAsDataURL(this.files[0])
        }
      }
    })
  })
})
