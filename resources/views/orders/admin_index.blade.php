@extends('layouts.app')
@section('topbar_title', 'All Orders')
@section('content')

@php
    $badge = fn($s) => match($s) {
        'paid' => 'badge-soft-success',
        'pending' => 'badge-soft-warning',
        'canceled' => 'badge-soft-danger',
        default => 'badge-soft-muted',
    };
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="page-title mb-1">All Orders</h3>
        <p class="page-subtitle">{{ $orders->count() }} order{{ $orders->count() == 1 ? '' : 's' }} placed.</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table dash-table align-middle">
                <thead><tr>
                    <th>Order</th><th>Customer</th><th>Items</th><th>Total</th><th>Status</th><th class="text-end">Date</th>
                </tr></thead>
                <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td class="fw-semibold">#{{ $order->id }}</td>
                        <td>
                            @if($order->user)
                                <div class="fw-semibold">{{ $order->user->firstname }} {{ $order->user->lastname }}</div>
                                <div class="text-muted" style="font-size:.78rem;">{{ $order->user->email }}</div>
                            @else
                                <span class="text-muted">Guest</span>
                            @endif
                        </td>
                        <td><span class="badge badge-pill badge-soft-muted">{{ $order->products->sum(fn($p) => $p->pivot->quantity) }} item(s)</span></td>
                        <td class="fw-semibold">${{ number_format($order->total_price, 2) }}</td>
                        <td><span class="badge badge-pill {{ $badge($order->status) }}">{{ ucfirst($order->status) }}</span></td>
                        <td class="text-end text-muted">{{ $order->created_at ? $order->created_at->format('M d, Y') : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No orders yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
