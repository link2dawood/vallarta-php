<?php
include 'header.php';

// Get all categories for dropdown
$categories_query = "SELECT * FROM cat ORDER BY cat_name ASC";
$categories_result = mysqli_query($con, $categories_query);

// Get all products for JavaScript
$products_query = "SELECT movie_id, title, price, unit, cat_id, thumbnail FROM movies WHERE unit > 0 ORDER BY title ASC";
$products_result = mysqli_query($con, $products_query);

$products_json = [];
while ($product = mysqli_fetch_assoc($products_result)) {
    $products_json[] = $product;
}
?>

<title>420 Vallarta - Smart Product Selector</title>

<style>
.product-selector {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.selector-header {
    text-align: center;
    margin-bottom: 30px;
}

.product-row {
    display: flex;
    align-items: center;
    background: #f8f9fa;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 10px;
    border: 2px solid #e9ecef;
}

.product-dropdown {
    flex: 2;
    margin-right: 15px;
}

.product-dropdown select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
}

.quantity-controls {
    display: flex;
    align-items: center;
    margin: 0 15px;
}

.quantity-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: #007bff;
    color: white;
    border-radius: 5px;
    cursor: pointer;
    font-size: 18px;
    font-weight: bold;
}

.quantity-btn:hover {
    background: #0056b3;
}

.quantity-btn:disabled {
    background: #6c757d;
    cursor: not-allowed;
}

.quantity-input {
    width: 60px;
    text-align: center;
    margin: 0 10px;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
}

.product-info {
    flex: 1;
    text-align: right;
}

.product-price {
    font-size: 18px;
    font-weight: bold;
    color: #28a745;
}

.product-stock {
    font-size: 14px;
    color: #6c757d;
}

.product-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 5px;
    margin-right: 15px;
}

.remove-product {
    background: #dc3545;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 5px;
    cursor: pointer;
    margin-left: 10px;
}

.remove-product:hover {
    background: #c82333;
}

.add-product-btn {
    background: #28a745;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    margin-bottom: 20px;
}

.add-product-btn:hover {
    background: #218838;
}

.cart-summary {
    background: #ffffff;
    border: 2px solid #007bff;
    border-radius: 10px;
    padding: 20px;
    margin-top: 30px;
    text-align: center;
}

.cart-total {
    font-size: 24px;
    font-weight: bold;
    color: #007bff;
    margin-bottom: 20px;
}

.checkout-btn {
    background: #007bff;
    color: white;
    border: none;
    padding: 15px 30px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 18px;
    margin: 10px;
}

.checkout-btn:hover {
    background: #0056b3;
}

.category-filter {
    margin-bottom: 20px;
}

.category-filter select {
    width: 200px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
}

.empty-cart {
    text-align: center;
    color: #6c757d;
    font-style: italic;
    margin: 40px 0;
}
</style>

<div class="site-section">
    <div class="container">
        <div class="product-selector">
            <div class="selector-header">
                <h2>Smart Product Selector</h2>
                <p>Select products and quantities with ease. No more typing errors!</p>
            </div>

            <div class="category-filter">
                <label for="category-filter">Filter by Category:</label>
                <select id="category-filter" onchange="filterProducts()">
                    <option value="">All Categories</option>
                    <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['cat_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button class="add-product-btn" onclick="addProductRow()">+ Add Product</button>

            <div id="product-rows">
                <!-- Product rows will be added here dynamically -->
            </div>

            <div class="cart-summary">
                <div class="cart-total">
                    Total: $<span id="grand-total">0.00</span>
                </div>
                <button class="checkout-btn" onclick="proceedToCheckout()" id="checkout-btn" disabled>
                    Proceed to Checkout
                </button>
                <button class="checkout-btn" onclick="addToCart()" id="add-to-cart-btn" style="background: #28a745;" disabled>
                    Add to Cart
                </button>
            </div>

            <div id="empty-cart-message" class="empty-cart">
                <h4>Your cart is empty</h4>
                <p>Add some products to get started!</p>
            </div>
        </div>
    </div>
</div>

<script>
// Products data from PHP
const products = <?= json_encode($products_json) ?>;
let productRowCounter = 0;
let selectedProducts = [];

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    addProductRow(); // Start with one row
    updateCartSummary();
});

