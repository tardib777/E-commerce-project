@extends('layouts.app')
@section('content')
<h2 class="mb-4">Add Product to the order {{ $order_id }}</h2>
<form action="{{ route('orders.update',$order_id) }}" method="post">
    @csrf
    @method('PUT')
 
    <input type="hidden" class="form-control" name="product_id" value="{{ $product->id }}">

                        
    <div class="row mb-3">
        <label for="quantity" class="col-md-4 col-form-label text-md-end">{{ __('Quantity') }}</label>

        <div class="col-md-6">
            <input id="quantity" type="number" class="form-control @error('quantity') is-invalid @enderror" name="quantity" required>

                @error('quantity')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
        </div>
    </div>
    <input id="price" type="hidden" name="price" value="{{ $product->price }}">
              
    <div class="row mb-0">
        <div class="col-md-6 offset-md-4">
            <button type="submit" class="btn btn-primary">
                {{ __('Add') }}
            </button>
        </div>
    </div>
                        
</form>
@endsection