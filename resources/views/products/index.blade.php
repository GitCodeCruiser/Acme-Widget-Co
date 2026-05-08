@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <div class="container py-4">
        <div class="row mb-3">
            <div class="col-12 d-flex align-items-center justify-content-between gap-2">
                <h1 class="h3 mb-0">Products</h1>
                <button
                    class="btn btn-outline-secondary"
                    type="button"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#cartSidebar"
                    aria-controls="cartSidebar"
                >
                    View Cart
                </button>
            </div>
        </div>

        <div id="status-message" class="row mb-3 d-none" aria-live="polite">
            <div class="col-12">
                <div class="alert alert-success mb-0" role="alert"></div>
            </div>
        </div>

        <div class="row g-3">
            @forelse ($products as $product)
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <h2 class="h5 card-title mb-1">{{ $product->name }}</h2>
                            <p class="text-secondary mb-1">Code: {{ $product->code }}</p>
                            <p class="fw-semibold mb-3">${{ number_format((float) $product->price, 2) }}</p>

                            <div class="mt-auto d-flex gap-2 align-items-end">
                                <div class="flex-grow-1">
                                    <label class="form-label mb-1" for="qty-{{ $product->id }}">Qty</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        id="qty-{{ $product->id }}"
                                        name="quantity"
                                        min="1"
                                        step="1"
                                        value="1"
                                    >
                                </div>
                                <button
                                    type="button"
                                    class="btn btn-primary add-to-cart-btn"
                                    data-product-code="{{ $product->code }}"
                                    data-product-name="{{ $product->name }}"
                                    data-qty-select-id="qty-{{ $product->id }}"
                                >
                                    Add
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-secondary mb-0" role="alert">
                        No products found.
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    @include('products.partials.cart-sidebar')
@endsection

@push('scripts')
    <script src="{{ asset('js/products.js') }}"></script>
@endpush
