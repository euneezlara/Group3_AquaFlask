<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingCharge;
use Auth;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $product = Product::with('product_images')->find($request->id);

        if ($product == null) {
            return response()->json([
                'status' => false,
                'message' => "Product not found"

            ]);
        }

        if (Cart::count() > 0) {
            // echo "Product already in cart";

            $cartContent = Cart::content();
            $productAlreadyExist = false;

            foreach ($cartContent as $item) {
                if ($item->id == $product->id) {
                    $productAlreadyExist = true;
                }
            }

            if ($productAlreadyExist == false) {
                Cart::add($product->id, $product->name, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);

                $status = true;
                $message = '<strong>' . $product->name . '</strong> added in your cart successfully';
                session()->flash('success', $message);

            } else {
                $status = false;
                $message = $product->name . ' already your in cart';

            }

        } else {

            Cart::add($product->id, $product->name, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);

            $status = true;
            $message = '<strong>' . $product->name . '</strong> added in your cart successfully';
            session()->flash('success', $message);
        }

        return response()->json([
            'status' => $status,
            'message' => $message

        ]);


        // Cart::add('293ad', 'Product 1', 1, 9.99);

    }

    public function cart()
    {

        $cartContent = Cart::content();
        $data['cartContent'] = $cartContent;
        return view('front\cart', $data);

    }

    public function updateCart(Request $request)
    {
        $rowId = $request->rowId;
        $qty = $request->qty;

        $itemInfo = Cart::get($rowId);
        $product = Product::find($itemInfo->id);

        if ($product->track_qty == 'Yes') {
            if ($qty <= $product->qty) {
                Cart::update($rowId, $qty);
                $message = 'Cart updated successfully';
                $status = true;
                session()->flash('success', $message);
            } else {
                $message = 'Requested quantity (' . $qty . ') not available in stock.';
                $status = false;
                session()->flash('error', $message);
            }
        } else {
            Cart::update($rowId, $qty);
            $message = 'Cart updated successfully';
            $status = true;
            session()->flash('success', $message);
        }

        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    public function deleteItem(Request $request)
    {
        $rowId = $request->input('rowId');

        // Validate $rowId if needed

        $itemInfo = Cart::get($rowId);

        if ($itemInfo === null) {
            $errorMessage = 'Item not found in cart';
            session()->flash('error', $errorMessage);

            return response()->json([
                'status' => false,
                'message' => $errorMessage
            ]);
        }

        Cart::remove($rowId);

        $message = 'Item removed from cart successfully';
        session()->flash('success', $message);

        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }

    // public function checkout()
    // {

    //     //empty cart
    //     if (Cart::count() == 0) {
    //         return redirect()->route('front.cart');
    //     }

    //     //user not logged in
    //     if (Auth::check() == false) {
    //         if (!session()->has('url.intended')) {
    //             session(['url.intended' => url()->current()]);

    //         }

    //         return redirect()->route('account.login');
    //     }

    //     $customerAddress = CustomerAddress::where('user_id', Auth::user()->id)->first();

    //     session()->forget('url.intended');

    //     $countries = Country::orderBy('name', 'ASC')->get();

    //     // Calculate Shipping
    //     $userCountry = $customerAddress->country_id;
    //     $shippingInfo = ShippingCharge::where('country_id', $userCountry)->first();

    //     $totalQty = 0;
    //     $totalShippingCharge = 0;
    //     $grandTotal = 0;
    //     foreach (Cart::content() as $item) {
    //         $totalQty += $item->qty;
    //     }

    //     $totalShippingCharge = $shippingInfo->amount;

    //     $grandTotal = Cart::subtotal(2, '.', '') + $totalShippingCharge;


    //     return view('front.checkout', [
    //         'countries' => $countries,
    //         'customerAddress' => $customerAddress,
    //         'totalShippingCharge' => $totalShippingCharge,
    //         'grandTotal' => $grandTotal
    //     ]);


    // }

    public function checkout()
    {
        // Empty cart
        if (Cart::count() == 0) {
            return redirect()->route('front.cart');
        }

        // User not logged in
        if (Auth::check() == false) {
            session(['url.intended' => url()->previous()]);
            return redirect()->route('account.login')->with('redirected_from_checkout', true);

        }

        // Fetch customer address
        $customerAddress = CustomerAddress::where('user_id', Auth::user()->id)->first();

        // Forget intended URL for redirect
        session()->forget('url.intended');

        $countries = Country::orderBy('name', 'ASC')->get();
        $totalShippingCharge = 0;
        $grandTotal = 0;

        // Calculate Shipping
        if ($customerAddress !== null) {
            $userCountry = $customerAddress->country_id;
            $shippingInfo = ShippingCharge::where('country_id', $userCountry)->first();

            if ($shippingInfo !== null) {
                $totalShippingCharge = $shippingInfo->amount;
            }

            // Calculate grand total
            $grandTotal = Cart::subtotal(2, '.', '') + $totalShippingCharge;
        }

        return view('front.checkout', [
            'countries' => $countries,
            'customerAddress' => $customerAddress,
            'totalShippingCharge' => $totalShippingCharge,
            'grandTotal' => $grandTotal
        ]);
    }


    public function processCheckout(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'country' => 'required',
            'address' => 'required',
            'city' => 'required',
            'province' => 'required',
            'zip' => 'required',
            'mobile' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Please complete all the required fields',
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        // saving user address

        $user = Auth::user();

        CustomerAddress::updateOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'country_id' => $request->country,
                'address' => $request->address,
                'apartment' => $request->apartment,
                'city' => $request->city,
                'province' => $request->province,
                'zip' => $request->zip,

            ]
        );

        if ($request->payment_method == 'cod') {
            $subTotal = Cart::subtotal(2, '.', '');

            // Fetch shipping charge based on country
            $shippingInfo = ShippingCharge::where('country_id', $request->country)->first();

            if ($shippingInfo !== null) {
                $shipping = $shippingInfo->amount;
            } else {
                // If shipping charge for the country is not found, use a default value or handle it accordingly
                $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();
                $shipping = $shippingInfo ? $shippingInfo->amount : 0; // Use 0 if not found
            }

            $grandTotal = $subTotal + $shipping;

            // Create and save the order
            $order = new Order;
            $order->subtotal = $subTotal;
            $order->shipping = $shipping;
            $order->grand_total = $grandTotal;
            $order->user_id = $user->id;
            $order->payment_status = 'not paid';
            $order->status = 'pending';
            $order->first_name = $request->first_name;
            $order->last_name = $request->last_name;
            $order->email = $request->email;
            $order->mobile = $request->mobile;
            $order->address = $request->address;
            $order->apartment = $request->apartment;
            $order->province = $request->province;
            $order->city = $request->city;
            $order->zip = $request->zip;
            $order->notes = $request->notes;
            $order->country_id = $request->country;
            $order->save();


            // Save order items
            foreach (Cart::content() as $item) {
                $orderItem = new OrderItem;
                $orderItem->product_id = $item->id;
                $orderItem->order_id = $order->id;
                $orderItem->name = $item->name;
                $orderItem->qty = $item->qty;
                $orderItem->price = $item->price;
                $orderItem->total = $item->price * $item->qty;
                $orderItem->save();

                //update product stock
                $productData = Product::find($item->id);
                if ($productData->track_qty == 'Yes') {
                    $currentQty = $productData->qty;
                    $updatedQty = $currentQty - $item->qty;
                    $productData->qty = $updatedQty;
                    $productData->save();

                }
            }

            // Flash success message and clear cart
            session()->flash('success', 'You have successfully placed your order');
            Cart::destroy();

            return response()->json([
                'message' => 'Order saved successfully',
                'orderId' => $order->id,
                'status' => true
            ]);
        } else {
            // Handle other payment methods or cases here if needed
        }


    }

    public function thankyou($id)
    {
        return view('front.thanks', [
            'id' => $id
        ]);
    }

    public function getOrderSummary(Request $request)
    {
        if ($request->country_id > 0) {
            $subTotal = Cart::subtotal(2, '.', '');

            $shippingInfo = ShippingCharge::where('country_id', $request->country_id)->first();

            $totalQty = 0;
            foreach (Cart::content() as $item) {
                $totalQty += $item->qty;
            }

            if ($shippingInfo != null) {
                $shippingCharge = $shippingInfo->amount;

                $grandTotal = $subTotal + $shippingCharge;

                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal, 2),
                    'shippingCharge' => number_format($shippingCharge, 2),
                ]);

            } else {
                $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();
                $shippingCharge = $shippingInfo->amount;
                $grandTotal = $subTotal + $shippingCharge;

                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal, 2),
                    'shippingCharge' => number_format($shippingCharge, 2),
                ]);

            }

        } else {
            // $grandTotal = 0;
            return response()->json([
                'status' => true,
                'grandTotal' => number_format($grandTotal, 2),
                'shippingCharge' => number_format(0, 2),
            ]);
        }
    }
}
