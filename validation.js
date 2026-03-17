// validation.js - client-side checkout form validation

window.addEventListener("DOMContentLoaded", function () {
  var form = document.getElementById("checkoutForm");
  if (!form) return;

  var inputs = {
    fullName: document.getElementById("fullName"),
    email: document.getElementById("email"),
    phone: document.getElementById("phone"),
    city: document.getElementById("city"),
    address: document.getElementById("address"),
    productName: document.getElementById("productName"),
    productCategory: document.getElementById("productCategory"),
    quantity: document.getElementById("quantity")
  };

  var placeOrderBtn = document.getElementById("placeOrderBtn");
  var totalPriceDisplay = document.getElementById("totalPriceDisplay");

  function setStatus(input, errorId, message) {
    var errorElement = document.getElementById(errorId);
    if (!errorElement) return false;

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
    var val = inputs.fullName.value.trim();
    if (!val) return setStatus(inputs.fullName, "fullNameError", "Name is required.");
    if (val.length < 3) return setStatus(inputs.fullName, "fullNameError", "Min 3 characters.");
    return setStatus(inputs.fullName, "fullNameError", "");
  }

  function validateEmail() {
    var val = inputs.email.value.trim();
    if (!val) return setStatus(inputs.email, "emailError", "Email is required.");
    var pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!pattern.test(val)) return setStatus(inputs.email, "emailError", "Invalid email format.");
    return setStatus(inputs.email, "emailError", "");
  }

  function validatePhone() {
    var val = inputs.phone.value.trim();
    if (!val) return setStatus(inputs.phone, "phoneError", "Phone is required.");
    if (val.replace(/\D/g, "").length !== 10) return setStatus(inputs.phone, "phoneError", "Need 10 digits.");
    return setStatus(inputs.phone, "phoneError", "");
  }

  function validateProduct() {
      var nameVal = inputs.productName.value.trim();
      var catVal = inputs.productCategory.value;
      
      var nameOk = setStatus(inputs.productName, "productNameError", nameVal ? "" : "Required.");
      var catOk = setStatus(inputs.productCategory, "productCategoryError", catVal ? "" : "Required.");
      
      return nameOk && catOk;
  }

  function validateQuantity() {
    var val = inputs.quantity.value;
    if (!val || Number(val) <= 0) return setStatus(inputs.quantity, "quantityError", "Invalid qty.");
    return setStatus(inputs.quantity, "quantityError", "");
  }

  function updatePrice() {
    var qty = Number(inputs.quantity.value);
    if (qty > 0) {
      var result = window.calculateTotalPrice(40, qty); // Using base price 40
      totalPriceDisplay.innerHTML = `<strong>Total Price:</strong> Rs. ${result.finalTotal} <span style="font-size: 0.8rem; color: var(--success-color);">(${result.discountPercentage}% Discount)</span>`;
    } else {
      totalPriceDisplay.textContent = "Total: Rs. 0";
    }
  }

  function checkForm() {
    var v1 = validateFullName();
    var v2 = validateEmail();
    var v3 = validatePhone();
    var v4 = validateProduct();
    var v5 = validateQuantity();
    
    placeOrderBtn.disabled = !(v1 && v2 && v3 && v4 && v5);
  }

  // Listeners
  Object.keys(inputs).forEach(key => {
    inputs[key].addEventListener("input", function() {
        if (key === 'quantity') updatePrice();
        checkForm();
    });
  });

  form.addEventListener("submit", function (e) {
    e.preventDefault();
    alert("✅ Success! Your order has been placed successfully (Demo Visualization).");
    form.reset();
    totalPriceDisplay.textContent = "Total: Rs. 0";
    placeOrderBtn.disabled = true;
    
    // Clear success classes
    Object.keys(inputs).forEach(key => inputs[key].classList.remove("success"));
  });

  // Initial
  updatePrice();
});
