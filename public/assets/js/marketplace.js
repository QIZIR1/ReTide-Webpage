let cart = window.INITIAL_CART || [];

// Add
function addToCart(product) {
    $.ajax({
        url: '/cart/add',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            product_id: product.id
        },
        success: function(response) {
            const itemAda = cart.find((item) => item.id == product.id);

            if (itemAda) {
                itemAda.jumlah++;
            } else {
                cart.push({
                    ...product,
                    jumlah: 1,
                });
            }

            updateCart();
            openCart();
        },
        error: function(xhr) {
            console.error('Error adding to cart:', xhr);
            if (xhr.status === 401) {
                showToast('Silakan login terlebih dahulu', 'error');
                setTimeout(() => {
                    window.location.href = '/login';
                }, 1500);
            } else if (xhr.status === 422 && xhr.responseJSON) {
                // Stock limit reached
                showToast(xhr.responseJSON.message, 'error');
            } else {
                showToast('Gagal menambahkan ke keranjang', 'error');
            }
        }
    });
}

// Update
function updateCart() {
    const cartCount = cart.reduce((sum, item) => sum + item.jumlah, 0);
    $("#cart-count").text(cartCount);
    
    // Animate cart count
    $("#cart-count").addClass('scale-150');
    setTimeout(() => $("#cart-count").removeClass('scale-150'), 200);

    const cartItems = $("#cart-items");
    cartItems.empty();

    if (cart.length === 0) {
        cartItems.append(`
            <div class="flex flex-col items-center justify-center h-full text-gray-500 py-10">
                <i class="fas fa-shopping-basket text-5xl mb-4 opacity-50"></i>
                <p>Your cart is empty.</p>
            </div>
        `);
    } else {
        let cartHtml = '';
        cart.forEach((item) => {
            cartHtml += `
                <div class="flex items-center gap-4 bg-surfaceElevated border border-border p-3 rounded-xl relative group">
                    <div class="w-20 h-20 bg-surfaceActive rounded-lg overflow-hidden flex-shrink-0 border border-border">
                        ${item.icon}
                    </div>
                    <div class="flex-grow">
                        <h4 class="text-white font-medium text-sm mb-1">${item.name}</h4>
                        <div class="text-brand font-bold text-sm mb-2">Rp ${parseInt(item.price).toLocaleString('id-ID')}</div>
                        <div class="flex items-center space-x-3 bg-surfaceActive rounded-lg w-fit px-2 py-1">
                            <button class="qty-btn qty-minus text-gray-400 hover:text-white p-2" aria-label="Decrease quantity" data-id="${item.id}">
                                <i class="fas fa-minus text-xs"></i>
                            </button>
                            <span class="qty-display text-white font-medium w-6 text-center text-sm">${item.jumlah}</span>
                            <button class="qty-btn qty-plus text-gray-400 hover:text-white p-2" aria-label="Increase quantity" data-id="${item.id}">
                                <i class="fas fa-plus text-xs"></i>
                            </button>
                        </div>
                    </div>
                    <button class="remove-btn absolute top-3 right-3 text-gray-500 hover:text-red-500 transition-colors bg-surfaceElevated rounded-full p-2 opacity-0 group-hover:opacity-100" aria-label="Remove item" data-id="${item.id}" title="Remove Item">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            `;
        });
        cartItems.append(cartHtml);
    }

    updateTotal();
}

// Update total
function updateTotal() {
    const total = cart.reduce((sum, item) => sum + item.price * item.jumlah, 0);
    $("#total-price").text(total.toLocaleString('id-ID'));
}

// Increase (validated against available stock on the server)
function tambahKuantitas(productId) {
    const item = cart.find((item) => item.id == productId);
    if (!item) return;

    $.ajax({
        url: '/cart/update',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            product_id: productId,
            quantity: item.jumlah + 1
        },
        success: function(response) {
            item.jumlah = response.quantity;
            updateCart();
        },
        error: function(xhr) {
            const message = xhr.responseJSON && xhr.responseJSON.message
                ? xhr.responseJSON.message
                : 'Gagal memperbarui jumlah';
            showToast(message, 'error');
        }
    });
}

// Decrease
function kurangKuantitas(productId) {
    const item = cart.find((item) => item.id == productId);
    if (item) {
        if (item.jumlah > 1) {
            item.jumlah--;
        } else {
            removeFromCart(productId);
        }
        updateCart();
    }
}

