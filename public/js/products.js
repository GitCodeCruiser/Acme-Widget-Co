const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
const statusAlert = document.getElementById('status-message')?.querySelector('.alert');
const cartSidebarElement = document.getElementById('cartSidebar');
const cartItemsElement = document.getElementById('cart-items');
const cartEmptyStateElement = document.getElementById('cart-empty-state');
const cartSummaryElement = document.getElementById('cart-summary');
const cartDeliveryChargeElement = document.getElementById('cart-delivery-charge');
const cartTotalElement = document.getElementById('cart-total');

function formatCurrency(amount) {
    return `$${Number(amount || 0).toFixed(2)}`;
}

function applyCartSummary(summary = {}) {
    if (!cartDeliveryChargeElement || !cartTotalElement) {
        return;
    }

    const deliveryCharge = summary.delivery_charge ?? 0;
    const total = summary.total ?? 0;

    cartDeliveryChargeElement.textContent = formatCurrency(deliveryCharge);
    cartTotalElement.textContent = formatCurrency(total);
}

async function refreshCartTotals() {
    try {
        const response = await fetch('/cart/total', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            },
        });

        if (!response.ok) {
            return;
        }

        const totals = await response.json().catch(() => null);

        if (totals) {
            applyCartSummary(totals);
        }
    } catch (error) {

    }
}

function renderCart(cart) {
    if (!cartItemsElement || !cartEmptyStateElement) {
        return;
    }

    const items = Object.values(cart || {});
    const hasItems = items.length > 0;

    cartItemsElement.innerHTML = '';
    cartEmptyStateElement.classList.toggle('d-none', hasItems);
    cartSummaryElement?.classList.toggle('d-none', !hasItems);

    let totalItems = 0;
    let subtotal = 0;

    items.forEach((item) => {
        const quantity = Number(item.quantity || 0);
        const price = Number(item.price || 0);

        totalItems += quantity;
        subtotal += price * quantity;

        const itemRow = document.createElement('div');
        itemRow.className = 'list-group-item';
        itemRow.innerHTML = `
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                    <div class="fw-semibold">${item.name}</div>
                    <small class="text-secondary">${item.product_code}</small>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary cart-qty-decrement" data-product-code="${item.product_code}" data-product-id="${item.product_id}">-</button>
                    <span class="px-1">${quantity}</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary cart-qty-increment" data-product-code="${item.product_code}">+</button>
                </div>
            </div>
        `;

        itemRow.querySelector('.cart-qty-increment').addEventListener('click', () => {
            addToCart(item.product_code, 1);
        });

        itemRow.querySelector('.cart-qty-decrement').addEventListener('click', () => {
            addToCart(item.product_code, -1);
        });

        cartItemsElement.appendChild(itemRow);
    });

    applyCartSummary({
        item_count: totalItems,
        subtotal,
        total: subtotal,
    });

    void refreshCartTotals();
}

async function addToCart(productCode, quantity) {
    if (!csrfToken) {
        return null;
    }

    try {
        const response = await fetch(`/cart/add/${productCode}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ quantity }),
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            showMessage(errorData.message || 'Unable to update cart.', 'danger');
            return null;
        }

        const cart = await response.json().catch(() => ({}));
        renderCart(cart);
        return cart;
    } catch (error) {
        showMessage('Unable to update cart.', 'danger');
        return null;
    }
}

function openCartSidebar() {
    if (!cartSidebarElement || typeof bootstrap === 'undefined' || !bootstrap.Offcanvas) {
        return;
    }

    const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(cartSidebarElement);
    offcanvas.show();
}

function showMessage(text, type = 'success') {
    if (!statusAlert) {
        return;
    }

    statusAlert.className = `alert alert-${type} mb-0`;
    statusAlert.textContent = text;
    statusAlert.parentElement?.classList.remove('d-none');
}

renderCart(window.initialCart || {});

if (csrfToken) {
    document.querySelectorAll('.add-to-cart-btn').forEach((button) => {
        button.addEventListener('click', async () => {
            const productCode = button.dataset.productCode;
            const productName = button.dataset.productName;
            const qtySelect = document.getElementById(button.dataset.qtySelectId);

            if (!qtySelect) {
                showMessage('Quantity selector is missing.', 'danger');
                return;
            }

            const quantity = Number(qtySelect.value);
            button.disabled = true;

            try {
                const cart = await addToCart(productCode, quantity);

                if (cart) {
                    openCartSidebar();
                    showMessage(`${quantity} x ${productName} added to cart.`, 'success');
                }
            } finally {
                button.disabled = false;
            }
        });
    });
}
