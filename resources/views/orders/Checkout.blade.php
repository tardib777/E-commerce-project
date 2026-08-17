@extends('layouts.app')

@section('content')
<h2>Checkout</h2>

<form id="checkout-form" action="" method="get">
    <div class="row mb-3">
        <label for="method" class="col-md-4 col-form-label text-md-end">
            {{ __('Payment method') }}
        </label>

        <div class="col-md-6">
            <select id="method" name="method" class="form-control" required>
                <option value="">-- Select a payment method --</option>
                @foreach($gateways as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>

            @error('method')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </div>

    <div class="row mb-3">
        <label for="currency" class="col-md-4 col-form-label text-md-end">
            {{ __('Currency') }}
        </label>

        <div class="col-md-6">
            <select id="currency" name="currency" class="form-control" required>
                @foreach($currencies as $code => $label)
                    <option value="{{ $code }}" @selected($code === 'USD')>{{ $label }}</option>
                @endforeach
            </select>

            @error('currency')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </div>

    <div class="row mb-3">
        <label for="price" class="col-md-4 col-form-label text-md-end">
            {{ __('Price') }}
        </label>
        <div class="col-md-6">
            <div id="price">{{ $order->total_price }}</div>
        </div>
    </div>

    <div class="row mb-0">
        <div class="col-md-6 offset-md-4">
            <button type="submit" class="btn btn-primary">
                {{ __('Select') }}
            </button>
        </div>
    </div>
</form>

<script>
    const form = document.getElementById('checkout-form');
    const methodSelect = document.getElementById('method');
    const currencySelect = document.getElementById('currency');
    const payBase = "{{ url('orders/pay') }}/";
    const orderId = "{{ $order->id }}";

    // Build the action URL from the current selections right before submitting.
    form.addEventListener('submit', function (e) {
        const method = methodSelect.value;
        if (!method) {
            e.preventDefault();
            return;
        }
        form.action = payBase + method + '/' + orderId + '/' + currencySelect.value;
    });
</script>
@endsection
