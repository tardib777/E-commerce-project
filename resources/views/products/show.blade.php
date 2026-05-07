@extends('layouts.app')
@section('content')
<style>
    .product-hero {
         position: relative;
        background-image: url('{{ asset("storage/" . $product->image) }}');
        background-repeat: no-repeat;
        background-position: center center;
        background-size: contain;   /* Show full image */
        background-color: #000;     /* Fills empty space around image */
        min-height: 60vh;           /* Flexible height */
        width: 100%;
        color: white;
        display: flex;
        align-items: flex-end;
    }

    .product-hero-overlay {
        background: rgba(0, 0, 0, 0.5);
        height: 100%;
        padding: 3rem;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }

    .product-section {
        padding: 3rem 0;
    }

    .product-section h2 {
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .product-price {
        font-size: 2rem;
        color: #0d6efd;
    }

    .category-badge {
        background: #e9ecef;
        border-radius: 0.25rem;
        padding: 0.3rem 0.75rem;
        font-size: 0.875rem;
        margin-right: 0.5rem;
    }
</style>

<!-- Hero Image Section -->
<div class="img-fluid product-hero">
    <div class="product-hero-overlay">
        <h1 class="display-4">{{ $product->name }}</h1>
        <div>
            @foreach($product->categories as $category)
                <span class="category-badge">{{ $category->name }}</span>
            @endforeach
        </div>
    </div>
</div>

<!-- Product Details Section -->
<div class="container product-section">
    <div class="row">
        <div class="col-lg-8">
            <h2>Description</h2>
            <p style="font-size: 1.1rem; line-height: 1.7;">{{ $product->description }}</p>
        </div>
        <div class="col-lg-4">
            <h2>Details</h2>
            <p class="product-price">${{ number_format($product->price, 2) }}</p>
            <p><strong>Available Quantity:</strong> {{ $product->available_quantity }}</p>
            <div class="mt-4">
                <a href="{{ route('home') }}" class="btn btn-outline-dark me-2">Back to Products</a>
                @auth
                @hasrole('admin')
                    <a href="{{ route('products.edit',$product->id) }}" class="btn btn-primary">Edit Product</a>
                    <form action="{{ route('products.destroy',$product->id) }}" method="post">
                        @csrf
                        @method('DELETE')
                        <input type="submit" class="btn btn-danger" value="Delete the product">
                    </form>
                @endhasrole
                @hasrole('customer')
                <a href="{{ route('orders.addProductPage', ['product_id' => $product->id]) }}" class="btn btn-primary" id="add">Add to an order</a>
                @endrole
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection