@extends('admin\layouts\app')

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Admin Dashboard</h1>
                </div>
                <div class="col-sm-6">

                </div>
            </div>
        </div>

    </section>

    <section class="content">
        <!-- Default box -->
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-6">
                    <div class="row">
                        <div class="col-lg-6 col-md-6">
                            <div class="small-box card">
                                <div class="inner">
                                    <!-- Content for the first box -->
                                    <h3>{{ $totalCustomers }}</h3>
                                    <p>Total Customers</p>
                                </div>
                                <!-- Add your icon or image if needed -->
                                <div class="icon">
                                    <i class="ion ion-stats-bars"></i>
                                </div>
                                <!-- Add any footer link or additional content -->
                                <a href="javascript:void(0);" class="small-box-footer">&nbsp;</a>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="small-box card">
                                <div class="inner">
                                    <!-- Content for the second box -->
                                    <h3>{{$totalProducts}}</h3>
                                    <p>Total Products</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-bag"></i>
                                </div>
                                <a href="{{route("products.index")}}" class="small-box-footer text-dark">More info <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row">
                        <div class="col-lg-6 col-md-6">
                            <div class="small-box card">
                                <div class="inner">
                                    <!-- Content for the third box -->
                                    <h3>{{$totalOrders}}</h3>
                                    <p>Total Orders</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-bag"></i>
                                </div>
                                <a href="{{route ('orders.index')}}" class="small-box-footer text-dark">More info <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="small-box card">
                                <div class="inner">
                                    <!-- Content for the fourth box -->
                                    <h3>₱{{number_format($totalRevenue,2)}}</h3>
                                    <p>Total Sales</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-person-add"></i>
                                </div>
                                <a href="javascript:void(0);" class="small-box-footer">&nbsp;</a>
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
        console.log("hello")
    </script>
@endsection
