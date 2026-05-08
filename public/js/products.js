const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
const statusRow = document.getElementById('status-message');
const statusAlert = statusRow ? statusRow.querySelector('.alert') : null;
const cartSidebarElement = document.getElementById('cartSidebar');
const cartItemsElement = document.getElementById('cart-items');
const cartEmptyStateElement = document.getElementById('cart-empty-state');
const cartSubtotalElement = document.getElementById('cart-subtotal');
const cartItemCountElement = document.getElementById('cart-item-count');

function formatCurrency(amount) {
    return `$${Number(amount || 0).toFixed(2)}`;
}

function renderCart(cart) {
    if (!cartItemsElement || !cartEmptyStateElement || !cartSubtotalElement || !cartItemCountElement) {
        return;
    }

    const items = Object.values(cart || {});
    const hasItems = items.length > 0;

    cartItemsElement.innerHTML = '';
    cartEmptyStateElement.classList.toggle('d-none', hasItems);

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
                <small>${quantity} x ${formatCurrency(price)}</small>
            </div>
        `;

        cartItemsElement.appendChild(itemRow);
    });

    cartItemCountElement.textContent = String(totalItems);
    cartSubtotalElement.textContent = formatCurrency(subtotal);
}

function openCartSidebar() {
    if (!cartSidebarElement || typeof bootstrap === 'undefined' || !bootstrap.Offcanvas) {
        return;
    }

    const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(cartSidebarElement);
    offcanvas.show();
}

function showMessage(text, type = 'success') {
    if (!statusRow || !statusAlert) {
        return;
    }

    statusAlert.className = `alert alert-${type} mb-0`;
    statusAlert.textContent = text;
    statusRow.classList.remove('d-none');
}

if (csrfTokenMeta) {
    const csrfToken = csrfTokenMeta.getAttribute('content');

    document.querySelectorAll('.add-to-cart-btn').forEach((button) => {
        button.addEventListener('click', async () => {
            const productCode = button.dataset.productCode;
            const productName = button.dataset.productName;
            const qtySelect = document.getElementById(button.dataset.qtySelectId);

            if (!qtySelect) {
                showMessage('Quantity selector is missing.', 'danger');
                return;
            }

            const quantity = qtySelect.value;
            button.disabled = true;

            try {
                const response = await fetch(`/cart/add/${productCode}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ quantity: Number(quantity) }),
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    const message = errorData.message || 'Unable to add product to cart.';
                    showMessage(message, 'danger');
                    return;
                }

                const cart = await response.json().catch(() => ({}));
                renderCart(cart);
                openCartSidebar();

                showMessage(`${quantity} x ${productName} added to cart.`, 'success');
            } catch (error) {
                showMessage('Unable to add product to cart.', 'danger');
            } finally {
                button.disabled = false;
            }
        });
    });
}
