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

// Initialize products page: render cards, wire search/filter
function initProductsPage() {
  var grid = document.getElementById("productGrid");
  if (!grid) return;

  grid.innerHTML = "";
  for (var i = 0; i < productNames.length; i++) {
    var card = document.createElement("div");
    card.className = "card p-0 overflow-hidden product-card";
    card.dataset.category = productCategories[i];
    card.dataset.name = productNames[i].toLowerCase();

    // Random crop image for variety (using Unsplash)
    var imgId = 1500382 + i;
    var img = document.createElement("img");
    img.src = "https://images.unsplash.com/photo-" + imgId + "?auto=format&fit=crop&w=400&q=80";
    img.style.cssText = "width:100%; height:180px; object-fit:cover;";

    var content = document.createElement("div");
    content.style.padding = "1.5rem";

    var title = document.createElement("h3");
    title.textContent = productNames[i];
    title.style.marginBottom = "0.5rem";

    var categoryBadge = document.createElement("span");
    categoryBadge.textContent = productCategories[i];
    categoryBadge.style.cssText = "display:inline-block; background:var(--bg-light); color:var(--primary-color); padding:4px 12px; border-radius:12px; font-size:0.75rem; font-weight:600; margin-bottom:1rem;";

    var priceP = document.createElement("p");
    priceP.innerHTML = "<strong>Rs. " + productPrices[i] + "</strong> / kg";
    priceP.style.fontSize = "1.1rem";

    var addBtn = document.createElement("button");
    addBtn.className = "btn btn-primary btn-block mt-lg";
    addBtn.style.marginTop = "1rem";
    addBtn.textContent = "Add to Cart";
    
    if (!productAvailability[i]) {
      addBtn.disabled = true;
      addBtn.textContent = "Out of Stock";
      addBtn.style.background = "#ccc";
    }

    addBtn.addEventListener("click", (function (index) {
      return function () {
        addToCart(index);
      };
    })(i));

    content.appendChild(categoryBadge);
    content.appendChild(title);
    content.appendChild(priceP);
    content.appendChild(addBtn);

    card.appendChild(img);
    card.appendChild(content);

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
      var c = cards[j];
      var name = c.dataset.name;
      var category = c.dataset.category;

      var matchesSearch = name.indexOf(searchTerm) !== -1;
      var matchesCategory = selectedCategory === "all" || category === selectedCategory;

      c.style.display = matchesSearch && matchesCategory ? "block" : "none";
    }
  }

  if (searchInput) searchInput.addEventListener("input", applyFilters);
  if (categoryFilter) categoryFilter.addEventListener("change", applyFilters);
}

// Cart functionality
function addToCart(productIndex) {
  var name = productNames[productIndex];
  var price = productPrices[productIndex];

  cart.push({ name: name, price: price });

  if (cartCountSpan) {
    cartCountSpan.textContent = cart.length;
  }

  // Show a nice snackbar/toast instead of alert if possible, but keeping it simple as per rules
  alert(name + " added to cart! (Demo)");
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
