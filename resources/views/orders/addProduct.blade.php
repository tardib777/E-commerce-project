@extends('layouts.app')
@section('content')
<h2 class="mb-4">Add Product to an order</h2>
<form action="{{ route('orders.addProduct') }}" method="post">
    @csrf
 
    <input type="hidden" class="form-control" name="product_id" value="{{ $product->id }}">

                        
    <div class="row mb-3">
        <label for="quantity" class="col-md-4 col-form-label text-md-end">{{ __('Quantity') }}</label>

        <div class="col-md-6">
            <input id="quantity" type="number" class="form-control @error('quantity') is-invalid @enderror" name="quantity"
                   value="{{ old('quantity', 1) }}" min="1" max="{{ max(0, $product->available_quantity) }}"
                   required @disabled($product->available_quantity < 1)>
            <small class="text-muted">
                {{ $product->available_quantity > 0 ? $product->available_quantity.' in stock' : 'Out of stock' }}
                &middot; ${{ number_format((float) $product->price, 2) }} each
            </small>

                @error('quantity')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
        </div>
    </div>
    <div class="row mb-0">
        <div class="col-md-6 offset-md-4">
            <button type="submit" class="btn btn-primary" @disabled($product->available_quantity < 1)>
                {{ __('Add') }}
            </button>
        </div>
    </div>
                        
</form>
@endsection