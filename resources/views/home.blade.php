@extends('layouts.app')

@section('content')

@hasrole('admin')
    {{-- ============================ ADMIN DASHBOARD ============================ --}}
    @php
        $productCount  = \App\Models\Product::count();
        $categoryCount = \App\Models\Category::count();
        $orderCount    = \App\Models\Order::count();
        $userCount     = \App\Models\User::count();
        $paidCount     = \App\Models\Order::where('status', 'paid')->count();
        $pendingCount  = \App\Models\Order::where('status', 'pending')->count();
        $revenue       = \App\Models\Order::where('status', 'paid')->sum('total_price');
        $recentProducts = \App\Models\Product::with('categories')->latest()->take(6)->get();
        $recentOrders   = \App\Models\Order::with('user')->latest()->take(5)->get();
        $badge = fn($s) => match($s) {
            'paid' => 'badge-soft-success',
            'pending' => 'badge-soft-warning',
            'canceled' => 'badge-soft-danger',
            default => 'badge-soft-muted',
        };
    @endphp

    <!-- Page header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h3 class="page-title mb-1">Welcome back, {{ Auth::user()->firstname }} 👋</h3>
            <p class="page-subtitle">Here's what's happening in your store today.</p>
        </div>
        <a href="{{ route('products.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3">
            <i class="ti ti-circle-plus fs-5"></i> Add Product
        </a>
    </div>

    <!-- Stat cards -->
    <div class="row g-3 mb-2">
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon soft-primary"><i class="ti ti-box"></i></div>
                <div><div class="stat-value">{{ $productCount }}</div><div class="stat-label">Total Products</div></div>
            </div></div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon soft-info"><i class="ti ti-category"></i></div>
                <div><div class="stat-value">{{ $categoryCount }}</div><div class="stat-label">Categories</div></div>
            </div></div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon soft-warning"><i class="ti ti-shopping-cart"></i></div>
                <div><div class="stat-value">{{ $orderCount }}</div><div class="stat-label">Total Orders</div></div>
            </div></div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon soft-success"><i class="ti ti-users"></i></div>
                <div><div class="stat-value">{{ $userCount }}</div><div class="stat-label">Registered Users</div></div>
            </div></div>
        </div>
    </div>

    <!-- Secondary metrics -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
                <div><div class="stat-label mb-1">Revenue (paid)</div><div class="stat-value">${{ number_format($revenue, 2) }}</div></div>
                <div class="stat-icon soft-success"><i class="ti ti-currency-dollar"></i></div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
                <div><div class="stat-label mb-1">Paid Orders</div><div class="stat-value">{{ $paidCount }}</div></div>
                <div class="stat-icon soft-primary"><i class="ti ti-circle-check"></i></div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
                <div><div class="stat-label mb-1">Pending Orders</div><div class="stat-value">{{ $pendingCount }}</div></div>
                <div class="stat-icon soft-warning"><i class="ti ti-clock"></i></div>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Recent products -->
        <div class="col-lg-8" id="products-table">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Recent Products</h5>
                        <a href="{{ route('products.create') }}" class="btn btn-sm btn-outline-primary">Add New</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table dash-table align-middle">
                            <thead><tr>
                                <th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th class="text-end">Actions</th>
                            </tr></thead>
                            <tbody>
                            @forelse($recentProducts as $product)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $product->image ? asset('storage/'.$product->image) : '' }}" class="table-thumb" alt="" onerror="this.style.visibility='hidden'">
                                            <span class="fw-semibold">{{ Str::limit($product->name, 26) }}</span>
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
                                <tr><td colspan="5" class="text-center text-muted py-4">No products yet. <a href="{{ route('products.create') }}">Add your first product</a>.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent orders -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Recent Orders</h5>
                    <div class="table-responsive">
                        <table class="table dash-table align-middle">
                            <thead><tr><th>Order</th><th>Customer</th><th class="text-end">Total</th><th class="text-end">Status</th></tr></thead>
                            <tbody>
                            @forelse($recentOrders as $order)
                                <tr>
                                    <td class="fw-semibold">#{{ $order->id }}</td>
                                    <td class="text-muted text-truncate" style="max-width:90px;">{{ optional($order->user)->firstname ?? 'Guest' }}</td>
                                    <td class="fw-semibold text-end">${{ number_format($order->total_price, 2) }}</td>
                                    <td class="text-end"><span class="badge badge-pill {{ $badge($order->status) }}">{{ ucfirst($order->status) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No orders yet.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@else
    {{-- ============================ STOREFRONT (customers/guests) ============================ --}}
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="container py-4">
                    <h2 class="mb-4 fw-bold">Products</h2>
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-12 d-flex">
                            <select id="category" name="category" class="form-control form-control-sm me-2 flex-grow-1" required>
                                @foreach($categories as $category)
                                    <option id="selected" value="{{ $category->id }}" {{ request()->segment(2)==$category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        @forelse ($products as $product)
                            <div class="col-md-4 mt-4">
                                <div class="card h-100 shadow-sm">
                                    <img src="{{ asset('./storage/'.$product->image) }}" class="card-img-top" alt="{{ $product->name }}">
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title">{{ $product->name }}</h5>
                                        <p class="card-text description-clipped" id="desc-{{ $product->id }}">
                                            {{ Str::words($product->description, 30, '') }}
                                        </p>
                                        <div class="mt-auto">
                                            <p class="fw-bold">price: {{ $product->price }} $</p>
                                            <p class="text-muted">category:
                                                {{ $product->categories->pluck('name')->implode(' , ') }}
                                            </p>
                                            <a href="{{route('products.show',$product->id)}}" class="btn btn-primary w-100">More Details</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-center">There is no products related to this category at the moment</p>
                        @endforelse
                    </div>
                </div>

                <style>
                    .description-clipped {
                        overflow: hidden;
                        display: -webkit-box;
                        -webkit-line-clamp: 2;
                        -webkit-box-orient: vertical;
                    }
                </style>
                <script>
                    document.getElementById('category').addEventListener('change', function() {
                        let category = this.value;
                        if (category) {
                            location.replace("{{ url('/home') }}/" + category);
                        }
                    });
                </script>
            </div>
        </div>
    </div>
@endhasrole

@endsection