// Remove
function removeFromCart(productId) {
    cart = cart.filter((item) => item.id != productId);
    updateCart();
}

// Clear
function clearCart() {
    if (cart.length > 0 && confirm("Are you sure you want to clear your cart?")) {
        cart = [];
        updateCart();
    }
}

// Cart UI Logic
function openCart() {
    $("#cart-overlay").removeClass("hidden");
    // slight delay for overlay fade
    setTimeout(() => {
        $("#cart-overlay").removeClass("opacity-0").addClass("opacity-100");
    }, 10);
    $("#cart-section").removeClass("translate-x-full");
}

function closeCart() {
    $("#cart-section").addClass("translate-x-full");
    $("#cart-overlay").removeClass("opacity-100").addClass("opacity-0");
    setTimeout(() => {
        $("#cart-overlay").addClass("hidden");
    }, 300);
}

// Checkout
function checkout() {
    if (cart.length === 0) {
        showToast("Your cart is empty!", "error");
        return;
    }
    
    // Sync cart to backend first
    const cartData = {};
    cart.forEach(item => {
        cartData[item.id] = {
            id: item.id,
            name: item.name,
            quantity: item.jumlah,
            price: item.price,
            image: item.icon
        };
    });

    // Disable button to prevent double clicks
    const $btn = $(".checkout-btn");
    const originalContent = $btn.html();
    $btn.html('<i class="fas fa-spinner fa-spin mr-2"></i> Processing...').prop("disabled", true);

    $.ajax({
        url: '/cart/sync',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: { cart: cartData },
        success: function() {
            window.location.href = '/cart/checkout';
        },
        error: function(xhr) {
            console.error('Error syncing cart:', xhr);
            const message = xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.message
                ? xhr.responseJSON.message
                : 'Failed to prepare checkout';
            showToast(message, 'error');
            $btn.html(originalContent).prop("disabled", false);
        }
    });
}

// Show Toast Notification
function showToast(message, type = "success") {
    if ($(".toast-container").length === 0) {
        $("body").append('<div class="toast-container fixed bottom-8 right-8 z-[100] flex flex-col gap-3"></div>');
    }

    const isSuccess = type === "success";
    const bgColor = isSuccess ? "bg-brand" : "bg-red-500";
    const textColor = isSuccess ? "text-black" : "text-white";
    const icon = isSuccess ? "fa-check-circle" : "fa-exclamation-circle";

    const toast = $(`
        <div class="toast-message ${bgColor} ${textColor} px-6 py-4 rounded-xl font-medium shadow-2xl flex items-center gap-3 transform translate-x-[120%] transition-transform duration-300">
            <i class="fas ${icon} text-xl"></i>
            <span>${message}</span>
        </div>
    `);

    $(".toast-container").append(toast);

    // Slide in
    requestAnimationFrame(() => {
        toast.removeClass("translate-x-[120%]");
    });

    // Remove after 3s
    setTimeout(() => {
        toast.addClass("translate-x-[120%]");
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Event listener
$(document).ready(function () {
    
    // Add to cart
    $(document).on("click", ".add-to-cart-btn", function () {
        const $btn = $(this);
        const originalContent = $btn.html();
        
        const product = {
            id: $btn.data("id"),
            name: $btn.data("name"),
            price: parseInt($btn.data("price")),
            icon: $btn.data("icon"), // html string
        };
        
        addToCart(product);

        // UI Feedback: Button Animation
        $btn.addClass("bg-green-500 text-white").removeClass("hover:bg-brand text-black bg-surfaceActive")
            .html('<i class="fas fa-check"></i>')
            .prop("disabled", true);
        
        // Revert button after 1.5 seconds
        setTimeout(() => {
            $btn.removeClass("bg-green-500 text-white").addClass("hover:bg-brand bg-surfaceActive")
                .html(originalContent)
                .prop("disabled", false);
        }, 1500);
    });

    // Cart toggles
    $("#cart-btn").click(openCart);
    $("#close-cart, #cart-overlay").click(closeCart);

    // jumlah controls
    $(document).on("click", ".qty-plus", function () {
        tambahKuantitas($(this).data("id"));
    });

    $(document).on("click", ".qty-minus", function () {
        kurangKuantitas($(this).data("id"));
    });

    // Remove item
    $(document).on("click", ".remove-btn", function () {
        removeFromCart($(this).data("id"));
    });

    // Checkout
    $(".checkout-btn").click(checkout);
    
    // Initial empty state render
    updateCart();
});