function addProductRow() {
    productRowCounter++;
    const rowId = 'product-row-' + productRowCounter;

    const productRow = document.createElement('div');
    productRow.className = 'product-row';
    productRow.id = rowId;

    productRow.innerHTML = `
        <div class="product-dropdown">
            <select onchange="selectProduct(${productRowCounter})" id="product-select-${productRowCounter}">
                <option value="">Select a product...</option>
                ${getProductOptions()}
            </select>
        </div>

        <div class="quantity-controls" id="quantity-controls-${productRowCounter}" style="display: none;">
            <button type="button" class="quantity-btn" onclick="changeQuantity(${productRowCounter}, -1)">-</button>
            <input type="number" class="quantity-input" id="quantity-${productRowCounter}" value="1" min="1" max="1"
                   onchange="updateQuantity(${productRowCounter})" readonly>
            <button type="button" class="quantity-btn" onclick="changeQuantity(${productRowCounter}, 1)">+</button>
        </div>

        <div class="product-info" id="product-info-${productRowCounter}" style="display: none;">
            <img id="product-image-${productRowCounter}" class="product-image" src="" alt="">
            <div class="product-price">$<span id="product-price-${productRowCounter}">0.00</span></div>
            <div class="product-stock">Stock: <span id="product-stock-${productRowCounter}">0</span></div>
        </div>

        <button type="button" class="remove-product" onclick="removeProductRow(${productRowCounter})">×</button>
    `;

    document.getElementById('product-rows').appendChild(productRow);
}

function getProductOptions(categoryFilter = '') {
    let options = '';
    products.forEach(product => {
        if (!categoryFilter || product.cat_id == categoryFilter) {
            options += `<option value="${product.movie_id}" data-price="${product.price}" data-stock="${product.unit}" data-image="${product.thumbnail}" data-category="${product.cat_id}">${product.title} - $${product.price}</option>`;
        }
    });
    return options;
}

function filterProducts() {
    const categoryFilter = document.getElementById('category-filter').value;

    // Update all product dropdowns
    document.querySelectorAll('[id^="product-select-"]').forEach(select => {
        const currentValue = select.value;
        select.innerHTML = '<option value="">Select a product...</option>' + getProductOptions(categoryFilter);

        // Restore selection if still valid
        if (currentValue && select.querySelector(`option[value="${currentValue}"]`)) {
            select.value = currentValue;
        }
    });
}

function selectProduct(rowId) {
    const select = document.getElementById(`product-select-${rowId}`);
    const selectedOption = select.options[select.selectedIndex];

    if (selectedOption.value) {
        const price = parseFloat(selectedOption.dataset.price);
        const stock = parseInt(selectedOption.dataset.stock);
        const image = selectedOption.dataset.image;

        // Show product info and quantity controls
        document.getElementById(`product-info-${rowId}`).style.display = 'block';
        document.getElementById(`quantity-controls-${rowId}`).style.display = 'flex';

        // Update product info
        document.getElementById(`product-price-${rowId}`).textContent = price.toFixed(2);
        document.getElementById(`product-stock-${rowId}`).textContent = stock;
        document.getElementById(`product-image-${rowId}`).src = `uploads/${image}`;

        // Update quantity input max value
        const quantityInput = document.getElementById(`quantity-${rowId}`);
        quantityInput.max = stock;
        quantityInput.value = 1;

        // Update buttons state
        updateQuantityButtons(rowId);

    } else {
        // Hide controls if no product selected
        document.getElementById(`product-info-${rowId}`).style.display = 'none';
        document.getElementById(`quantity-controls-${rowId}`).style.display = 'none';
    }

    updateCartSummary();
}

