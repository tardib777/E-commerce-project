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
           <div class="row mb-3">
        <label for="crypto_currency" class="col-md-4 col-form-label text-md-end">
            {{ __('Select Cryptocurrency') }}
        </label>

        <div class="col-md-6">
            <select id="crypto_currency" name="crypto_currency" class="form-control">
                <option value="">-- Select a cryptocurrency --</option>
                @foreach($currencies as $currency)
                    <option value="{{ $currency }}">{{ $currency }}</option>
                @endforeach
            </select>

            @error('crypto_currency')
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
    const cryptoSelect = document.getElementById('crypto_currency');
    const cryptoRow = cryptoSelect.closest('.row');
    const payBase = "{{ url('orders/pay') }}/";
    const orderId = "{{ $order->id }}";

    // The cryptocurrency picker is only relevant for NOWPayments.
    function toggleCryptoRow() {
        cryptoRow.style.display = methodSelect.value === 'NOWPayment' ? '' : 'none';
    }
    methodSelect.addEventListener('change', toggleCryptoRow);
    toggleCryptoRow();

    // Build the action URL from the current selections right before submitting.
    form.addEventListener('submit', function (e) {
        const method = methodSelect.value;
        if (!method) {
            e.preventDefault();
            return;
        }
        let action = payBase + method + '/' + orderId;
        if (method === 'NOWPayment' && cryptoSelect.value) {
            action += '/' + cryptoSelect.value;
        }
        form.action = action;
    });
</script>
@endsection
