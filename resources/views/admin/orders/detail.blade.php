@extends('admin.layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid my-2">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Order: {{ $order->id }}</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('orders.index') }}" class="btn btn-primary">Back</a>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Default box -->
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-9">
                    @include('admin.message')
                    <div class="card">
                        <div class="card-header pt-3">
                            <div class="row invoice-info">
                                <div class="col-sm-4 invoice-col">
                                    <h1 class="h5 mb-3">Shipping Address</h1>
                                    <address>
                                        <strong>{{ $order->first_name . ' ' . $order->last_name }}</strong><br>
                                        {{ $order->address }}<br>
                                        {{ $order->city }}, {{ $order->zip }} {{ $order->countryName }}<br>
                                        Phone: {{ $order->mobile }}<br>
                                        Email: {{ $order->email }}
                                    </address>
                                    <strong>Shipped Date</strong>
                                    @if (!empty($order->shipped_date))
                                        {{ \Carbon\Carbon::parse($order->shipped_date)->format('d M, Y') }}
                                    @else
                                        Not yet Shipped
                                    @endif

                                </div>



                                <div class="col-sm-4 invoice-col">
                                    <b>Order ID:</b> {{ $order->id }}<br>
                                    <b>Total:</b> {{ number_format($order->grand_total, 2) }}<br>
                                    <b>Status:</b>
                                    @if ($order->status == 'pending')
                                        <span class="text-danger">Pending</span>
                                    @elseif($order->status == 'shipped')
                                        <span class="text-info">Shipped</span>
                                    @elseif($order->status == 'delivered')
                                        <span class="text-success">Delivered</span>
                                    @else
                                        <span class="text-danger">Cancelled</span>
                                    @endif
                                    <br>
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-3">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th width="100">Price</th>
                                        <th width="100">Qty</th>
                                        <th width="100">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($orderItems as $item)
                                        <tr>
                                            <td>{{ $item->name }}</td>
                                            <td>₱{{ number_format($item->price, 2) }}</td>
                                            <td>{{ $item->qty }}</td>
                                            <td>₱{{ number_format($item->total, 2) }}</td>
                                        </tr>
                                    @endforeach

                                    <tr>
                                        <th colspan="3" class="text-right">Subtotal:</th>
                                        <td>₱{{ number_format($order->subtotal, 2) }}</td>
                                    </tr>

                                    <tr>
                                        <th colspan="3" class="text-right">Shipping:</th>
                                        <td>₱{{ number_format($order->shipping, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-right">Grand Total:</th>
                                        <td>₱{{ number_format($order->grand_total, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <form action="{{ route('orders.changeOrderStatus', $order->id) }}" method="post"
                            name="changeOrderStatusForm" id="changeOrderStatusForm">

                            @csrf
                            <div class="card-body">
                                <h2 class="h4 mb-3">Order Status</h2>
                                <div class="mb-3">
                                    <select name="status" id="status" class="form-control">
                                        <option value="pending"{{ $order->status == 'pending' ? ' selected' : '' }}>Pending
                                        </option>

                                        <option value="shipped"{{ $order->status == 'shipped' ? ' selected' : '' }}>Shipped
                                        </option>

                                        <option value="delivered"{{ $order->status == 'delivered' ? ' selected' : '' }}>
                                            Delivered</option>
                                        <option value="cancelled"{{ $order->status == 'cancelled' ? ' selected' : '' }}>
                                            Cancelled</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <input value="{{ $order->shipped_date }}" type="text" name="shipped_date"
                                        id="shipped_date" class="form-control datepicker">




                                </div>
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        </div>
        </div>
        <!-- /.card -->
    </section>
    <!-- /.content -->
@endsection
@section('customJS')
    <script>
        <!-- jQuery 
        -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <!-- Bootstrap Datepicker CSS and JS -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize datepicker
            $('#shipped_date').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                orientation: 'bottom',
            });

            // Add an onchange event handler to the status dropdown
            $('#status').on('change', function() {
                // Check if the selected value is either "shipped" or "delivered"
                if ($(this).val() === 'shipped' || $(this).val() === 'delivered') {
                    // Enable the "Shipped Date" textbox
                    $('#shipped_date').prop('disabled', false);
                } else {
                    // Disable the "Shipped Date" textbox
                    $('#shipped_date').prop('disabled', true);
                }
            });

            // Trigger the event handler on page load to set the initial state
            $('#status').trigger('change');
        });
    </script>
@endsection