function changeQuantity(rowId, change) {
    const quantityInput = document.getElementById(`quantity-${rowId}`);
    const currentQuantity = parseInt(quantityInput.value);
    const maxQuantity = parseInt(quantityInput.max);

    const newQuantity = Math.max(1, Math.min(maxQuantity, currentQuantity + change));
    quantityInput.value = newQuantity;

    updateQuantityButtons(rowId);
    updateCartSummary();
}

function updateQuantity(rowId) {
    updateQuantityButtons(rowId);
    updateCartSummary();
}

function updateQuantityButtons(rowId) {
    const quantityInput = document.getElementById(`quantity-${rowId}`);
    const quantity = parseInt(quantityInput.value);
    const maxQuantity = parseInt(quantityInput.max);

    const minusBtn = document.querySelector(`#product-row-${rowId} .quantity-btn:first-of-type`);
    const plusBtn = document.querySelector(`#product-row-${rowId} .quantity-btn:last-of-type`);

    minusBtn.disabled = quantity <= 1;
    plusBtn.disabled = quantity >= maxQuantity;
}

function removeProductRow(rowId) {
    const row = document.getElementById(`product-row-${rowId}`);
    if (row) {
        row.remove();
        updateCartSummary();
    }
}

function updateCartSummary() {
    let total = 0;
    let hasProducts = false;

    selectedProducts = [];

    document.querySelectorAll('.product-row').forEach(row => {
        const rowId = row.id.split('-')[2];
        const select = document.getElementById(`product-select-${rowId}`);
        const quantityInput = document.getElementById(`quantity-${rowId}`);

        if (select && select.value && quantityInput) {
            const selectedOption = select.options[select.selectedIndex];
            const price = parseFloat(selectedOption.dataset.price);
            const quantity = parseInt(quantityInput.value);
            const subtotal = price * quantity;

            total += subtotal;
            hasProducts = true;

            selectedProducts.push({
                id: select.value,
                name: selectedOption.text.split(' - $')[0],
                price: price,
                quantity: quantity,
                image: selectedOption.dataset.image
            });
        }
    });

    document.getElementById('grand-total').textContent = total.toFixed(2);
    document.getElementById('checkout-btn').disabled = !hasProducts;
    document.getElementById('add-to-cart-btn').disabled = !hasProducts;

    // Show/hide empty cart message
    const emptyMessage = document.getElementById('empty-cart-message');
    const cartSummary = document.querySelector('.cart-summary');

    if (hasProducts) {
        emptyMessage.style.display = 'none';
        cartSummary.style.display = 'block';
    } else {
        emptyMessage.style.display = 'block';
        cartSummary.style.display = 'none';
    }
}

function addToCart() {
    if (selectedProducts.length === 0) {
        alert('Please select at least one product');
        return;
    }

    // Add each product to cart individually
    let addedCount = 0;

    selectedProducts.forEach(product => {
        const formData = new FormData();
        formData.append('add_to_cart', '1');
        formData.append('movie_id', product.id);
        formData.append('product_name', product.name);
        formData.append('product_price', product.price);
        formData.append('product_image', product.image);

        fetch('cart/cartfunction.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            addedCount++;
            if (addedCount === selectedProducts.length) {
                alert('Products added to cart successfully!');
                // Reset the form
                document.getElementById('product-rows').innerHTML = '';
                productRowCounter = 0;
                addProductRow();
                updateCartSummary();
            }
        })
        .catch(error => {
            console.error('Error adding product to cart:', error);
            alert('Error adding product to cart. Please try again.');
        });
    });
}

function proceedToCheckout() {
    if (selectedProducts.length === 0) {
        alert('Please select at least one product');
        return;
    }

    addToCart();

    // Wait a moment for cart addition to complete, then redirect
    setTimeout(() => {
        window.location.href = 'checkout.php';
    }, 2000);
}
</script>

<?php include 'footer.php'; ?>