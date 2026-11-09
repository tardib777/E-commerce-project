@extends('layouts.app')

@section('content')
<style>
    .checkout-card { max-width: 560px; margin: 0 auto; border: 0; border-radius: 16px; box-shadow: 0 10px 30px rgba(42,53,71,.08); }
    .pay-option { display: flex; align-items: center; gap: 14px; border: 2px solid #5d87ff; background: rgba(93,135,255,.06); border-radius: 12px; padding: 14px 16px; }
    .pay-option .title { font-weight: 700; color: #2a3547; }
    .pay-option .sub { font-size: .85rem; color: #5a6a85; }
    .card-brands { display: flex; gap: 8px; margin-inline-start: auto; }
    .brand-badge { display: inline-flex; align-items: center; justify-content: center; height: 30px; min-width: 52px; padding: 0 8px; border-radius: 6px; background: #fff; border: 1px solid #e5eaef; font-weight: 800; font-size: .85rem; }
    .brand-visa { color: #1a1f71; font-style: italic; letter-spacing: .5px; }
    .brand-mc span { width: 18px; height: 18px; border-radius: 50%; display: inline-block; }
    .brand-mc span:first-child { background: #eb001b; }
    .brand-mc span:last-child { background: #f79e1b; margin-inline-start: -7px; mix-blend-mode: multiply; }
    .summary-row { display: flex; justify-content: space-between; padding: 6px 0; color: #5a6a85; }
    .summary-row.total { border-top: 1px solid #e5eaef; margin-top: 6px; padding-top: 12px; color: #2a3547; font-weight: 700; font-size: 1.15rem; }
    .test-hint { font-size: .82rem; background: #fff8e6; border: 1px solid #ffe2a8; border-radius: 10px; padding: 10px 12px; color: #7a5a00; }
</style>

<div class="card checkout-card">
    <div class="card-body p-4">
        <h3 class="mb-1">Checkout</h3>
        <p class="text-muted mb-4">Order #{{ $order->id }}</p>

        @foreach($gateways as $key => $label)
            <div class="pay-option mb-4">
                <i class="ti ti-credit-card fs-7 text-primary"></i>
                <div>
                    <div class="title">{{ $label }}</div>
                    <div class="sub">Secure payment page powered by Stripe. Card details never touch our server.</div>
                </div>
                <div class="card-brands" aria-label="Accepted cards">
                    <span class="brand-badge brand-visa">VISA</span>
                    <span class="brand-badge brand-mc" title="Mastercard"><span></span><span></span></span>
                </div>
            </div>
        @endforeach

        <div class="mb-4">
            <div class="summary-row"><span>Items</span><span>{{ $order->products->count() }}</span></div>
            <div class="summary-row total"><span>Total</span><span>${{ number_format((float) $order->total_price, 2) }}</span></div>
        </div>

        <div class="test-hint mb-4">
            <strong>Sandbox mode:</strong> no real money is charged. Use test card
            <code>4242 4242 4242 4242</code> (Visa) or <code>5555 5555 5555 4444</code> (Mastercard),
            any future expiry date and any CVC.
        </div>

        @php($method = array_key_first($gateways))
        <a href="{{ route('orders.payment.pay', ['method' => $method, 'order_id' => $order->id, 'currency' => array_key_first($currencies)]) }}"
           class="btn btn-primary w-100 py-2 fw-semibold">
            <i class="ti ti-lock"></i> Pay ${{ number_format((float) $order->total_price, 2) }} with card
        </a>
        <a href="{{ route('orders.index') }}" class="btn btn-link w-100 mt-2">Back to my orders</a>
    </div>
</div>
@endsection
