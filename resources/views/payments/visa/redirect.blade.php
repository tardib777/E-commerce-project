@extends('layouts.app')

@section('content')
<h2>{{ __('Redirecting to Visa secure payment...') }}</h2>
<p>{{ __("Please wait, you're being redirected to complete your card payment securely.") }}</p>

<form id="visa-payment-form" action="{{ $endpoint }}" method="post">
    @foreach($fields as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach
    <noscript>
        <button type="submit" class="btn btn-primary">{{ __('Continue to payment') }}</button>
    </noscript>
</form>

<script>
    document.getElementById('visa-payment-form').submit();
</script>
@endsection
