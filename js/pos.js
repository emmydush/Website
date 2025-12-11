// POS System JavaScript

let cart = [];
const TAX_RATE = 0.18; // 18% tax

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Toast
    const toast = new Toast({ duration: 3000 });
    
    // Barcode Scanner Setup
    let barcodeBuffer = '';
    let barcodeTimeout;
    const BARCODE_TIMEOUT = 100; // milliseconds to wait before processing
    
    // Create hidden barcode input if it doesn't exist
    if (!document.getElementById('barcodeInput')) {
        const barcodeInput = document.createElement('input');
        barcodeInput.id = 'barcodeInput';
        barcodeInput.type = 'text';
        barcodeInput.style.position = 'absolute';
        barcodeInput.style.left = '-9999px';
        document.body.appendChild(barcodeInput);
    }
    
    const barcodeInput = document.getElementById('barcodeInput');
    
    // Product search
    const productSearch = document.getElementById('productSearch');
    const productsGrid = document.getElementById('productsGrid');
    const productCards = productsGrid.querySelectorAll('.product-card');
    
    productSearch.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        
        productCards.forEach(card => {
            const productName = card.querySelector('.product-name').textContent.toLowerCase();
            const barcode = card.dataset.barcode || '';
            if (productName.includes(searchTerm) || barcode.includes(searchTerm)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
    
    // Barcode scanner detection - any key press goes to barcode input
    document.addEventListener('keydown', function(e) {
        // Only capture if not in a text input
        if (e.target.id === 'productSearch' || e.target.id === 'discountInput') {
            return;
        }
        
        // Ctrl+B for manual scanner activation
        if (e.ctrlKey && e.key === 'b') {
            e.preventDefault();
            activateScanner();
            return;
        }
        
        // Enter for checkout (unless in barcode mode)
        if (e.key === 'Enter' && document.activeElement.id !== 'barcodeInput') {
            e.preventDefault();
            checkout();
            return;
        }
    });
    
    // Barcode input handling
    barcodeInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const barcode = barcodeInput.value.trim();
            
            if (barcode) {
                processBarcodeInput(barcode);
            }
            
            barcodeInput.value = '';
            barcodeInput.blur();
        }
    });
    
    // Scanner button click
    document.getElementById('scannerBtn').addEventListener('click', function() {
        activateScanner();
    });
    
    function activateScanner() {
        barcodeInput.focus();
        const toast = new Toast();
        toast.show('Scanner active - ready to scan', 'info', { title: 'Scanner Mode' });
    }
    
    function processBarcodeInput(barcode) {
        const toast = new Toast();
        
        // Search for product by barcode or ID
        let found = false;
        const productCards = document.querySelectorAll('.product-card');
        
        for (const card of productCards) {
            const cardBarcode = card.dataset.barcode || '';
            const cardId = card.dataset.productId || '';
            const productName = card.querySelector('.product-name').textContent;
            
            if (cardBarcode === barcode || cardId === barcode) {
                // Simulate click on the add to cart button
                const addBtn = card.querySelector('.add-to-cart-btn');
                if (addBtn) {
                    addBtn.click();
                    toast.show(`${productName} scanned and added to cart`, 'success', { title: 'Product Scanned' });
                    found = true;
                    break;
                }
            }
        }
        
        if (!found) {
            toast.show(`Barcode not found: ${barcode}`, 'warning', { title: 'Barcode Scan Failed' });
        }
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+B for scanner
        if (e.ctrlKey && e.key === 'b') {
            e.preventDefault();
            activateScanner();
        }
        
        // Enter for checkout
        if (e.key === 'Enter' && document.activeElement.id !== 'productSearch' && document.activeElement.id !== 'discountInput' && document.activeElement.id !== 'barcodeInput') {
            e.preventDefault();
            checkout();
        }
    });
    
    // Discount input
    document.getElementById('discountInput').addEventListener('change', updateCartSummary);
    
    // Checkout button
    document.getElementById('checkoutBtn').addEventListener('click', checkout);
    
    // Clear cart button
    document.querySelector('.clear-cart-btn').addEventListener('click', function() {
        if (cart.length > 0) {
            if (confirm('Are you sure you want to clear the cart?')) {
                cart = [];
                updateCart();
                toast.show('Cart cleared', 'info', { title: 'Cart Updated' });
            }
        }
    });
    
    // Update time
    updateDateTime();
    setInterval(updateDateTime, 60000);
});

