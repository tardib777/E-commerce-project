@extends('layouts.app')

@section('content')
<h2>Payment</h2>

<form id="checkout-form" action="" method="post">
    @csrf
    <label for=""></label>
    

    <button type="submit">Pay with NOWPayments</button>
 
@endsection
