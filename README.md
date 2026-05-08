# Acme Widget Co Cart

Laravel-based cart demo for Acme Widget Co with a Bootstrap product listing page and an offcanvas cart sidebar.

## What This App Does

- Lists products on the home page.
- Lets users add products with a selected quantity.
- Shows cart contents in a right-side offcanvas sidebar.
- Supports quantity changes directly in the cart (`+` and `-`).
- Calculates delivery charges based on cart subtotal thresholds.
- Applies volume discounts (offers) on eligible products.
- Displays subtotal, delivery charge, and total in the cart sidebar.
- Persists cart data in the user session.

## Tech Stack

- Laravel 13
- PHP 8.2+
- Bootstrap 5 (CDN)
- Vanilla JavaScript (`public/js/products.js`)

## How It Works

### 1. Product listing

- Route: `GET /`
- Controller method: `CartController@index`
- View: `resources/views/products/index.blade.php`

The page renders product cards with:

- Product name, code, and price
- Quantity input (default `1`)
- Add button

### 2. Cart add/update behavior

- Route: `POST /cart/add/{productCode}`
- Controller method: `CartController@add`
- Request validation: `AddToCartRequest`

Request payload:

```json
{
	"quantity": 1
}
```

Cart keying strategy:

- Cart is stored in session under key `cart`.
- Each item is keyed by `product_id` for constant-time updates.

Quantity rules:

- Positive quantity adds/increments items.
- Negative quantity decrements items.
- If resulting quantity is `<= 0`, the item is removed.

### 3. Cart totals calculation

- Route: `GET /cart/total`
- Controller method: `CartController@total`
- Response:

```json
{
	"subtotal": 65.90,
	"offer_discount": 16.47,
	"delivery_charge": 4.95,
	"total": 54.37
}
```

Example above reflects a cart with 2 × `R01` using current seeded offer and delivery rules.

This endpoint:

- Calculates cart subtotal (sum of price × quantity per item).
- Looks up applicable delivery charge tier by subtotal threshold.
- Applies volume discounts (offers) on eligible products.
- Returns totals for display in the cart sidebar.

The frontend calls this endpoint automatically after any cart mutation to keep the summary up-to-date.

### 4. Frontend cart interactions

Main file: `public/js/products.js`

- `renderCart(cart)` renders cart rows and subtotal.
- `addToCart(productCode, quantity)` is the single cart mutation function used by:
	- Product card Add button
	- Cart sidebar `+` button (`quantity = 1`)
	- Cart sidebar `-` button (`quantity = -1`)

The sidebar opens automatically when adding from product cards.

### 5. Session hydration on page load

- Blade injects `window.initialCart = @json(session('cart', []))`.
- JavaScript calls `renderCart(window.initialCart || {})`.

This ensures cart state survives refreshes.

## Setup

1. Install PHP dependencies:

```bash
composer install
```

2. Install frontend dependencies:

```bash
npm install
```

3. Configure environment:

```bash
cp .env.example .env
php artisan key:generate
```

4. Run migrations:

```bash
php artisan migrate
```

5. Seed data:

```bash
php artisan db:seed
```

6. Start the app:

```bash
php artisan serve
npm run dev
```

## Seeded Data

**Products:**

- `R01` Red Widget (`$32.95`)
- `G01` Green Widget (`$24.95`)
- `B01` Blue Widget (`$7.95`)

**Delivery Charges:**

Tiered by subtotal threshold:

- `$0.00–$49.99` → `$4.95`
- `$50.00–$89.99` → `$2.95`
- `$90.00+` → Free

**Offers:**

- Red Widget (`R01`): Buy 2, get the 2nd at 50% off.
  - Rule: Every 2 items, apply 50% discount to one unit.
  - Example: 2× R01 @ $32.95 each = $65.90 - $16.475 (50% off 1) = `$49.425`

## Assumptions Made

- Cart is session-scoped (guest-user cart), not persisted per authenticated user.
- Quantity `0` is invalid; decrements are represented with `-1`.
- A product entry is removed automatically when quantity becomes `<= 0`.
- Product codes are unique and stable identifiers for add operations.
- Prices are read from DB at add-time and stored in the cart snapshot.
- Currency formatting in UI is USD-style (`$` with 2 decimals).
- Delivery charges are applied based on cart subtotal threshold.
- Offers (volume discounts) are applied per-product based on quantity tiers.
- Cart sidebar displays subtotal, delivery charge, and total (computed server-side).
- CSRF token meta tag exists in layout for AJAX requests.

## Notes

- Items are removed by decrementing to `<= 0`; there is no dedicated `x` (remove) button.
- The `/cart/total` endpoint is called automatically after each cart mutation to fetch current delivery charges and discounts.
- Offers are evaluated server-side based on `SpendThresholdOffer` database records; discount logic uses integer division to determine eligible quantity.

## Staging Admin Seeder

If needed for staging/admin setup:

```bash
php artisan db:seed --class=AdminUserSeeder
```

Current seeded credentials:

- Email: `admin@admin.com`
- Password: `12345678`