function updateDateTime() {
    const now = new Date();
    const options = { 
        weekday: 'short', 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    document.getElementById('currentDateTime').textContent = now.toLocaleDateString('en-US', options);
}

function addToCart(button) {
    const card = button.closest('.product-card');
    const productId = parseInt(card.dataset.productId);
    const productName = card.dataset.productName;
    const productPrice = parseFloat(card.dataset.productPrice);
    
    // Check if product already in cart
    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: productId,
            name: productName,
            price: productPrice,
            quantity: 1
        });
    }
    
    updateCart();
    
    // Show toast
    const toast = new Toast();
    toast.show(`${productName} added to cart`, 'success', { title: 'Added to Cart' });
}

function updateCart() {
    const cartItemsDiv = document.getElementById('cartItems');
    const cartBadge = document.getElementById('cartBadge');
    
    cartBadge.textContent = cart.length;
    
    if (cart.length === 0) {
        cartItemsDiv.innerHTML = `
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <p>Your cart is empty</p>
            </div>
        `;
    } else {
        cartItemsDiv.innerHTML = cart.map((item, index) => `
            <div class="cart-item">
                <div class="cart-item-info">
                    <p class="cart-item-name">${item.name}</p>
                    <p class="cart-item-price">${item.price.toFixed(2)} FRW × ${item.quantity}</p>
                </div>
                <div class="cart-item-qty">
                    <button onclick="decreaseQuantity(${index})">-</button>
                    <input type="number" value="${item.quantity}" min="1" onchange="updateQuantity(${index}, this.value)">
                    <button onclick="increaseQuantity(${index})">+</button>
                </div>
                <button class="cart-item-remove" onclick="removeFromCart(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `).join('');
    }
    
    updateCartSummary();
}

function increaseQuantity(index) {
    cart[index].quantity += 1;
    updateCart();
}

function decreaseQuantity(index) {
    if (cart[index].quantity > 1) {
        cart[index].quantity -= 1;
    } else {
        removeFromCart(index);
    }
    updateCart();
}

function updateQuantity(index, value) {
    const qty = parseInt(value);
    if (qty > 0) {
        cart[index].quantity = qty;
        updateCart();
    }
}

function removeFromCart(index) {
    const item = cart[index];
    cart.splice(index, 1);
    updateCart();
    
    const toast = new Toast();
    toast.show(`${item.name} removed from cart`, 'info');
}

function updateCartSummary() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const discountPercent = parseFloat(document.getElementById('discountInput').value) || 0;
    const discountAmount = subtotal * (discountPercent / 100);
    const subtotalAfterDiscount = subtotal - discountAmount;
    const tax = subtotalAfterDiscount * TAX_RATE;
    const total = subtotalAfterDiscount + tax;
    
    document.getElementById('subtotal').textContent = subtotal.toFixed(2) + ' FRW';
    document.getElementById('discount').textContent = discountAmount.toFixed(2) + ' FRW';
    document.getElementById('tax').textContent = tax.toFixed(2) + ' FRW';
    document.getElementById('total').textContent = total.toFixed(2) + ' FRW';
    
    // Enable/disable checkout button
    document.getElementById('checkoutBtn').disabled = cart.length === 0;
}

function checkout() {
    if (cart.length === 0) {
        const toast = new Toast();
        toast.show('Cart is empty. Add items first.', 'warning', { title: 'Empty Cart' });
        return;
    }
    
    // Calculate totals
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const discountPercent = parseFloat(document.getElementById('discountInput').value) || 0;
    const discountAmount = subtotal * (discountPercent / 100);
    const subtotalAfterDiscount = subtotal - discountAmount;
    const tax = subtotalAfterDiscount * TAX_RATE;
    const total = subtotalAfterDiscount + tax;
    
    const toast = new Toast();
    
    // Save each item in cart to database
    const savePromises = cart.map(item => {
        const formData = new FormData();
        formData.append('product_id', item.id);
        formData.append('quantity_sold', item.quantity);
        formData.append('sale_price', item.price);
        formData.append('total_amount', item.price * item.quantity);
        
        return fetch('php/add_sale.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'success') {
                throw new Error(data.message || 'Failed to save sale');
            }
            return data;
        });
    });
    
    // Wait for all sales to be saved
    Promise.all(savePromises)
        .then(results => {
            // All sales saved successfully
            toast.show(`Order completed! Total: ${total.toFixed(2)} FRW`, 'success', { title: 'Payment Successful' });
            
            // Clear cart after successful checkout
            setTimeout(() => {
                cart = [];
                document.getElementById('discountInput').value = '';
                updateCart();
            }, 1500);
        })
        .catch(error => {
            console.error('Checkout error:', error);
            toast.show(`Checkout failed: ${error.message}`, 'error', { title: 'Error' });
        });
}
