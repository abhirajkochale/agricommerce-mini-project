// script.js - JavaScript concepts and interactions

// Sample product data
var productNames = ["Organic Wheat", "Basmati Rice", "Hybrid Tomato", "Mountain Potato", "Alphonso Mango"];
var productCategories = ["Grains", "Grains", "Vegetables", "Vegetables", "Fruits"];
var productPrices = [28, 95, 20, 22, 120];
var productAvailability = [true, true, true, true, true];

var cart = [];
var cartCountSpan;

// DOMContentLoaded to ensure elements exist
window.addEventListener("DOMContentLoaded", function () {
  cartCountSpan = document.getElementById("cartCount");

  initThemeToggle();
  initProductsPage();
  initRegistrationDemo();

  // Handle auto-triggering feedback modal after order
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('success') === 'order_placed') {
      setTimeout(openFeedbackModal, 500); // Small delay for better UX
  }
});

// Sticky header scroll effect
window.addEventListener("scroll", function() {
  const header = document.querySelector(".main-header");
  if (header) {
      if (window.scrollY > 50) {
          header.classList.add("scrolled");
      } else {
          header.classList.remove("scrolled");
      }
  }
});

/**
 * Modal System Control
 */
/**
 * Modal System Control
 */
function openFeedbackModal() {
    const modal = document.getElementById('feedbackModal');
    if (modal) {
        modal.style.display = 'flex';
        // Force a reflow before adding the 'show' class for the transition
        modal.offsetHeight; 
        modal.classList.add('show');
        document.body.style.overflow = 'hidden'; // Prevent scrolling
    }
}

function closeFeedbackModal() {
    const modal = document.getElementById('feedbackModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = ''; // Restore scrolling
        // Wait for transition to finish before hiding completely
        setTimeout(() => {
            if (!modal.classList.contains('show')) {
                modal.style.display = 'none';
            }
        }, 300);
    }
}

// Close modal when clicking outside the content
window.addEventListener('click', function(e) {
    const modal = document.getElementById('feedbackModal');
    if (e.target === modal) {
        closeFeedbackModal();
    }
});


// Theme toggle (light/dark mode)
function initThemeToggle() {
  var toggleBtn = document.getElementById("themeToggle");
  if (!toggleBtn) return;

  toggleBtn.addEventListener("click", function () {
    document.body.classList.toggle("dark-theme");
    var icon = toggleBtn.querySelector(".icon");
    if (document.body.classList.contains("dark-theme")) {
      if (icon) icon.textContent = "☀️";
      localStorage.setItem("theme", "dark");
    } else {
      if (icon) icon.textContent = "🌙";
      localStorage.setItem("theme", "light");
    }
  });

  // Load saved theme
  if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark-theme");
    var icon = toggleBtn.querySelector(".icon");
    if (icon) icon.textContent = "☀️";
  }
}

