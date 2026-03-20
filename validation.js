// validation.js - client-side checkout form validation

window.addEventListener("DOMContentLoaded", function () {
  var form = document.getElementById("checkoutForm");
  if (!form) return;

  var inputs = {
    fullName: form.querySelector('input[name="full_name"]'),
    address: form.querySelector('textarea[name="address"]'),
    cardName: form.querySelector('input[name="card_name"]'),
    cardNumber: form.querySelector('input[name="card_number"]'),
    expiry: form.querySelector('input[name="expiry"]'),
    cvv: form.querySelector('input[name="cvv"]')
  };

  // Create error elements dynamically if they don't exist
  Object.values(inputs).forEach(function(input) {
      if (input && !input.nextElementSibling?.classList.contains('error-message')) {
          var err = document.createElement("small");
          err.className = "error-message";
          err.style.color = "var(--danger-color)";
          err.style.display = "block";
          err.style.marginTop = "4px";
          input.parentNode.insertBefore(err, input.nextSibling);
      }
  });

  function showError(input, message) {
      input.classList.add("error");
      input.classList.remove("success");
      input.nextElementSibling.textContent = message;
      return false;
  }

  function showSuccess(input) {
      input.classList.remove("error");
      input.classList.add("success");
      input.nextElementSibling.textContent = "";
      return true;
  }

  function validate() {
      var isValid = true;

      // Full Name
      if (inputs.fullName.value.trim().length < 3) {
          showError(inputs.fullName, "Name must be at least 3 characters.");
          isValid = false;
      } else {
          showSuccess(inputs.fullName);
      }

      // Address
      if (inputs.address.value.trim().length < 10) {
          showError(inputs.address, "Please enter a complete delivery address.");
          isValid = false;
      } else {
          showSuccess(inputs.address);
      }

      // Card Name
      if (inputs.cardName.value.trim().length < 3) {
          showError(inputs.cardName, "Name on card is required.");
          isValid = false;
      } else {
          showSuccess(inputs.cardName);
      }

      // Card Number
      var cardVal = inputs.cardNumber.value.replace(/\D/g, '');
      if (cardVal.length !== 16) {
          showError(inputs.cardNumber, "Card number must be 16 digits.");
          isValid = false;
      } else {
          showSuccess(inputs.cardNumber);
      }

      // Expiry
      var expMatch = inputs.expiry.value.match(/^(0[1-9]|1[0-2])\/?([0-9]{2})$/);
      if (!expMatch) {
          showError(inputs.expiry, "Invalid expiry format (MM/YY).");
          isValid = false;
      } else {
          showSuccess(inputs.expiry);
      }

      // CVV
      var cvvVal = inputs.cvv.value.replace(/\D/g, '');
      if (cvvVal.length < 3 || cvvVal.length > 4) {
          showError(inputs.cvv, "CVV must be 3 or 4 numbers.");
          isValid = false;
      } else {
          showSuccess(inputs.cvv);
      }

      return isValid;
  }

  // Format Card Number automatically
  inputs.cardNumber.addEventListener("input", function(e) {
      var v = e.target.value.replace(/\D/g, '');
      var formatted = v.match(/.{1,4}/g);
      if (formatted) {
          e.target.value = formatted.join('-');
      }
  });

  // Format Expiry automatically
  inputs.expiry.addEventListener("input", function(e) {
      var v = e.target.value.replace(/\D/g, '');
      if (v.length >= 2) {
          e.target.value = v.substring(0,2) + '/' + v.substring(2,4);
      } else {
          e.target.value = v;
      }
  });

  form.addEventListener("submit", function (e) {
    if (!validate()) {
        e.preventDefault();
    } else {
        // Change button text to show processing
        var btn = form.querySelector('button[type="submit"]');
        if (btn) {
            btn.textContent = "Processing Payment...";
            btn.style.opacity = "0.7";
            // Disable button slightly after form submit to prevent double-click
            setTimeout(() => btn.disabled = true, 50);
        }
    }
  });
});
