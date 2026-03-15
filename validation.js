// validation.js - client-side checkout form validation

window.addEventListener("DOMContentLoaded", function () {
  var form = document.getElementById("checkoutForm");
  if (!form) return; // only on product.html

  var fullName = document.getElementById("fullName");
  var email = document.getElementById("email");
  var phone = document.getElementById("phone");
  var password = document.getElementById("password");
  var confirmPassword = document.getElementById("confirmPassword");
  var address = document.getElementById("address");
  var city = document.getElementById("city");
  var zip = document.getElementById("zip");
  var productCategory = document.getElementById("productCategory");
  var productName = document.getElementById("productName");
  var quantity = document.getElementById("quantity");
  var cardNumber = document.getElementById("cardNumber");
  var expiry = document.getElementById("expiry");
  var cvv = document.getElementById("cvv");
  var placeOrderBtn = document.getElementById("placeOrderBtn");
  var totalPriceDisplay = document.getElementById("totalPriceDisplay");

  function setStatus(input, errorId, message) {
    var errorElement = document.getElementById(errorId);
    if (message) {
      input.classList.remove("success");
      input.classList.add("error");
      errorElement.textContent = message;
      return false;
    } else {
      input.classList.remove("error");
      input.classList.add("success");
      errorElement.textContent = "";
      return true;
    }
  }

  function validateFullName() {
    var value = fullName.value.trim();
    if (!value) return setStatus(fullName, "fullNameError", "Full name is required.");
    if (value.length < 3)
      return setStatus(fullName, "fullNameError", "Please enter at least 3 characters.");
    return setStatus(fullName, "fullNameError", "");
  }

  function validateEmail() {
    var value = email.value.trim();
    if (!value) return setStatus(email, "emailError", "Email is required.");
    var pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!pattern.test(value))
      return setStatus(email, "emailError", "Please enter a valid email address.");
    return setStatus(email, "emailError", "");
  }

  function validatePhone() {
    var value = phone.value.trim();
    if (!value) return setStatus(phone, "phoneError", "Phone number is required.");
    var digits = value.replace(/\D/g, "");
    if (digits.length !== 10)
      return setStatus(phone, "phoneError", "Enter a 10-digit phone number.");
    return setStatus(phone, "phoneError", "");
  }

  function validatePassword() {
    var value = password.value;
    if (!value) return setStatus(password, "passwordError", "Password is required.");
    // At least 8 characters, one uppercase, one lowercase, one digit, one special char
    var strongPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/;
    if (!strongPattern.test(value)) {
      return setStatus(
        password,
        "passwordError",
        "Password must be 8+ chars with upper, lower, number, and special character."
      );
    }
    return setStatus(password, "passwordError", "");
  }

  function validateConfirmPassword() {
    if (!confirmPassword.value)
      return setStatus(confirmPassword, "confirmPasswordError", "Please confirm password.");
    if (confirmPassword.value !== password.value)
      return setStatus(confirmPassword, "confirmPasswordError", "Passwords do not match.");
    return setStatus(confirmPassword, "confirmPasswordError", "");
  }

  function validateAddress() {
    var value = address.value.trim();
    if (!value) return setStatus(address, "addressError", "Shipping address is required.");
    return setStatus(address, "addressError", "");
  }

  function validateCity() {
    var value = city.value.trim();
    if (!value) return setStatus(city, "cityError", "City is required.");
    return setStatus(city, "cityError", "");
  }

  function validateZip() {
    var value = zip.value.trim();
    if (!value) return setStatus(zip, "zipError", "Zip code is required.");
    var pattern = /^\d{5,6}$/;
    if (!pattern.test(value))
      return setStatus(zip, "zipError", "Zip code must be 5 or 6 digits.");
    return setStatus(zip, "zipError", "");
  }

  function validateProductCategory() {
    var value = productCategory.value;
    if (!value) return setStatus(productCategory, "productCategoryError", "Select a category.");
    return setStatus(productCategory, "productCategoryError", "");
  }

  function validateProductName() {
    var value = productName.value.trim();
    if (!value) return setStatus(productName, "productNameError", "Product name is required.");
    return setStatus(productName, "productNameError", "");
  }

  function validateQuantity() {
    var value = quantity.value;
    if (!value) return setStatus(quantity, "quantityError", "Quantity is required.");
    var num = Number(value);
    if (isNaN(num) || num <= 0)
      return setStatus(quantity, "quantityError", "Quantity must be greater than zero.");
    return setStatus(quantity, "quantityError", "");
  }

  function validateCardNumber() {
    var value = cardNumber.value.replace(/\s+/g, "");
    if (!value) return setStatus(cardNumber, "cardNumberError", "Card number is required.");
    var pattern = /^\d{16}$/;
    if (!pattern.test(value))
      return setStatus(cardNumber, "cardNumberError", "Enter a 16-digit card number.");
    return setStatus(cardNumber, "cardNumberError", "");
  }

  function validateExpiry() {
    var value = expiry.value;
    if (!value) return setStatus(expiry, "expiryError", "Expiry date is required.");

    var today = new Date();
    var selectedDate = new Date(value + "-01");
    if (selectedDate <= today)
      return setStatus(expiry, "expiryError", "Expiry date must be in the future.");
    return setStatus(expiry, "expiryError", "");
  }

  function validateCVV() {
    var value = cvv.value.trim();
    if (!value) return setStatus(cvv, "cvvError", "CVV is required.");
    var pattern = /^\d{3,4}$/;
    if (!pattern.test(value))
      return setStatus(cvv, "cvvError", "CVV must be 3 or 4 digits.");
    return setStatus(cvv, "cvvError", "");
  }

  function updateTotalPrice() {
    var qty = Number(quantity.value);
    if (!qty || qty <= 0) {
      totalPriceDisplay.textContent = "Rs. 0";
      return;
    }

    // Use first product price as reference (demo) or 30 if unavailable
    var unitPrice = 30;
    if (typeof window.calculateTotalPrice === "function") {
      var result = window.calculateTotalPrice(unitPrice, qty);
      totalPriceDisplay.textContent =
        "Base: Rs. " +
        result.baseTotal +
        " | Discount: " +
        result.discountPercentage +
        "% | Final: Rs. " +
        result.finalTotal;
    } else {
      totalPriceDisplay.textContent = "Rs. " + unitPrice * qty;
    }
  }

  function checkFormValidity() {
    var v1 = validateFullName();
    var v2 = validateEmail();
    var v3 = validatePhone();
    var v4 = validatePassword();
    var v5 = validateConfirmPassword();
    var v6 = validateAddress();
    var v7 = validateCity();
    var v8 = validateZip();
    var v9 = validateProductCategory();
    var v10 = validateProductName();
    var v11 = validateQuantity();
    var v12 = validateCardNumber();
    var v13 = validateExpiry();
    var v14 = validateCVV();

    var allValid =
      v1 &&
      v2 &&
      v3 &&
      v4 &&
      v5 &&
      v6 &&
      v7 &&
      v8 &&
      v9 &&
      v10 &&
      v11 &&
      v12 &&
      v13 &&
      v14;

    placeOrderBtn.disabled = !allValid;
  }

  // Real-time validation events
  fullName.addEventListener("input", function () {
    validateFullName();
    checkFormValidity();
  });
  email.addEventListener("input", function () {
    validateEmail();
    checkFormValidity();
  });
  phone.addEventListener("input", function () {
    validatePhone();
    checkFormValidity();
  });
  password.addEventListener("input", function () {
    validatePassword();
    validateConfirmPassword();
    checkFormValidity();
  });
  confirmPassword.addEventListener("input", function () {
    validateConfirmPassword();
    checkFormValidity();
  });
  address.addEventListener("input", function () {
    validateAddress();
    checkFormValidity();
  });
  city.addEventListener("input", function () {
    validateCity();
    checkFormValidity();
  });
  zip.addEventListener("input", function () {
    validateZip();
    checkFormValidity();
  });
  productCategory.addEventListener("change", function () {
    validateProductCategory();
    checkFormValidity();
  });
  productName.addEventListener("input", function () {
    validateProductName();
    checkFormValidity();
  });
  quantity.addEventListener("input", function () {
    validateQuantity();
    updateTotalPrice();
    checkFormValidity();
  });
  cardNumber.addEventListener("input", function () {
    // simple spacing formatting every 4 digits (optional)
    var digits = cardNumber.value.replace(/\D/g, "");
    var formatted = digits.replace(/(.{4})/g, "$1 ").trim();
    cardNumber.value = formatted;
    validateCardNumber();
    checkFormValidity();
  });
  expiry.addEventListener("change", function () {
    validateExpiry();
    checkFormValidity();
  });
  cvv.addEventListener("input", function () {
    validateCVV();
    checkFormValidity();
  });

  // Final submit handler
  form.addEventListener("submit", function (e) {
    e.preventDefault();
    checkFormValidity();
    if (placeOrderBtn.disabled) return;

    alert(
      "Order placed successfully! This is a front-end validation demo only; no data is stored in the database."
    );
    form.reset();
    totalPriceDisplay.textContent = "Rs. 0";
    placeOrderBtn.disabled = true;
  });

  // Initial state
  placeOrderBtn.disabled = true;
  updateTotalPrice();
});