// Initialize products page: wire search/filter and Add to Cart
function initProductsPage() {
  var grid = document.getElementById("productGrid");
  if (!grid) return;

  // Search/filter/sort products
  var searchInput = document.getElementById("searchInput");
  var categoryFilter = document.getElementById("categoryFilter");
  var sortFilter = document.getElementById("sortFilter");

  function applyFilters() {
    var searchTerm = searchInput ? searchInput.value.toLowerCase() : "";
    var selectedCategory = categoryFilter ? categoryFilter.value : "all";
    var sortValue = sortFilter ? sortFilter.value : "default";

    var cards = Array.from(grid.getElementsByClassName("product-card"));

    // Filter
    cards.forEach(function (c) {
      // name and category are stored in data attributes in the HTML
      var name = c.dataset.name.toLowerCase();
      var category = c.dataset.category;

      var matchesSearch = name.indexOf(searchTerm) !== -1;
      var matchesCategory = selectedCategory === "all" || category === selectedCategory;

      if (matchesSearch && matchesCategory) {
        c.style.display = "block";
      } else {
        c.style.display = "none";
      }
    });

    // Sort visible cards
    var visibleCards = cards.filter(c => c.style.display !== "none");
    if (sortValue !== "default") {
      visibleCards.sort(function (a, b) {
        if (sortValue === "price_asc" || sortValue === "price_desc") {
          var pA = parseFloat(a.dataset.price);
          var pB = parseFloat(b.dataset.price);
          return sortValue === "price_asc" ? pA - pB : pB - pA;
        } else if (sortValue === "name_asc") {
          var nA = a.dataset.name;
          var nB = b.dataset.name;
          return nA.localeCompare(nB);
        }
        return 0;
      });
    }

    // Re-append to grid to change visual order
    cards.forEach(c => c.remove());
    visibleCards.forEach(c => grid.appendChild(c));
    // append hidden ones at the end
    var hiddenCards = cards.filter(c => c.style.display === "none");
    hiddenCards.forEach(c => grid.appendChild(c));
  }

  if (searchInput) searchInput.addEventListener("input", applyFilters);
  if (categoryFilter) categoryFilter.addEventListener("change", applyFilters);
  if (sortFilter) sortFilter.addEventListener("change", applyFilters);

  // Check for URL parameters to apply initial filters
  var urlParams = new URLSearchParams(window.location.search);
  var initialSearch = urlParams.get('search');
  var initialCat = urlParams.get('cat');

  if (initialSearch && searchInput) {
      searchInput.value = initialSearch;
      applyFilters();
  } else if (initialCat && categoryFilter) {
      categoryFilter.value = initialCat;
      applyFilters();
  }

  // Wire up Add to Cart buttons
  var addBtns = grid.querySelectorAll(".add-to-cart-btn");
  addBtns.forEach(function (btn) {
    btn.addEventListener("click", function () {
      var productId = this.dataset.id;
      var productName = this.dataset.name;
      addToCart(productId, productName);
    });
  });

  // Fetch initial cart count
  updateCartCount();
}

function updateCartCount() {
  fetch('get_cart_count.php')
    .then(response => response.json())
    .then(data => {
      if (cartCountSpan) {
        cartCountSpan.textContent = data.count || 0;
      }
    })
    .catch(error => console.error('Error fetching cart count:', error));
}

// Cart functionality (Real-time DB backed)
function addToCart(productId, productName) {
  if (!productId) return;

  var formData = new FormData();
  formData.append('product_id', productId);
  formData.append('quantity', 1);
  
  // CSRF token from global variable (set in dashboard PHP)
  if (typeof CSRF_TOKEN !== 'undefined') {
    formData.append('csrf_token', CSRF_TOKEN);
  }

  fetch('add_to_cart.php', {
    method: 'POST',
    body: formData
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Use a better visual feedback if possible, or stick to alert for simplicity
        showGlobalAlert(productName + " added to cart successfully!", "success");
        updateCartCount();
      } else {
        showGlobalAlert("Error: " + data.message, "danger");
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showGlobalAlert("An error occurred. Check browser console.", "danger");
    });
}

function showGlobalAlert(message, type) {
    // Check if we have an alert container, if not create one
    let container = document.getElementById('globalAlertContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'globalAlertContainer';
        container.style.position = 'fixed';
        container.style.top = '80px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        container.style.maxWidth = '300px';
        document.body.appendChild(container);
    }
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `<span>${message}</span>`;
    container.appendChild(alert);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    }, 3000);
}

// Simple farmer registration demo message
function initRegistrationDemo() {
  var btn = document.getElementById("registerFarmerBtn");
  var msg = document.getElementById("registrationMessage");
  if (!btn || !msg) return;

  btn.addEventListener("click", function () {
    msg.textContent = "✅ Registration request submitted! Thank you for joining AgroConnect. (Demo only)";
    msg.className = "alert alert-success mt-lg";
    msg.style.display = "block";
    btn.disabled = true;
  });
}

// Helper for total price calculation
function calculateTotalPrice(unitPrice, quantity) {
  var baseTotal = unitPrice * quantity;
  var discountPercentage = 0;

  if (quantity >= 50) discountPercentage = 15;
  else if (quantity >= 10) discountPercentage = 10;
  else if (quantity > 0) discountPercentage = 5;

  var discountAmount = (baseTotal * discountPercentage) / 100;
  var finalTotal = baseTotal - discountAmount;

  return {
    baseTotal: baseTotal,
    discountPercentage: discountPercentage,
    finalTotal: finalTotal,
  };
}

// Make it available globally
window.calculateTotalPrice = calculateTotalPrice;