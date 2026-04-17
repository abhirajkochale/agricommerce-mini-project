/**
 * AgroConnect - Unified Client-side Validation
 * Handles Checkout, Login, Registration, and Feedback
 */

window.addEventListener("DOMContentLoaded", function () {
    
    // --- Utility Functions ---
    function showError(input, message) {
        input.classList.add("error");
        input.classList.remove("success");
        let errorElement = input.parentNode.querySelector('.error-message');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
        return false;
    }

    function showSuccess(input) {
        input.classList.remove("error");
        input.classList.add("success");
        let errorElement = input.parentNode.querySelector('.error-message');
        if (errorElement) {
            errorElement.textContent = "";
            errorElement.style.display = 'none';
        }
        return true;
    }

    function setBtnLoading(btn, text) {
        if (!btn) return;
        const originalText = btn.innerHTML;
        btn.setAttribute('data-original-text', originalText);
        btn.disabled = true;
        btn.style.opacity = "0.7";
        btn.innerHTML = `<span class="spinner"></span> ${text}`;
    }

    // --- 1. Checkout Form Validation ---
    const checkoutForm = document.getElementById("checkoutForm");
    if (checkoutForm) {
        const checkoutInputs = {
            fullName: checkoutForm.querySelector('input[name="full_name"]'),
            address: checkoutForm.querySelector('textarea[name="address"]'),
            cardNumber: checkoutForm.querySelector('input[name="card_number"]'),
            expiry: checkoutForm.querySelector('input[name="expiry"]'),
            cvv: checkoutForm.querySelector('input[name="cvv"]')
        };

        checkoutForm.addEventListener("submit", function (e) {
            let isValid = true;

            // Name Validation
            if (checkoutInputs.fullName.value.trim().length < 3) {
                showError(checkoutInputs.fullName, "Name must be at least 3 characters.");
                isValid = false;
            } else showSuccess(checkoutInputs.fullName);

            // Address Validation
            if (checkoutInputs.address.value.trim().length < 10) {
                showError(checkoutInputs.address, "Please enter a complete delivery address.");
                isValid = false;
            } else showSuccess(checkoutInputs.address);

            // Card Number Validation (16 digits)
            const cleanCard = checkoutInputs.cardNumber.value.replace(/\D/g, '');
            if (cleanCard.length !== 16) {
                showError(checkoutInputs.cardNumber, "Card number must be 16 digits.");
                isValid = false;
            } else showSuccess(checkoutInputs.cardNumber);

            // Expiry Validation (MM/YY)
            const expiryRegex = /^(0[1-9]|1[0-2])\/([0-9]{2})$/;
            if (!expiryRegex.test(checkoutInputs.expiry.value)) {
                showError(checkoutInputs.expiry, "Use MM/YY format (e.g. 12/25).");
                isValid = false;
            } else {
                // Future date check (simple version)
                const parts = checkoutInputs.expiry.value.split('/');
                const month = parseInt(parts[0], 10);
                const year = parseInt("20" + parts[1], 10);
                const now = new Date();
                const expDate = new Date(year, month - 1, 1);
                
                if (expDate < new Date(now.getFullYear(), now.getMonth(), 1)) {
                    showError(checkoutInputs.expiry, "Card has expired.");
                    isValid = false;
                } else showSuccess(checkoutInputs.expiry);
            }

            // CVV Validation (3-4 digits)
            const cleanCVV = checkoutInputs.cvv.value.replace(/\D/g, '');
            if (cleanCVV.length < 3 || cleanCVV.length > 4) {
                showError(checkoutInputs.cvv, "CVV must be 3 or 4 digits.");
                isValid = false;
            } else showSuccess(checkoutInputs.cvv);

            if (!isValid) {
                e.preventDefault();
            } else {
                setBtnLoading(checkoutForm.querySelector('button[type="submit"]'), "Processing Payment...");
            }
        });

        // Dynamic formatting for Card Number
        if (checkoutInputs.cardNumber) {
            checkoutInputs.cardNumber.addEventListener("input", (e) => {
                let v = e.target.value.replace(/\D/g, '').match(/.{1,4}/g);
                e.target.value = v ? v.join('-') : '';
            });
        }

        // Dynamic formatting for Expiry
        if (checkoutInputs.expiry) {
            checkoutInputs.expiry.addEventListener("input", (e) => {
                let v = e.target.value.replace(/\D/g, '');
                if (v.length >= 2) {
                    e.target.value = v.substring(0, 2) + '/' + v.substring(2, 4);
                }
            });
        }
    }

    // --- 2. Login Form Validation ---
    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {
            setBtnLoading(loginForm.querySelector('button[type="submit"]'), "Verifying...");
        });
    }

    // --- 3. Registration Form Validation ---
    const registerForm = document.getElementById("registerForm");
    if (registerForm) {
        const regInputs = {
            name: registerForm.querySelector('#name'),
            email: registerForm.querySelector('#email'),
            password: registerForm.querySelector('#password'),
            confirm: registerForm.querySelector('#confirm_password')
        };

        registerForm.addEventListener("submit", function (e) {
            let isValid = true;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (regInputs.name.value.trim().length < 3) {
                showError(regInputs.name, "Name must be at least 3 characters.");
                isValid = false;
            } else showSuccess(regInputs.name);

            if (!emailRegex.test(regInputs.email.value.trim())) {
                showError(regInputs.email, "Invalid email format.");
                isValid = false;
            } else showSuccess(regInputs.email);

            const password = regInputs.password.value;
            const hasCapital = /[A-Z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);

            if (password.length < 7) {
                showError(regInputs.password, "Minimum 7 characters required.");
                isValid = false;
            } else if (!hasCapital || !hasNumber || !hasSpecial) {
                showError(regInputs.password, "Include a capital letter, number, and special character.");
                isValid = false;
            } else {
                showSuccess(regInputs.password);
            }

            if (regInputs.password.value !== regInputs.confirm.value) {
                showError(regInputs.confirm, "Passwords do not match.");
                isValid = false;
            } else showSuccess(regInputs.confirm);

            if (!isValid) {
                e.preventDefault();
            } else {
                setBtnLoading(registerForm.querySelector('button[type="submit"]'), "Creating Account...");
            }
        });
    }

    // --- 4. Feedback Form Handling ---
    const staticFeedbackForm = document.getElementById("staticFeedbackForm");
    if (staticFeedbackForm) {
        staticFeedbackForm.addEventListener("submit", function (e) {
            setBtnLoading(staticFeedbackForm.querySelector('#staticFeedbackSubmit'), "Submitting...");
        });
    }

    const modalFeedbackForm = document.getElementById("modalFeedbackForm");
    if (modalFeedbackForm) {
        modalFeedbackForm.addEventListener("submit", function (e) {
            setBtnLoading(modalFeedbackForm.querySelector('#modalFeedbackSubmit'), "Sending...");
        });
    }
});
