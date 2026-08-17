@extends('layouts.app')
@section('topbar_title', 'Edit Product')
@section('content')

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="page-title mb-1">Edit Product</h3>
        <p class="page-subtitle">Update the details of <span class="fw-semibold">{{ $product->name }}</span>.</p>
    </div>
    <a href="{{ route('home') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
        <i class="ti ti-arrow-left"></i> Back
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">
        <div class="card form-card">
            <div class="card-body p-4">
                <form action="{{ route('products.update', $product->id) }}" method="post" enctype="multipart/form-data">
                    @csrf

                    @if($product->image)
                        <div class="mb-4 d-flex align-items-center gap-3">
                            <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="rounded" style="width:88px;height:88px;object-fit:cover;background:#eef1f6;">
                            <div><div class="fw-semibold">Current image</div><div class="text-muted small">Upload a new file below to replace it.</div></div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="productName" class="form-label">Product Name</label>
                        <input id="productName" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $product->name) }}" required autocomplete="name" autofocus>
                        @error('name') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" rows="4" class="form-control @error('description') is-invalid @enderror" name="description" required>{{ old('description', $product->description) }}</textarea>
                        @error('description') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price ($)</label>
                            <input id="price" type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" name="price" value="{{ old('price', $product->price) }}" required>
                            @error('price') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="avbnum" class="form-label">Available Quantity</label>
                            <input id="avbnum" type="number" class="form-control @error('available_quantity') is-invalid @enderror" name="available_quantity" value="{{ old('available_quantity', $product->available_quantity) }}" required>
                            @error('available_quantity') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select id="category" class="form-select" name="category_id" required>
                            <option value="">Select category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ (int) old('category_id', $product->category_id) === (int) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="image" class="form-label">Product Image</label>
                        <input id="image" type="file" accept="image/*" class="form-control @error('image') is-invalid @enderror" name="image" required>
                        <div class="form-text">Re-uploading an image is required to save changes.</div>
                        @error('image') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4">
                            <i class="ti ti-device-floppy"></i> Update Product
                        </button>
                        <a href="{{ route('home') }}" class="btn btn-light">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
