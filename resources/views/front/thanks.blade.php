@extends('front.layouts.app')

@section('content')
<br>
<br>
    <section class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body text-center">
                        @if(Session::has('success'))
                            <div class="alert alert-success">
                                {{ Session::get('success') }}
                            </div>
                        @endif

                        <h2>Thank you! Your order is being processed.</h2>
                        <br>
                        <h3>Order ID is: {{ $id }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
