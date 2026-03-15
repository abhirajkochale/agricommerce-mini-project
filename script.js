// script.js - JavaScript concepts and interactions

// Sample product data using arrays and variables
var productNames = ["Wheat", "Rice", "Tomato", "Potato", "Mango"];
var productCategories = ["Grains", "Grains", "Vegetables", "Vegetables", "Fruits"];
var productPrices = [28, 40, 18, 22, 70];
var productAvailability = [true, true, false, true, true];

var cart = [];
var cartCountSpan;

// DOMContentLoaded to ensure elements exist
window.addEventListener("DOMContentLoaded", function () {
  var welcome = document.getElementById("welcomeMessage");
  if (welcome) {
    welcome.textContent = "Welcome to AgroConnect! Explore fresh crops and place demo orders.";
  }

  cartCountSpan = document.getElementById("cartCount");

  initThemeToggle();
  initProductsPage();
  initRegistrationDemo();
});

// Theme toggle (light/dark mode)
function initThemeToggle() {
  var toggleBtn = document.getElementById("themeToggle");
  if (!toggleBtn) return;

  toggleBtn.addEventListener("click", function () {
    document.body.classList.toggle("dark-theme");
    if (document.body.classList.contains("dark-theme")) {
      toggleBtn.textContent = "Light Mode";
    } else {
      toggleBtn.textContent = "Dark Mode";
    }
  });
}

// Initialize products page: render cards, wire search/filter
function initProductsPage() {
  var grid = document.getElementById("productGrid");
  if (!grid) return; // Only on product.html

  // Build product cards dynamically using arrays
  grid.innerHTML = "";
  for (var i = 0; i < productNames.length; i++) {
    var card = document.createElement("div");
    card.className = "product-card";
    card.dataset.category = productCategories[i];
    card.dataset.name = productNames[i].toLowerCase();

    var title = document.createElement("h3");
    title.textContent = productNames[i];

    var priceP = document.createElement("p");
    priceP.textContent = "Price: Rs. " + productPrices[i] + " / kg";

    // Demonstrate variables and operators
    var quantityExample = 10; // example quantity
    var totalPriceExample = productPrices[i] * quantityExample; // operators

    var availabilityP = document.createElement("p");
    availabilityP.textContent = productAvailability[i]
      ? "Available now"
      : "Currently unavailable";

    var infoP = document.createElement("p");
    infoP.textContent =
      "Example: 10 kg would cost Rs. " + totalPriceExample + " (before discount).";

    var addBtn = document.createElement("button");
    addBtn.textContent = "Add to Cart";
    addBtn.addEventListener("click", (function (index) {
      return function () {
        addToCart(index);
      };
    })(i));

    card.appendChild(title);
    card.appendChild(priceP);
    card.appendChild(availabilityP);
    card.appendChild(infoP);
    card.appendChild(addBtn);

    grid.appendChild(card);
  }

  // Search/filter products
  var searchInput = document.getElementById("searchInput");
  var categoryFilter = document.getElementById("categoryFilter");

  function applyFilters() {
    var searchTerm = searchInput.value.toLowerCase();
    var selectedCategory = categoryFilter.value;

    var cards = grid.getElementsByClassName("product-card");
    for (var j = 0; j < cards.length; j++) {
      var card = cards[j];
      var name = card.dataset.name;
      var category = card.dataset.category;

      // switch statement for category messaging (demonstration)
      switch (selectedCategory) {
        case "Grains":
        case "Vegetables":
        case "Fruits":
        case "all":
          // allowed values, nothing special needed
          break;
        default:
          selectedCategory = "all";
      }

      var matchesSearch = name.indexOf(searchTerm) !== -1;
      var matchesCategory =
        selectedCategory === "all" || category === selectedCategory;

      card.style.display = matchesSearch && matchesCategory ? "flex" : "none";
    }
  }

  if (searchInput) {
    searchInput.addEventListener("input", applyFilters);
  }
  if (categoryFilter) {
    categoryFilter.addEventListener("change", applyFilters);
  }
}

// Cart functionality
function addToCart(productIndex) {
  var name = productNames[productIndex];
  var price = productPrices[productIndex];
  var available = productAvailability[productIndex];

  // if-else discount logic based on availability and simple rule
  var discount;
  if (!available) {
    alert(name + " is currently unavailable and cannot be added.");
    return;
  } else if (price > 50) {
    discount = 10; // 10% discount for expensive crops
  } else {
    discount = 5; // 5% discount otherwise
  }

  cart.push({ name: name, price: price, discount: discount });

  if (cartCountSpan) {
    cartCountSpan.textContent = cart.length;
  }

  alert(
    name +
      " added to cart with a " +
      discount +
      "% discount (demo only)."
  );
}

// Simple farmer registration demo message
function initRegistrationDemo() {
  var btn = document.getElementById("registerFarmerBtn");
  var msg = document.getElementById("registrationMessage");
  if (!btn || !msg) return;

  btn.addEventListener("click", function () {
    msg.textContent =
      "Thank you! This is a demo registration. In the real system, farmer details would be stored in the database.";
  });
}

// Expose a helper for total price calculation used by validation.js
function calculateTotalPrice(unitPrice, quantity) {
  var baseTotal = unitPrice * quantity;
  var discountPercentage = 0;

  if (quantity >= 50) {
    discountPercentage = 15;
  } else if (quantity >= 10) {
    discountPercentage = 10;
  } else if (quantity > 0) {
    discountPercentage = 5;
  }

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
