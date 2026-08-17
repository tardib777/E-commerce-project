@extends('layouts.app')
@section('topbar_title', 'All Products')
@section('content')

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="page-title mb-1">All Products</h3>
        <p class="page-subtitle">{{ $products->count() }} product{{ $products->count() == 1 ? '' : 's' }} in your store.</p>
    </div>
    <a href="{{ route('products.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3">
        <i class="ti ti-circle-plus fs-5"></i> Add Product
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table dash-table align-middle">
                <thead><tr>
                    <th>#</th><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th class="text-end">Actions</th>
                </tr></thead>
                <tbody>
                @forelse($products as $product)
                    <tr>
                        <td class="text-muted">{{ $product->id }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $product->image ? asset('storage/'.$product->image) : '' }}" class="table-thumb" alt="" onerror="this.style.visibility='hidden'">
                                <span class="fw-semibold">{{ Str::limit($product->name, 30) }}</span>
                            </div>
                        </td>
                        <td><span class="text-muted">{{ $product->categories->pluck('name')->implode(', ') ?: '—' }}</span></td>
                        <td class="fw-semibold">${{ number_format($product->price, 2) }}</td>
                        <td>
                            @if($product->available_quantity > 0)
                                <span class="badge badge-pill badge-soft-success">{{ $product->available_quantity }} in stock</span>
                            @else
                                <span class="badge badge-pill badge-soft-danger">Out of stock</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2 flex-wrap justify-content-end">
                                <a href="{{ route('products.show', $product->id) }}" class="btn-action btn-action-view" title="Show"><i class="ti ti-eye"></i><span>Show</span></a>
                                <a href="{{ route('products.edit', $product->id) }}" class="btn-action btn-action-edit" title="Edit"><i class="ti ti-edit"></i><span>Edit</span></a>
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Delete this product?');" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action btn-action-delete" title="Delete"><i class="ti ti-trash"></i><span>Delete</span></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No products yet. <a href="{{ route('products.create') }}">Add your first product</a>.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
