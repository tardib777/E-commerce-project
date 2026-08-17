@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <!-- Customer Info -->
    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h4 class="card-title mb-1">user: {{ $user->firstname }} {{ $user->lastname }}</h4>
                <p class="mb-0 text-muted">Email: {{ $user->email }}</p>
            </div>
            <div>
                <span class="badge bg-primary">Total Orders: {{ $orders->count() }}</span>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Order History</h5>
        </div>
        <div class="card-body p-0">
            @if($orders->count())
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Order Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Settings</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $index => $order)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $order->created_at }}</td>
                            <td>
                                <ul class="list-unstyled mb-0">
                                    @foreach($order->products as $product)
                                        <li>{{ $product->name }} (x{{ $product->pivot->quantity }}) 
                                            @if($order->status == 'pending')
                                            <form action="{{ route('orders.removeProduct',[$order->id,$product->id]) }}" method="post">
                                                @csrf
                                                @method('DELETE')
                                                <input type="submit" class="btn btn-danger" value="DELETE">
                                            </form>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td>${{ number_format($order->total_price, 2) }}</td>
                            <td>
                                <span class="badge 
                                    @if($order->status == 'pending') bg-warning 
                                    @elseif($order->status == 'paid') bg-secondary
                                    @elseif($order->status == 'completed') bg-success 
                                    @elseif($order->status == 'cancelled') bg-danger 
                                    @else bg-secondary 
                                    @endif">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td>
                                @if($order->status == 'pending')
                                <form action="{{ route('orders.cancel',$order->id) }}" method="post">
                                    @csrf
                                    @method('PUT')
                                    <input type="submit" class="btn btn-danger" value="Cancel">
                
                                </form>
                                    <a href="{{ route('orders.checkout',$order->id) }}" class="btn btn-success">Checkout</a>
                                    <a href="{{ route('orders.payment.check',$order->id) }}" class="btn btn-info mt-1">Check payment status</a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-3">
                <p class="mb-0">No orders found for this customer.</p>
            </div>
            @endif
        </div>
    </div>
    <a href="{{ route('home') }}" class="btn btn-outline-secondary">Back to Products</a>
</div>
@endsection
