<div class="offcanvas offcanvas-end" tabindex="-1" id="cartSidebar" aria-labelledby="cartSidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="cartSidebarLabel">Cart</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column">
        <p id="cart-empty-state" class="text-secondary mb-3">Your cart is empty.</p>

        <div id="cart-items" class="list-group mb-3"></div>

        <div id="cart-summary" class="mt-auto border-top pt-3 d-none">
            <div class="d-flex justify-content-between">
                <span>Delivery</span>
                <strong id="cart-delivery-charge">$0.00</strong>
            </div>
            <div class="d-flex justify-content-between border-top mt-2 pt-2">
                <span>Total</span>
                <strong id="cart-total">$0.00</strong>
            </div>
        </div>
    </div>
</div>
