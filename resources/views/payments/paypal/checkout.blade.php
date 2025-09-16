@extends('layouts.app')
@section('content')
<div class="container">

    <div class="row mt-5 mb-5">

        <div class="col-10 offset-1 mt-5">
                    
            <div style="display:flex; justify-content: center; flex-direction: column;">

                <a href="{{ route('paypal.payment') }}" class="btn btn-success">Pay with PayPal </a>

            </div>
        </div>
    </div>
</div>
@endsection
        
        
    